<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require ROOT.'/app/actions.php';
require ROOT.'/app/views.php';
require ROOT.'/app/pages.php';
require ROOT.'/app/workspace.php';
$routes=['home'=>['Discover','home'],'catalog'=>['Explore','catalog'],'detail'=>['Artwork & details','detail'],'artists'=>['Museo','artists'],'artist'=>['Artist portfolio','artist_page'],'artwork'=>['Portfolio artwork','artwork'],'feed'=>['Community feed','feed'],'saved'=>['Saved collection','saved'],'login'=>['Welcome back',fn()=>auth_page(false)],'register'=>['Join SIKHAYA',fn()=>auth_page(true)],'profile'=>['Your profile','profile'],'cart'=>['Shopping bag',fn()=>cart(false)],'checkout'=>['Fake checkout',fn()=>cart(true)],'orders'=>['Your orders','orders'],'work'=>['Rentals & commissions','work'],'studio'=>['My studio','studio_full'],'admin'=>['Admin workspace','admin_full'],'activity'=>['Notifications','activity'],'about'=>['Our story','about'],'help'=>['How it works','help']];
$page=(string)($_GET['p']??'home');
try {
    reconcile();actions();
    if(!isset($routes[$page])){http_response_code(404);$page='not-found';}
    if(in_array($page,['saved','profile','cart','checkout','orders','work','activity']))need_user();
    if($page==='studio')need_user('artist');if($page==='admin')need_user('admin');
    // Redirect before rendering headers when a portfolio work has a sale listing.
    if($page==='artwork'){$linked=one("SELECT id FROM listings WHERE artwork_id=? AND status IN ('published','sold_out') ORDER BY id DESC LIMIT 1",[(int)($_GET['id']??0)]);if($linked)go('detail',['id'=>$linked['id']]);}
    shell_start($page,$routes[$page][0]??'Page not found');
    if($page==='not-found')empty_state('That page wandered out of the studio.','Return to the collection and find something new.');else ($routes[$page][1])();
}catch(DomainException $e){if(!headers_sent()){http_response_code(403);shell_start('error','Not available');}empty_state('A little pause.', $e->getMessage());}
catch(Throwable $e){error_log((string)$e,3,ROOT.'/storage/logs/errors.log');if(!headers_sent()){http_response_code(500);shell_start('error','Something needs attention');}empty_state('We couldn’t open this page.','Please try again. If setup is incomplete, run start-local.cmd.');}
shell_end();
