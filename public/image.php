<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
$m=one('SELECT * FROM media WHERE id=?',[(int)($_GET['id']??0)]);
if(!$m){http_response_code(404);exit;}
$u=user();$public=(bool)one("SELECT i.media_id FROM artwork_images i JOIN artworks a ON a.id=i.artwork_id JOIN users u ON u.id=a.artist_id WHERE i.media_id=? AND a.status='visible' AND u.status='active' UNION SELECT i.media_id FROM listing_images i JOIN listings l ON l.id=i.listing_id JOIN users u ON u.id=l.seller_id WHERE i.media_id=? AND l.status IN ('published','sold_out') AND u.status='active' UNION SELECT i.media_id FROM post_images i JOIN posts p ON p.id=i.post_id JOIN users u ON u.id=p.author_id WHERE i.media_id=? AND p.status='visible' AND u.status='active' LIMIT 1",[$m['id'],$m['id'],$m['id']]);
$private=$u&&($u['id']===$m['owner_id']||$u['role']==='admin'||one('SELECT r.id FROM commission_requests r JOIN listings l ON l.id=r.offer_listing_id LEFT JOIN commission_messages c ON c.request_id=r.id WHERE (r.reference_media_id=? OR c.attachment_media_id=?) AND (r.buyer_id=? OR l.seller_id=?) LIMIT 1',[$m['id'],$m['id'],$u['id'],$u['id']]));
if(!$public&&!$private){http_response_code(403);exit;}
$relative=explode('#',$m['storage_path'])[0];$base=str_starts_with($relative,'assets/images/')?ROOT.'/public/':ROOT.'/storage/';$resolved=realpath($base.$relative);$allowed=realpath($base);
if(!$resolved||!$allowed||!str_starts_with(strtolower($resolved),strtolower($allowed).DIRECTORY_SEPARATOR)||!is_file($resolved)){http_response_code(404);exit;}
header('Content-Type: '.$m['mime_type']);header('Cache-Control: '.($public?'public, max-age=3600':'private, no-store'));header('Content-Length: '.filesize($resolved));session_write_close();readfile($resolved);
