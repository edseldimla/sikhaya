<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
header('Content-Type: application/json');header('Cache-Control: no-store');
reconcile();
$a=one("SELECT a.*,l.unit_price_centavos FROM auctions a JOIN listings l ON l.id=a.listing_id WHERE a.id=? AND l.status NOT IN ('hidden','archived')",[(int)($_GET['auction']??0)]);
if(!$a){http_response_code(404);echo json_encode(['error'=>'Auction not found']);exit;}
$high=one('SELECT amount_centavos FROM bids WHERE auction_id=? ORDER BY amount_centavos DESC,id LIMIT 1',[$a['id']]);
echo json_encode(['status'=>$a['status'],'ends_at'=>$a['ends_at'],'server_time'=>now(),'highest_centavos'=>$high['amount_centavos']??$a['unit_price_centavos'],'minimum_centavos'=>$high?$high['amount_centavos']+$a['min_increment_centavos']:$a['unit_price_centavos']]);
