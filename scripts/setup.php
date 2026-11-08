<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__.'/../app/bootstrap.php';
$path=database_path();
if(is_file($path)){
    $check=new PDO('sqlite:'.$path);
    if($check->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn()){
        if((int)$check->query('SELECT COUNT(*) FROM users')->fetchColumn()>0){echo "SIKHAYA is already set up. Existing data was kept.\n";exit;}
        // A schema-only setup may safely retry a rolled-back seed.
        $check=null; require ROOT.'/database/seed.php'; echo "Sample data added.\n";exit;
    }
}
$setup=new PDO('sqlite:'.$path);$setup->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$setup->exec(file_get_contents(ROOT.'/database/schema.sql'));$setup=null;
require ROOT.'/database/seed.php';
echo "SIKHAYA is ready.\nBuyer: buyer@sikhaya.example\nArtist: artist@sikhaya.example\nAdmin: admin@sikhaya.example\nPassword for all demo accounts: Sikhaya2026!\n";
