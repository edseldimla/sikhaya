<?php
declare(strict_types=1);
const ROOT = __DIR__ . '/..';
date_default_timezone_set('Asia/Manila');
foreach (['database','uploads','sessions','logs','backups'] as $folder) {
    if (!is_dir(ROOT.'/storage/'.$folder)) mkdir(ROOT.'/storage/'.$folder, 0775, true);
}
ini_set('session.save_path', ROOT.'/storage/sessions');
if (PHP_SAPI !== 'cli') {
    session_name('sikhaya_session');
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','path'=>'/']);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header("Referrer-Policy: same-origin");
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");
}
function database_path(): string { return getenv('SIKHAYA_DB') ?: ROOT.'/storage/database/sikhaya.sqlite'; }
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        if (!is_file(database_path())) throw new RuntimeException('Run scripts/setup.php first.');
        $pdo = new PDO('sqlite:'.database_path(), null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
        $pdo->exec('PRAGMA foreign_keys=ON; PRAGMA busy_timeout=5000;');
    }
    return $pdo;
}
function query(string $sql, array $args=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($args); return $s; }
function one(string $sql,array $args=[]): ?array { return query($sql,$args)->fetch() ?: null; }
function all(string $sql,array $args=[]): array { return query($sql,$args)->fetchAll(); }
function insert(string $table,array $fields): int {
    $cols=array_keys($fields);
    query('INSERT INTO '.$table.' ('.implode(',',$cols).') VALUES ('.implode(',',array_fill(0,count($cols),'?')).')', array_values($fields));
    return (int)db()->lastInsertId();
}
function now(): string { return gmdate('Y-m-d\TH:i:s\Z'); }
function later(string $interval): string { return gmdate('Y-m-d\TH:i:s\Z',strtotime($interval)); }
function tx(callable $work): mixed {
    db()->exec('BEGIN IMMEDIATE');
    try { $result=$work(); db()->exec('COMMIT'); return $result; }
    catch(Throwable $e) { db()->exec('ROLLBACK'); throw $e; }
}
function e(mixed $s): string { return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function money(int|float $c): string { return '₱'.number_format($c/100, $c%100 ? 2 : 0); }
function pesos(string $text): int { if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/',$text)) throw new DomainException('Enter a valid peso amount.'); return (int)round((float)$text*100); }
function required(string $key,int $max=4000): string { $v=trim((string)($_POST[$key]??'')); if($v===''||mb_strlen($v)>$max) throw new DomainException('Please complete '.str_replace('_',' ',$key).' (up to '.$max.' characters).'); return $v; }
function user(): ?array { return isset($_SESSION['uid']) ? one('SELECT * FROM users WHERE id=?',[(int)$_SESSION['uid']]) : null; }
function need_user(?string $role=null): array { $u=user(); if(!$u) { $_SESSION['flash']=['error','Please sign in to continue.']; go('login'); } if($u['status']!=='active') throw new DomainException('This account is suspended.'); if($role && $u['role']!==$role) throw new DomainException('This action is for '.$role.' accounts.'); return $u; }
function go(string $page,array $params=[]): never { header('Location: /?'.http_build_query(['p'=>$page]+$params),true,303); exit; }
function csrf(): string { return $_SESSION['csrf']??=bin2hex(random_bytes(24)); }
function token_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function check_csrf(): void { if(!hash_equals(csrf(),(string)($_POST['csrf']??''))) throw new DomainException('Your form expired. Refresh and try again.'); }
function notify(int $uid,string $message,string $path,string $key): void { query('INSERT OR IGNORE INTO notifications(user_id,message,target_path,event_key,created_at) VALUES(?,?,?,?,?)',[$uid,$message,$path,$uid.':'.$key,now()]); }
function listing(int $id): array {
    $l=one('SELECT l.*,u.display_name,u.region,a.badge,w.medium,w.sold_at,w.status AS artwork_status FROM listings l JOIN users u ON u.id=l.seller_id JOIN artist_profiles a ON a.user_id=u.id LEFT JOIN artworks w ON w.id=l.artwork_id WHERE l.id=?',[$id]);
    if(!$l) throw new DomainException('This listing was not found.'); return $l;
}
function available(array $l): void { if($l['status']!=='published'||$l['stock_qty']<1||($l['artwork_id']&&($l['sold_at']||$l['artwork_status']!=='visible'))||one('SELECT id FROM users WHERE id=? AND status<>? ',[$l['seller_id'],'active'])) throw new DomainException('This item is no longer available.'); }
function slots(int $id): int { $o=one('SELECT slot_capacity FROM commission_offers WHERE listing_id=?',[$id]); return max(0,(int)$o['slot_capacity']-(int)query("SELECT COUNT(*) FROM commission_requests WHERE offer_listing_id=? AND status IN ('accepted','in_progress','delivered')",[$id])->fetchColumn()); }
function has_conflict(int $listing,string $start,string $end,int $exclude=0): bool {
    return (bool)one("SELECT id FROM rental_bookings WHERE listing_id=? AND id<>? AND ((status IN ('confirmed','collected') AND start_date<? AND end_date>?) OR (status='approved' AND hold_expires_at>? AND start_date<? AND end_date>?) OR (status='collected' AND end_date<=?)) LIMIT 1",[$listing,$exclude,$end,$start,now(),$end,$start,date('Y-m-d')]);
}
function image_for(array $l): string {
    $m=$l['artwork_id'] ? one('SELECT media_id FROM artwork_images WHERE artwork_id=? ORDER BY sort_order LIMIT 1',[$l['artwork_id']]) : one('SELECT media_id FROM listing_images WHERE listing_id=? ORDER BY sort_order LIMIT 1',[$l['id']]);
    return $m ? '/image.php?id='.$m['media_id'] : '/assets/images/art-01.jpg';
}
function upload_image(string $field,int $uid): ?int {
    if(empty($_FILES[$field])||$_FILES[$field]['error']===UPLOAD_ERR_NO_FILE) return null;
    $f=$_FILES[$field]; if($f['error']!==UPLOAD_ERR_OK||$f['size']>5*1024*1024) throw new DomainException('Use an image under 5 MB.');
    $info=@getimagesize($f['tmp_name']); $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!$info||!isset($types[$mime])||$info[0]*$info[1]>20000000) throw new DomainException('Use a valid JPEG, PNG, or WebP under 20 megapixels.');
    $name=bin2hex(random_bytes(20)).'.'.$types[$mime];
    if(!move_uploaded_file($f['tmp_name'],ROOT.'/storage/uploads/'.$name)) throw new RuntimeException('Image could not be stored.');
    return insert('media',['owner_id'=>$uid,'storage_path'=>'uploads/'.$name,'original_name'=>basename($f['name']),'mime_type'=>$mime,'byte_size'=>$f['size'],'width'=>$info[0],'height'=>$info[1],'alt_text'=>trim((string)($_POST['image_alt']??'Artwork uploaded by its creator')),'created_at'=>now()]);
}
function upload_images(int $uid,int $limit=4): array {
    if(!isset($_FILES['images'])) { $id=upload_image('image',$uid); return $id?[$id]:[]; }
    $files=$_FILES['images'];if(!is_array($files['name'])||count($files['name'])>$limit)throw new DomainException('Choose up to '.$limit.' images.');
    $ids=[];foreach($files['name'] as $i=>$name){$_FILES['_one']=[];foreach(['name','type','tmp_name','error','size'] as $key)$_FILES['_one'][$key]=$files[$key][$i];$id=upload_image('_one',$uid);if($id)$ids[]=$id;}unset($_FILES['_one']);return$ids;
}
const SECTIONS = ['likha_lokal'=>['Likha Lokal','Original art & handmade crafts','palette'], 'subasta'=>['Subasta','Online art auctions','gavel'], 'arkilikha'=>['Arkilikha','Equipment & device rental','camera'], 'gawang_hiling'=>['Gawang Hiling','Art made just for you','pen'], 'kagamit'=>['Kagamit','Art materials & tools','brush'], 'museo'=>['Museo','Meet our artists','gallery']];
require_once __DIR__.'/commerce.php';
