<?php
// Each transaction locks before checking price, stock, dates, or capacity.
function create_order(int $buyer,string $source,array $lines,string $token,?string $due=null,bool $paid=true): int {
    $old=one('SELECT id FROM orders WHERE checkout_token=?',[$token]); if($old) return (int)$old['id'];
    $sum=array_sum(array_column($lines,'total'));
    $u=one('SELECT display_name FROM users WHERE id=?',[$buyer]);
    $oid=insert('orders',['order_number'=>'SKH-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(3))),'buyer_id'=>$buyer,'source'=>$source,'recipient_name'=>$u['display_name'],'pickup_notes'=>trim((string)($_POST['pickup_notes']??'Arrange pickup with the artist.')),'total_centavos'=>$sum,'payment_status'=>$paid?'demo_paid':'awaiting_demo_payment','checkout_token'=>$token,'payment_due_at'=>$due,'demo_paid_at'=>$paid?now():null,'created_at'=>now(),'updated_at'=>now()]);
    foreach($lines as $line) {
        $l=$line['listing']; $fee=$line['fee']??0;
        insert('order_items',['order_id'=>$oid,'listing_id'=>$l['id'],'seller_id'=>$l['seller_id'],'auction_id'=>$line['auction_id']??null,'rental_booking_id'=>$line['rental_id']??null,'commission_request_id'=>$line['commission_id']??null,'item_title'=>$l['title'],'quantity'=>$line['quantity']??1,'unit_price_centavos'=>$line['unit']??$line['total'],'line_total_centavos'=>$line['total'],'commission_bps'=>$line['bps']??0,'platform_fee_centavos'=>$fee,'seller_net_centavos'=>$line['total']-$fee,'status'=>$paid?'confirmed':'awaiting_demo_payment','created_at'=>now(),'updated_at'=>now()]);
        notify((int)$l['seller_id'],($paid?'New demo order: ':'Auction winner: ').$l['title'],'/?p=orders','order:'.$oid.':'.$l['id']);
    }
    return $oid;
}
function reconcile(): void {
    tx(function(){
        query("UPDATE rental_bookings SET status='expired',updated_at=? WHERE status='approved' AND hold_expires_at<=?",[now(),now()]);
        query("UPDATE commission_requests SET status='expired',updated_at=? WHERE (status='accepted' AND checkout_expires_at<=?) OR (status='quoted' AND quote_expires_at<=?)",[now(),now(),now()]);
        query("UPDATE auctions SET status='live',updated_at=? WHERE status='scheduled' AND starts_at<=? AND ends_at>?",[now(),now(),now()]);
        foreach(all("SELECT * FROM auctions WHERE status IN ('live','scheduled') AND ends_at<=?",[now()]) as $a){
            $bid=one('SELECT * FROM bids WHERE auction_id=? ORDER BY amount_centavos DESC,id ASC LIMIT 1',[$a['id']]);
            query("UPDATE auctions SET status='ended',winning_bid_id=?,finalized_at=?,updated_at=? WHERE id=?",[$bid['id']??null,now(),now(),$a['id']]);
            $l=listing((int)$a['listing_id']);
            if($bid){
                $due=gmdate('Y-m-d\TH:i:s\Z',strtotime($a['ends_at'])+86400);
                create_order((int)$bid['bidder_id'],'auction',[['listing'=>$l,'total'=>(int)$bid['amount_centavos'],'auction_id'=>$a['id']]],'auction:'.$a['id'],$due,false);
                notify((int)$bid['bidder_id'],'You won '.$l['title'].'. Open your orders for demo checkout.','/?p=orders','auction:'.$a['id']);
            } else query("UPDATE listings SET status='archived',updated_at=? WHERE id=?",[now(),$l['id']]);
        }
        foreach(all("SELECT o.id,i.listing_id FROM orders o JOIN order_items i ON i.order_id=o.id WHERE o.payment_status='awaiting_demo_payment' AND o.payment_due_at<=?",[now()]) as $o){
            query("UPDATE orders SET payment_status='void',updated_at=? WHERE id=?",[now(),$o['id']]);
            query("UPDATE order_items SET status='cancelled',cancellation_reason='Payment window expired',cancelled_at=?,updated_at=? WHERE order_id=?",[now(),now(),$o['id']]);
            query("UPDATE listings SET status='archived',updated_at=? WHERE id=?",[now(),$o['listing_id']]);
        }
    });
}
function place_bid(int $id,int $amount,int $uid,string $token): void {
    tx(function()use($id,$amount,$uid,$token){
        if(one('SELECT id FROM bids WHERE request_token=?',[$token])) return;
        $a=one('SELECT * FROM auctions WHERE id=?',[$id]); if(!$a) throw new DomainException('Auction not found.');
        $l=listing((int)$a['listing_id']); available($l);
        if($l['seller_id']===$uid) throw new DomainException('You cannot bid on your own artwork.');
        if(!in_array($a['status'],['live','scheduled'])||now()<$a['starts_at']||now()>=$a['ends_at']) throw new DomainException('This auction is not open for bidding.');
        $high=one('SELECT * FROM bids WHERE auction_id=? ORDER BY amount_centavos DESC,id LIMIT 1',[$id]);
        $min=$high?(int)$high['amount_centavos']+(int)$a['min_increment_centavos']:(int)$l['unit_price_centavos'];
        if($amount<$min) throw new DomainException('The next bid must be at least '.money($min).'.');
        $bid=insert('bids',['auction_id'=>$id,'bidder_id'=>$uid,'amount_centavos'=>$amount,'request_token'=>$token,'created_at'=>now()]);
        if($high&&$high['bidder_id']!==$uid) notify((int)$high['bidder_id'],'You were outbid on '.$l['title'],'/?p=detail&id='.$l['id'],'bid:'.$bid);
    });
}
function pay_cart(int $uid,string $token,array $expected): int {
    return tx(function()use($uid,$token,$expected){
        $old=one('SELECT id FROM orders WHERE checkout_token=? AND buyer_id=?',[$token,$uid]); if($old)return(int)$old['id'];
        $cart=all('SELECT * FROM cart_items WHERE buyer_id=?',[$uid]); if(!$cart) throw new DomainException('Your cart is empty.');
        $lines=[];
        foreach($cart as $c){ $l=listing((int)$c['listing_id']); available($l);
            if(!in_array($l['section'],['likha_lokal','kagamit','museo'])||$l['seller_id']===$uid) throw new DomainException('This item cannot be purchased.');
            if((string)($expected[$l['id']]??'')!==(string)$l['unit_price_centavos']) throw new DomainException('A price changed. Review your cart and try again.');
            if($c['quantity']>$l['stock_qty'])throw new DomainException('Not enough stock for '.$l['title'].'.');
            $lines[]=['listing'=>$l,'total'=>$c['quantity']*$l['unit_price_centavos'],'unit'=>$l['unit_price_centavos'],'quantity'=>$c['quantity']];
            query("UPDATE listings SET stock_qty=stock_qty-?,status=CASE WHEN stock_qty-?=0 THEN 'sold_out' ELSE status END,updated_at=? WHERE id=?",[$c['quantity'],$c['quantity'],now(),$l['id']]);
            if($l['artwork_id'])query('UPDATE artworks SET sold_at=?,updated_at=? WHERE id=?',[now(),now(),$l['artwork_id']]);
        }
        $oid=create_order($uid,'cart',$lines,$token); query('DELETE FROM cart_items WHERE buyer_id=?',[$uid]); return $oid;
    });
}
function rental_quote(array $l,string $start,string $end): array {
    $s=DateTimeImmutable::createFromFormat('!Y-m-d',$start);$e=DateTimeImmutable::createFromFormat('!Y-m-d',$end);
    if(!$s||!$e||$s->format('Y-m-d')!==$start||$e->format('Y-m-d')!==$end||$start<date('Y-m-d')||$end<=$start)throw new DomainException('Choose valid future pickup and return dates.');
    $days=(int)$s->diff($e)->days;if($days<1||$days>30)throw new DomainException('Rent for 1 to 30 days.');
    $tier=one('SELECT * FROM rental_rate_tiers WHERE is_active=1 AND min_days<=? AND (max_days IS NULL OR max_days>=?)',[$days,$days]);
    if(!$tier)throw new DomainException('No fee tier for these dates.');
    $total=$days*$l['unit_price_centavos'];return ['days'=>$days,'tier'=>$tier,'total'=>$total,'fee'=>intdiv($total*$tier['commission_bps']+5000,10000)];
}
function cancel_order_item(int $id,int $admin,string $reason): void {
    tx(function()use($id,$admin,$reason){
        $i=one('SELECT i.*,o.payment_status,o.buyer_id FROM order_items i JOIN orders o ON o.id=i.order_id WHERE i.id=?',[$id]);
        if(!$i)throw new DomainException('Order item missing.');if($i['status']==='cancelled')return;
        if($i['status']==='completed')throw new DomainException('Completed items cannot be cancelled.');
        if($i['rental_booking_id']){$r=one('SELECT status FROM rental_bookings WHERE id=?',[$i['rental_booking_id']]);if(in_array($r['status'],['collected','returned']))throw new DomainException('Collected equipment must be returned, not cancelled.');query("UPDATE rental_bookings SET status='cancelled',updated_at=? WHERE id=?",[now(),$i['rental_booking_id']]);}
        elseif($i['commission_request_id'])query("UPDATE commission_requests SET status='cancelled',updated_at=? WHERE id=?",[now(),$i['commission_request_id']]);
        else {$l=listing((int)$i['listing_id']);$state=$i['auction_id']?'archived':(in_array($l['status'],['hidden','archived'])?$l['status']:'published');query('UPDATE listings SET stock_qty=stock_qty+?,status=?,updated_at=? WHERE id=?',[$i['quantity'],$state,now(),$l['id']]);if($l['artwork_id'])query('UPDATE artworks SET sold_at=NULL,updated_at=? WHERE id=?',[now(),$l['artwork_id']]);}
        query("UPDATE order_items SET status='cancelled',cancelled_by=?,cancellation_reason=?,cancelled_at=?,updated_at=? WHERE id=?",[$admin,$reason,now(),now(),$id]);
        if(!one("SELECT id FROM order_items WHERE order_id=? AND status<>'cancelled'",[$i['order_id']]))query('UPDATE orders SET payment_status=?,updated_at=? WHERE id=?',[in_array($i['payment_status'],['demo_paid','demo_refunded'])?'demo_refunded':'void',now(),$i['order_id']]);
        notify((int)$i['buyer_id'],'Order item cancelled by Admin: '.$i['item_title'].' — '.$reason,'/?p=orders','cancel-item:'.$id);
    });
}
