<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit('CLI only.');
require __DIR__.'/../app/bootstrap.php';
$dir=ROOT.'/storage/backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(2));
mkdir($dir.'/uploads',0775,true);
// VACUUM INTO creates a consistent database snapshot even while the site runs.
query('VACUUM INTO ?',[$dir.'/sikhaya.sqlite']);
foreach(scandir(ROOT.'/storage/uploads') as $name){$file=ROOT.'/storage/uploads/'.$name;if(is_file($file))copy($file,$dir.'/uploads/'.$name);}
file_put_contents($dir.'/RESTORE.txt',"Stop the PHP server. Keep the current database and uploads as a safety copy. Copy this sikhaya.sqlite to storage/database/sikhaya.sqlite and these uploads into storage/uploads. Keep the project public/assets folder. Start the server again. Never replace the database while PHP is running.\n");
echo "Backup saved: ".$dir.PHP_EOL;
