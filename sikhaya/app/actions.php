<?php
function actions(): void {
    if($_SERVER['REQUEST_METHOD']!=='POST')return;
    $action=(string)($_POST['action']??'');
    $return=(string)($_POST['return']??'/?p=home');
    if(!str_starts_with($return,'/?')||str_contains($return,"\r")||str_contains($return,"\n"))$return='/?p=home';
    try {
        check_csrf();
        if($action==='login'){
            $u=one('SELECT * FROM users WHERE email=?',[required('email',180)]);
            if(!$u||!password_verify((string)($_POST['password']??''),$u['password_hash'])||$u['status']!=='active')throw new DomainException('Email or password is incorrect, or account is suspended.');
            session_regenerate_id(true);$_SESSION['uid']=$u['id'];$_SESSION['csrf']=bin2hex(random_bytes(24));
            $_SESSION['flash']=['success','Welcome back, '.$u['display_name'].'.'];go('home');
        }
        if($action==='register'){
            $name=required('display_name',80);$email=required('email',180);$pass=required('password',200);
            if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new DomainException('Enter a valid email.');
            if(strlen($pass)<8||$pass!==($_POST['confirm_password']??''))throw new DomainException('Passwords must match and have at least 8 characters.');
            if(one('SELECT id FROM users WHERE email=?',[$email]))throw new DomainException('That email already has an account.');
            $role=($_POST['role']??'buyer')==='artist'?'artist':'buyer';
            $id=tx(function()use($name,$email,$pass,$role){$id=insert('users',['display_name'=>$name,'email'=>$email,'password_hash'=>password_hash($pass,PASSWORD_DEFAULT),'role'=>$role,'created_at'=>now(),'updated_at'=>now()]);if($role==='artist')insert('artist_profiles',['user_id'=>$id,'created_at'=>now(),'updated_at'=>now()]);return$id;});
            session_regenerate_id(true);$_SESSION['uid']=$id;$_SESSION['flash']=['success','Your account is ready. Welcome to SIKHAYA.'];go('home');
        }
        $u=need_user();$uid=(int)$u['id'];$id=(int)($_POST['id']??0);
        switch($action){
        case 'logout': $_SESSION=[];session_regenerate_id(true);go('home');
        case 'profile':
            $name=required('display_name',80);$bio=trim((string)($_POST['bio']??''));if(mb_strlen($bio)>2000)throw new DomainException('Keep your bio under 2,000 characters.');
            query('UPDATE users SET display_name=?,bio=?,region=?,updated_at=? WHERE id=?',[$name,$bio,substr(trim((string)($_POST['region']??'')),0,80),now(),$uid]);
            if(!empty($_POST['new_password'])){if(!password_verify((string)($_POST['current_password']??''),$u['password_hash'])||strlen($_POST['new_password'])<8)throw new DomainException('Check your current password and use at least 8 characters for the new one.');query('UPDATE users SET password_hash=? WHERE id=?',[password_hash($_POST['new_password'],PASSWORD_DEFAULT),$uid]);}
            break;
        case 'become_artist':
            if($u['role']!=='buyer')throw new DomainException('Your artist profile is already enabled.');
            tx(function()use($uid){query("UPDATE users SET role='artist',updated_at=? WHERE id=?",[now(),$uid]);insert('artist_profiles',['user_id'=>$uid,'created_at'=>now(),'updated_at'=>now()]);});break;
        case 'save':
            $type=(string)($_POST['type']??'listing');$targets=['listing'=>['listings','listing_id'],'artwork'=>['artworks','artwork_id'],'post'=>['posts','post_id'],'artist'=>['artist_profiles','artist_id']];
            if(!isset($targets[$type]))throw new DomainException('Invalid saved item.');[$table,$col]=$targets[$type];$pk=$type==='artist'?'user_id':'id';
            if(!one("SELECT $pk FROM $table WHERE $pk=?",[$id]))throw new DomainException('Item not found.');
            $saved=one("SELECT id FROM saved_items WHERE user_id=? AND $col=?",[$uid,$id]);
            if($saved)query('DELETE FROM saved_items WHERE id=?',[$saved['id']]);else insert('saved_items',['user_id'=>$uid,$col=>$id,'created_at'=>now()]);break;
        case 'cart_add':
            $l=listing($id);available($l);if($uid===$l['seller_id']||!in_array($l['section'],['likha_lokal','museo','kagamit']))throw new DomainException('This item cannot be added to your cart.');
            $qty=max(1,(int)($_POST['quantity']??1));$old=one('SELECT quantity FROM cart_items WHERE buyer_id=? AND listing_id=?',[$uid,$id]);
            if(($old['quantity']??0)+$qty>$l['stock_qty'])throw new DomainException('Not enough stock for that quantity.');
            query('INSERT INTO cart_items(buyer_id,listing_id,quantity,created_at,updated_at) VALUES(?,?,?,?,?) ON CONFLICT(buyer_id,listing_id) DO UPDATE SET quantity=quantity+excluded.quantity,updated_at=excluded.updated_at',[$uid,$id,$qty,now(),now()]);
            $_SESSION['flash']=['success','Added to your cart.'];header('Location: '.$return,true,303);exit;
        case 'cart_update':
            $qty=(int)($_POST['quantity']??0);if($qty<=0)query('DELETE FROM cart_items WHERE id=? AND buyer_id=?',[$id,$uid]);else{$c=one('SELECT listing_id FROM cart_items WHERE id=? AND buyer_id=?',[$id,$uid]);if(!$c)throw new DomainException('Cart item missing.');$l=listing((int)$c['listing_id']);if($qty>$l['stock_qty'])throw new DomainException('Quantity exceeds available stock.');query('UPDATE cart_items SET quantity=?,updated_at=? WHERE id=? AND buyer_id=?',[$qty,now(),$id,$uid]);}break;
        case 'checkout':$oid=pay_cart($uid,required('request_token',100),(array)($_POST['prices']??[]));$_SESSION['flash']=['success','Demo checkout complete. No money was charged.'];go('orders',['id'=>$oid]);
        case 'bid':place_bid($id,pesos(required('amount',20)),$uid,required('request_token',100));break;
        case 'rental_request':
            tx(function()use($id,$uid){$l=listing($id);available($l);if($l['section']!=='arkilikha'||$l['seller_id']===$uid)throw new DomainException('Choose another provider’s equipment.');$start=required('start_date',10);$end=required('end_date',10);$q=rental_quote($l,$start,$end);if(has_conflict($id,$start,$end))throw new DomainException('These dates are already reserved.');$rid=insert('rental_bookings',['listing_id'=>$id,'renter_id'=>$uid,'start_date'=>$start,'end_date'=>$end,'days'=>$q['days'],'daily_rate_centavos'=>$l['unit_price_centavos'],'rate_tier_id'=>$q['tier']['id'],'commission_bps'=>$q['tier']['commission_bps'],'rental_total_centavos'=>$q['total'],'platform_fee_centavos'=>$q['fee'],'created_at'=>now(),'updated_at'=>now()]);notify((int)$l['seller_id'],'New rental request for '.$l['title'],'/?p=work','rental:'.$rid);});go('work');
        case 'rental_step':
            tx(function()use($id,$uid,$u){$r=one('SELECT r.*,l.seller_id FROM rental_bookings r JOIN listings l ON l.id=r.listing_id WHERE r.id=?',[$id]);if(!$r)throw new DomainException('Booking not found.');$step=(string)($_POST['step']??'');$owner=$r['seller_id']===$uid;
                if($step==='cancel'&&$r['renter_id']===$uid&&in_array($r['status'],['requested','approved']))$next='cancelled';
                elseif($owner&&$step==='approve'&&$r['status']==='requested'){$l=listing((int)$r['listing_id']);available($l);rental_quote($l,$r['start_date'],$r['end_date']);if(has_conflict((int)$r['listing_id'],$r['start_date'],$r['end_date'],$id))throw new DomainException('Another booking now overlaps these dates.');$next='approved';query('UPDATE rental_bookings SET hold_expires_at=? WHERE id=?',[later('+24 hours'),$id]);}
                elseif($owner&&$step==='reject'&&$r['status']==='requested')$next='rejected';
                elseif($owner&&$step==='collect'&&$r['status']==='confirmed'){if(has_conflict((int)$r['listing_id'],$r['start_date'],$r['end_date'],$id)&&one("SELECT id FROM rental_bookings WHERE listing_id=? AND id<>? AND status='collected'",[$r['listing_id'],$id]))throw new DomainException('Previous equipment must be returned first.');$next='collected';query('UPDATE rental_bookings SET collected_at=? WHERE id=?',[now(),$id]);}
                elseif($owner&&$step==='return'&&$r['status']==='collected'){$next='returned';query("UPDATE rental_bookings SET returned_at=? WHERE id=?",[now(),$id]);query("UPDATE order_items SET status='completed',updated_at=? WHERE rental_booking_id=?",[now(),$id]);}
                else throw new DomainException('This rental action is not allowed.');
                query('UPDATE rental_bookings SET status=?,updated_at=? WHERE id=?',[$next,now(),$id]);notify((int)$r['renter_id'],'Rental #'.$id.' is now '.$next,'/?p=work','rental:'.$id.':'.$next);
            });break;
        case 'commission_request':
            tx(function()use($id,$uid){$l=listing($id);available($l);if($l['section']!=='gawang_hiling'||$l['seller_id']===$uid||slots($id)<1)throw new DomainException('This offer is not accepting requests.');$brief=required('brief');$due=trim((string)($_POST['due_date']??''));if($due&&$due<date('Y-m-d'))throw new DomainException('Choose a future due date.');$mid=upload_image('image',$uid);$rid=insert('commission_requests',['offer_listing_id'=>$id,'buyer_id'=>$uid,'brief'=>$brief,'budget_centavos'=>empty($_POST['budget'])?null:pesos($_POST['budget']),'requested_due_date'=>$due?:null,'reference_media_id'=>$mid,'created_at'=>now(),'updated_at'=>now()]);notify((int)$l['seller_id'],'New custom-art request: '.$l['title'],'/?p=work','commission:'.$rid);});go('work');
        case 'commission_step':
            tx(function()use($id,$uid){$r=one('SELECT r.*,l.seller_id FROM commission_requests r JOIN listings l ON l.id=r.offer_listing_id WHERE r.id=?',[$id]);if(!$r)throw new DomainException('Request missing.');$step=(string)($_POST['step']??'');$owner=$r['seller_id']===$uid;$buyer=$r['buyer_id']===$uid;
                if($step==='quote'&&$owner&&in_array($r['status'],['requested','quoted'])){$price=pesos(required('price',20));$due=required('due_date',10);if($price<=0||$due<date('Y-m-d')||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$due))throw new DomainException('Use a positive price and future due date.');$offer=one('SELECT * FROM commission_offers WHERE listing_id=?',[$r['offer_listing_id']]);query("UPDATE commission_requests SET quoted_price_centavos=?,quoted_due_date=?,agreed_revision_limit=?,quote_expires_at=?,status='quoted',updated_at=? WHERE id=?",[$price,$due,$offer['revision_limit'],later('+72 hours'),now(),$id]);}
                elseif($step==='accept'&&$buyer&&$r['status']==='quoted'&&$r['quote_expires_at']>now()){if(slots((int)$r['offer_listing_id'])<1)throw new DomainException('The last slot was just taken.');available(listing((int)$r['offer_listing_id']));query("UPDATE commission_requests SET status='accepted',accepted_at=?,checkout_expires_at=?,updated_at=? WHERE id=?",[now(),later('+24 hours'),now(),$id]);}
                elseif(in_array($step,['reject','cancel'])&&(($owner&&in_array($r['status'],['requested','quoted']))||($buyer&&in_array($r['status'],['requested','quoted','accepted'])))){query('UPDATE commission_requests SET status=?,updated_at=? WHERE id=?',[$step==='reject'?'rejected':'cancelled',now(),$id]);}
                elseif($step==='deliver'&&$owner&&$r['status']==='in_progress'){query("UPDATE commission_requests SET status='delivered',updated_at=? WHERE id=?",[now(),$id]);}
                elseif($step==='complete'&&$buyer&&$r['status']==='delivered'){query("UPDATE commission_requests SET status='completed',completed_at=?,updated_at=? WHERE id=?",[now(),now(),$id]);query("UPDATE order_items SET status='completed',updated_at=? WHERE commission_request_id=?",[now(),$id]);}
                elseif($step==='revision'&&$buyer&&$r['status']==='delivered'&&$r['revisions_used']<$r['agreed_revision_limit']){query("UPDATE commission_requests SET status='in_progress',revisions_used=revisions_used+1,updated_at=? WHERE id=?",[now(),$id]);insert('commission_messages',['request_id'=>$id,'author_id'=>$uid,'kind'=>'revision_request','body'=>required('body'),'created_at'=>now()]);}
                else throw new DomainException('This commission action is not available.');
                notify((int)($owner?$r['buyer_id']:$r['seller_id']),'Commission #'.$id.': '.$step,'/?p=work','commission:'.$id.':'.$step.':'.bin2hex(random_bytes(3)));
            });break;
        case 'message':
            $r=one('SELECT r.*,l.seller_id FROM commission_requests r JOIN listings l ON l.id=r.offer_listing_id WHERE r.id=?',[$id]);if(!$r||!in_array($uid,[$r['buyer_id'],$r['seller_id']],true))throw new DomainException('This project thread is private.');$mid=upload_image('image',$uid);insert('commission_messages',['request_id'=>$id,'author_id'=>$uid,'kind'=>'note','body'=>required('body'),'attachment_media_id'=>$mid,'created_at'=>now()]);break;
        case 'special_pay':
            $type=required('type',20);$token=required('request_token',100);
            $oid=tx(function()use($id,$uid,$type,$token){$old=one('SELECT id FROM orders WHERE checkout_token=? AND buyer_id=?',[$token,$uid]);if($old)return(int)$old['id'];
                if($type==='auction'){$o=one("SELECT * FROM orders WHERE id=? AND buyer_id=? AND payment_status='awaiting_demo_payment' AND payment_due_at>?",[$id,$uid,now()]);if(!$o)throw new DomainException('This winner order is not payable.');$i=one('SELECT * FROM order_items WHERE order_id=?',[$id]);$l=listing((int)$i['listing_id']);available($l);query("UPDATE orders SET payment_status='demo_paid',demo_paid_at=?,updated_at=? WHERE id=?",[now(),now(),$id]);query("UPDATE order_items SET status='confirmed',updated_at=? WHERE order_id=?",[now(),$id]);query("UPDATE listings SET status='sold_out',stock_qty=0,updated_at=? WHERE id=?",[now(),$l['id']]);query('UPDATE artworks SET sold_at=?,updated_at=? WHERE id=?',[now(),now(),$l['artwork_id']]);return$id;}
                if($type==='rental'){$r=one("SELECT * FROM rental_bookings WHERE id=? AND renter_id=? AND status='approved' AND hold_expires_at>?",[$id,$uid,now()]);if(!$r)throw new DomainException('Your rental hold has expired or is not approved.');$l=listing((int)$r['listing_id']);available($l);rental_quote($l,$r['start_date'],$r['end_date']);if(has_conflict((int)$l['id'],$r['start_date'],$r['end_date'],$id))throw new DomainException('These dates are no longer available.');$line=['listing'=>$l,'total'=>$r['rental_total_centavos'],'fee'=>$r['platform_fee_centavos'],'bps'=>$r['commission_bps'],'rental_id'=>$id];query("UPDATE rental_bookings SET status='confirmed',updated_at=? WHERE id=?",[now(),$id]);}
                elseif($type==='commission'){$r=one("SELECT * FROM commission_requests WHERE id=? AND buyer_id=? AND status='accepted' AND checkout_expires_at>?",[$id,$uid,now()]);if(!$r)throw new DomainException('Your accepted quote is not payable.');$l=listing((int)$r['offer_listing_id']);available($l);$line=['listing'=>$l,'total'=>$r['quoted_price_centavos'],'commission_id'=>$id];query("UPDATE commission_requests SET status='in_progress',updated_at=? WHERE id=?",[now(),$id]);}
                else throw new DomainException('Unknown checkout.');
                return create_order($uid,$type,[$line],$token);
            });$_SESSION['flash']=['success','Demo payment saved. No real money was charged.'];go('orders',['id'=>$oid]);
        case 'listing_create':
            need_user('artist');$section=required('section',30);if(!isset(SECTIONS[$section]))throw new DomainException('Choose a section.');$title=required('title',120);$desc=required('description');$price=pesos(required('price',20));if($price<1)throw new DomainException('Price must be positive.');$qty=max(1,min(9999,(int)($_POST['stock']??1)));$category=required('category',80);
            $mid=upload_image('image',$uid);if(!$mid)throw new DomainException('Upload an image for this listing.');
            tx(function()use($section,$title,$desc,$price,$qty,$category,$mid,$uid){$art=null;if(in_array($section,['museo','subasta'])||!empty($_POST['original'])){$art=insert('artworks',['artist_id'=>$uid,'title'=>$title,'description'=>$desc,'medium'=>trim((string)($_POST['medium']??'Mixed media')),'region'=>user()['region'],'year_created'=>(int)date('Y'),'created_at'=>now(),'updated_at'=>now()]);insert('artwork_images',['artwork_id'=>$art,'media_id'=>$mid,'sort_order'=>0]);$qty=1;}
                $lid=insert('listings',['seller_id'=>$uid,'artwork_id'=>$art,'section'=>$section,'category'=>$category,'title'=>$title,'description'=>$desc,'unit_price_centavos'=>$price,'stock_qty'=>$qty,'created_at'=>now(),'updated_at'=>now()]);if(!$art)insert('listing_images',['listing_id'=>$lid,'media_id'=>$mid,'sort_order'=>0]);
                if($section==='subasta'){$start=new DateTimeImmutable(required('start_time',30));$end=new DateTimeImmutable(required('end_time',30));if($end<=$start||$end<=new DateTimeImmutable())throw new DomainException('Auction end must be in the future and after start.');insert('auctions',['listing_id'=>$lid,'starts_at'=>$start->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),'ends_at'=>$end->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),'min_increment_centavos'=>max(100,pesos(required('increment',20))),'status'=>$start>new DateTimeImmutable()?'scheduled':'live','created_at'=>now(),'updated_at'=>now()]);}
                if($section==='gawang_hiling')insert('commission_offers',['listing_id'=>$lid,'slot_capacity'=>max(1,min(20,(int)($_POST['capacity']??3))),'estimated_days'=>max(1,min(365,(int)($_POST['days']??14))),'revision_limit'=>max(0,min(10,(int)($_POST['revisions']??2))),'terms'=>trim((string)($_POST['terms']??'Two revisions. Final price agreed before work.')),'created_at'=>now(),'updated_at'=>now()]);
            });go('studio');
        case 'listing_edit':
            need_user('artist');$l=listing($id);if($l['seller_id']!==$uid||$l['status']!=='published'||$l['section']==='subasta')throw new DomainException('This listing cannot be edited.');$price=pesos(required('price',20));$qty=(int)($_POST['stock']??1);if($price<=0||$qty<0||($l['artwork_id']&&$qty!==1))throw new DomainException('Check price and quantity.');query('UPDATE listings SET title=?,description=?,unit_price_centavos=?,stock_qty=?,updated_at=? WHERE id=?',[required('title',120),required('description'),$price,$qty,now(),$id]);break;
        case 'listing_pause':
            need_user('artist');$l=listing($id);if($l['seller_id']!==$uid||$l['section']==='subasta'||!in_array($l['status'],['published','paused']))throw new DomainException('This item cannot be paused.');query('UPDATE listings SET status=?,updated_at=? WHERE id=?',[$l['status']==='published'?'paused':'published',now(),$id]);break;
        case 'post_create':
            $body=required('body',3000);$images=upload_images($uid,4);if(!$images)throw new DomainException('Add an image to your artwork post.');$pid=tx(function()use($uid,$body,$images){$pid=insert('posts',['author_id'=>$uid,'body'=>$body,'created_at'=>now(),'updated_at'=>now()]);foreach($images as $i=>$mid)insert('post_images',['post_id'=>$pid,'media_id'=>$mid,'sort_order'=>$i]);return$pid;});go('feed',['post'=>$pid]);
        case 'post_edit':
            query('UPDATE posts SET body=?,updated_at=? WHERE id=? AND author_id=? AND shared_post_id IS NULL',[required('body',3000),now(),$id,$uid]);break;
        case 'post_delete':query("UPDATE posts SET status='deleted',updated_at=? WHERE id=? AND author_id=?",[now(),$id,$uid]);break;
        case 'react':
            if(!one("SELECT id FROM posts WHERE id=? AND status='visible'",[$id]))throw new DomainException('Post unavailable.');$reaction=required('reaction',10);if(!in_array($reaction,['like','love','wow']))throw new DomainException('Choose a reaction.');$old=one('SELECT reaction FROM post_reactions WHERE post_id=? AND user_id=?',[$id,$uid]);if($old&&$old['reaction']===$reaction)query('DELETE FROM post_reactions WHERE post_id=? AND user_id=?',[$id,$uid]);else query('INSERT INTO post_reactions(post_id,user_id,reaction,created_at) VALUES(?,?,?,?) ON CONFLICT(post_id,user_id) DO UPDATE SET reaction=excluded.reaction',[$id,$uid,$reaction,now()]);break;
        case 'comment':
            if(!one("SELECT id FROM posts WHERE id=? AND status='visible'",[$id]))throw new DomainException('Post unavailable.');$parent=(int)($_POST['parent']??0);if($parent&&!one('SELECT id FROM comments WHERE id=? AND post_id=? AND parent_comment_id IS NULL',[$parent,$id]))throw new DomainException('Reply must be on this post.');insert('comments',['post_id'=>$id,'author_id'=>$uid,'parent_comment_id'=>$parent?:null,'body'=>required('body',1500),'created_at'=>now(),'updated_at'=>now()]);break;
        case 'comment_delete':query("UPDATE comments SET status='deleted',updated_at=? WHERE id=? AND author_id=?",[now(),$id,$uid]);break;
        case 'share':
            $p=one("SELECT * FROM posts WHERE id=? AND status='visible'",[$id]);if(!$p)throw new DomainException('Post unavailable.');$original=$p['shared_post_id']?:$id;if(!one("SELECT id FROM posts WHERE id=? AND status='visible'",[$original]))throw new DomainException('Original unavailable.');if(!one("SELECT id FROM posts WHERE author_id=? AND shared_post_id=? AND status='visible'",[$uid,$original]))insert('posts',['author_id'=>$uid,'shared_post_id'=>$original,'body'=>trim((string)($_POST['body']??'')),'created_at'=>now(),'updated_at'=>now()]);break;
        case 'badge_apply':
            need_user('artist');if(query("SELECT COUNT(*) FROM artworks WHERE artist_id=? AND status='visible'",[$uid])->fetchColumn()<3)throw new DomainException('Add at least three visible portfolio works first.');if(one("SELECT id FROM badge_reviews WHERE artist_id=? AND status='pending'",[$uid]))throw new DomainException('Your application is already pending.');insert('badge_reviews',['artist_id'=>$uid,'statement'=>required('statement',2000),'created_at'=>now()]);break;
        case 'report':
            $map=['listing'=>'listing_id','post'=>'post_id','artwork'=>'artwork_id','comment'=>'comment_id','user'=>'reported_user_id'];$type=required('type',20);if(!isset($map[$type]))throw new DomainException('Invalid report.');insert('reports',['reporter_id'=>$uid,$map[$type]=>$id,'reason'=>required('reason',1000),'created_at'=>now()]);break;
        case 'read_notices':query('UPDATE notifications SET read_at=? WHERE user_id=?',[now(),$uid]);break;
        case 'fulfill':
            $i=one('SELECT i.*,o.payment_status FROM order_items i JOIN orders o ON o.id=i.order_id WHERE i.id=? AND i.seller_id=?',[$id,$uid]);$next=(string)($_POST['step']??'');if(!$i||$i['payment_status']!=='demo_paid'||$i['rental_booking_id']||$i['commission_request_id']||!(($i['status']==='confirmed'&&$next==='ready')||($i['status']==='ready'&&$next==='completed')))throw new DomainException('This order cannot move to that state.');query('UPDATE order_items SET status=?,updated_at=? WHERE id=?',[$next,now(),$id]);break;
        case 'admin_badge':
            need_user('admin');$r=one("SELECT * FROM badge_reviews WHERE id=? AND status='pending'",[$id]);if(!$r)throw new DomainException('Application already reviewed.');$approved=($_POST['decision']??'')==='approve';$reason=required('reason',1000);tx(function()use($approved,$reason,$id,$uid,$r){query('UPDATE badge_reviews SET status=?,reviewed_by=?,decision_reason=?,reviewed_at=? WHERE id=?',[$approved?'approved':'rejected',$uid,$reason,now(),$id]);if($approved)query("UPDATE artist_profiles SET badge='dalubhasa',updated_at=? WHERE user_id=?",[now(),$r['artist_id']]);notify((int)$r['artist_id'],'Badge review: '.($approved?'approved':'rejected'),'/?p=studio','badge:'.$id);});break;
        case 'admin_moderate':
            need_user('admin');$types=['listing'=>['listings','listing_id'],'post'=>['posts','post_id'],'artwork'=>['artworks','artwork_id'],'comment'=>['comments','comment_id'],'user'=>['users','target_user_id']];$type=required('type',20);if(!isset($types[$type]))throw new DomainException('Invalid target.');[$table,$col]=$types[$type];$target=one("SELECT * FROM $table WHERE id=?",[$id]);if(!$target||($type==='user'&&$target['role']==='admin'))throw new DomainException('This record cannot be moderated.');$reason=required('reason',1000);$restore=($_POST['step']??'')==='restore';$next=$type==='user'?($restore?'active':'suspended'):($restore?($type==='listing'?'published':'visible'):'hidden');
            tx(function()use($uid,$table,$col,$type,$id,$reason,$next,$target,$restore){query("UPDATE $table SET status=?,updated_at=? WHERE id=?",[$next,now(),$id]);insert('moderation_actions',['admin_id'=>$uid,$col=>$id,'action'=>$type==='user'?($restore?'unsuspend':'suspend'):($restore?'restore':'hide'),'reason'=>$reason,'previous_status'=>$target['status'],'new_status'=>$next,'created_at'=>now()]);});break;
        case 'admin_revoke':
            need_user('admin');$reason=required('reason',1000);tx(function()use($id,$uid,$reason){$a=one("SELECT user_id FROM artist_profiles WHERE user_id=? AND badge='dalubhasa'",[$id]);if(!$a)throw new DomainException('This artist does not have Dalubhasa.');query("UPDATE artist_profiles SET badge='sibol',updated_at=? WHERE user_id=?",[now(),$id]);insert('badge_reviews',['artist_id'=>$id,'kind'=>'revocation','statement'=>$reason,'status'=>'approved','reviewed_by'=>$uid,'decision_reason'=>$reason,'created_at'=>now(),'reviewed_at'=>now()]);});break;
        case 'admin_cancel':need_user('admin');cancel_order_item($id,$uid,required('reason',1000));break;
        case 'offer_update':
            need_user('artist');tx(function()use($id,$uid){$l=listing($id);if($l['seller_id']!==$uid||$l['section']!=='gawang_hiling')throw new DomainException('This offer is not yours.');$capacity=max(1,min(20,(int)($_POST['capacity']??1)));$occupied=(int)query("SELECT COUNT(*) FROM commission_requests WHERE offer_listing_id=? AND status IN ('accepted','in_progress','delivered')",[$id])->fetchColumn();if($capacity<$occupied)throw new DomainException('Capacity cannot be below occupied slots.');query('UPDATE commission_offers SET slot_capacity=?,revision_limit=?,estimated_days=?,terms=?,updated_at=? WHERE listing_id=?',[$capacity,max(0,min(10,(int)($_POST['revisions']??2))),max(1,min(365,(int)($_POST['days']??14))),required('terms',2000),now(),$id]);});break;
        case 'admin_report':need_user('admin');query('UPDATE reports SET status=?,reviewed_by=?,resolution_note=?,resolved_at=? WHERE id=? AND status=\'open\'',[(($_POST['decision']??'')==='dismiss'?'dismissed':'resolved'),$uid,required('reason',1000),now(),$id]);break;
        case 'admin_rates':
            need_user('admin');$rates=[];for($i=1;$i<=3;$i++){$n=filter_var($_POST['rate'.$i]??null,FILTER_VALIDATE_FLOAT);if($n===false||$n<0||$n>100)throw new DomainException('Rates must be between 0 and 100 percent.');$rates[$i]=(int)round($n*100);}tx(function()use($rates){foreach($rates as $id=>$bps)query('UPDATE rental_rate_tiers SET commission_bps=?,updated_at=? WHERE id=?',[$bps,now(),$id]);});break;
        default:throw new DomainException('Unknown action.');
        }
        $_SESSION['flash']=['success','Saved successfully.'];
    } catch(DomainException $e){$_SESSION['flash']=['error',$e->getMessage()];}
      catch(Throwable $e){error_log((string)$e,3,ROOT.'/storage/logs/errors.log');$_SESSION['flash']=['error','We could not save that change. Please check the fields and try again.'];}
    header('Location: '.$return,true,303);exit;
}
