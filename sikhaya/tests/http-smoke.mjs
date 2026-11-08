// Run against the isolated test server on port 8089, never the demo database.
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { DatabaseSync } from 'node:sqlite';
const base=process.env.SIKHAYA_TEST_URL || 'http://127.0.0.1:8089';
const testPath=process.env.SIKHAYA_TEST_DB || new URL('../storage/database/test.sqlite',import.meta.url);
const parsedUrl=new URL(base);
if(parsedUrl.hostname!=='127.0.0.1'||parsedUrl.port==='8088'||(typeof testPath==='string'?path.basename(testPath):path.basename(testPath.pathname))!=='test.sqlite')throw new Error('Use an isolated test.sqlite and a test loopback port, not the main server.');
const database=new DatabaseSync(testPath);
database.exec('PRAGMA foreign_keys=ON;');
let passed=0;
function check(label,condition){assert.ok(condition,label);passed++;console.log('PASS '+label);}
class Client {
  cookie='';csrf='';
  update(r){for(const c of r.headers.getSetCookie())if(c.startsWith('sikhaya_session='))this.cookie=c.split(';')[0];}
  async get(path){const r=await fetch(base+path,{headers:{cookie:this.cookie},redirect:'manual'});this.update(r);const text=await r.text();this.csrf=text.match(/name="csrf" value="([^"]+)"/)?.[1]||this.csrf;return{status:r.status,text,location:r.headers.get('location')};}
  async post(fields,{multipart=false,follow=true}={}){
    if(!this.csrf)await this.get('/?p=login');
    let body;if(multipart){body=new FormData();body.set('csrf',this.csrf);for(const[k,v]of Object.entries(fields))body.set(k,v);}else body=new URLSearchParams({csrf:this.csrf,...fields});
    const r=await fetch(base+'/',{method:'POST',headers:{cookie:this.cookie,...(multipart?{}:{'content-type':'application/x-www-form-urlencoded'})},body,redirect:'manual'});this.update(r);const loc=r.headers.get('location');if(follow&&loc)return this.get(loc);return{status:r.status,text:await r.text(),location:loc};
  }
  async login(role){await this.get('/?p=login');const r=await this.post({action:'login',email:role+'@sikhaya.example',password:'Sikhaya2026!'});check(role+' login',r.text.includes('Welcome back,'));}
}
const buyer=new Client(),artist=new Client(),admin=new Client(),guest=new Client();
await buyer.login('buyer');await artist.login('artist');await admin.login('admin');
for(const page of ['home','catalog','artists','feed','about','help']){const r=await guest.get('/?p='+page);check('public '+page,r.status===200&&!r.text.includes('We couldn’t open this page.'));}
for(const page of ['profile','saved','orders','work','cart','activity']){const r=await buyer.get('/?p='+page);check('buyer '+page,r.status===200&&!r.text.includes('We couldn’t open this page.'));}
check('buyer cannot open admin',(await buyer.get('/?p=admin')).status===403);
check('guest cart requires login',(await guest.get('/?p=cart')).status===303);
check('artist studio opens',(await artist.get('/?p=studio')).status===200);
check('admin workspace opens',(await admin.get('/?p=admin')).status===200);
let r=await buyer.post({action:'cart_add',id:31,quantity:1,csrf:'invalid',return:'/?p=cart'});
check('CSRF rejected',r.text.includes('Your form expired'));
const supply=database.prepare("SELECT * FROM listings WHERE section='kagamit' AND seller_id<>3 AND stock_qty>2 LIMIT 1").get();
database.prepare('DELETE FROM cart_items WHERE buyer_id=3').run();
await buyer.post({action:'cart_add',id:supply.id,quantity:1,return:'/?p=cart'});
check('cart persisted',database.prepare('SELECT quantity FROM cart_items WHERE buyer_id=3 AND listing_id=?').get(supply.id).quantity===1);
const before=supply.stock_qty,token='test-checkout-'+Date.now();
r=await buyer.post({action:'checkout',request_token:token,['prices['+supply.id+']']:supply.unit_price_centavos,return:'/?p=checkout'});
check('fake checkout saved',r.text.includes('Demo checkout complete'));
check('stock reduced once',database.prepare('SELECT stock_qty FROM listings WHERE id=?').get(supply.id).stock_qty===before-1);
await buyer.post({action:'checkout',request_token:token,['prices['+supply.id+']']:supply.unit_price_centavos,return:'/?p=checkout'});
check('repeat checkout idempotent',database.prepare('SELECT COUNT(*) AS n FROM orders WHERE checkout_token=?').get(token).n===1&&database.prepare('SELECT stock_qty FROM listings WHERE id=?').get(supply.id).stock_qty===before-1);
await buyer.post({action:'cart_add',id:supply.id,quantity:1,return:'/?p=cart'});
r=await buyer.post({action:'checkout',request_token:'changed-'+Date.now(),['prices['+supply.id+']']:1,return:'/?p=checkout'});
check('changed price requires review',r.text.includes('A price changed'));
const a=database.prepare("SELECT a.*,l.seller_id FROM auctions a JOIN listings l ON l.id=a.listing_id WHERE a.status='live' AND l.seller_id<>3 LIMIT 1").get();
const high=database.prepare('SELECT MAX(amount_centavos) AS n FROM bids WHERE auction_id=?').get(a.id).n;
r=await buyer.post({action:'bid',id:a.id,amount:(high+a.min_increment_centavos)/100,request_token:'test-bid-'+Date.now(),return:'/?p=detail&id='+a.listing_id});
check('valid bid accepted',!r.text.includes('flash error')&&database.prepare('SELECT MAX(amount_centavos) AS n FROM bids WHERE auction_id=?').get(a.id).n===high+a.min_increment_centavos);
r=await buyer.post({action:'bid',id:a.id,amount:1,request_token:'low-'+Date.now(),return:'/?p=detail&id='+a.listing_id});check('low bid rejected',r.text.includes('next bid must be at least'));
database.prepare("UPDATE auctions SET ends_at=?,starts_at=? WHERE id=?").run(new Date(Date.now()-1000).toISOString().replace('.000Z','Z'),new Date(Date.now()-86400000).toISOString().replace('.000Z','Z'),a.id);
await guest.get('/');await guest.get('/');
check('winner finalizes once',database.prepare('SELECT COUNT(*) AS n FROM order_items WHERE auction_id=?').get(a.id).n===1);
r=await buyer.post({action:'bid',id:a.id,amount:99999,request_token:'late-'+Date.now(),return:'/?p=detail&id='+a.listing_id});check('late bid rejected',r.text.includes('not open for bidding'));
const rental=database.prepare("SELECT * FROM listings WHERE section='arkilikha' AND seller_id=2 LIMIT 1").get();
const start=new Date(Date.now()+10*86400000).toISOString().slice(0,10),end=new Date(Date.now()+15*86400000).toISOString().slice(0,10);
await buyer.post({action:'rental_request',id:rental.id,start_date:start,end_date:end,return:'/?p=work'});
const booking=database.prepare('SELECT * FROM rental_bookings WHERE listing_id=? AND renter_id=3 ORDER BY id DESC LIMIT 1').get(rental.id);
check('rental daily quote',booking.days===5&&booking.rental_total_centavos===5*rental.unit_price_centavos&&booking.commission_bps===800&&booking.platform_fee_centavos===Math.round(booking.rental_total_centavos*.08));
await artist.post({action:'rental_step',id:booking.id,step:'approve',return:'/?p=work'});
check('artist rental approval',database.prepare('SELECT status FROM rental_bookings WHERE id=?').get(booking.id).status==='approved');
r=await buyer.post({action:'rental_request',id:rental.id,start_date:start,end_date:end,return:'/?p=work'});check('overlap rejected',r.text.includes('already reserved'));
await buyer.post({action:'special_pay',id:booking.id,type:'rental',request_token:'rental-pay-'+Date.now(),return:'/?p=work'});
check('rental fake payment confirmed',database.prepare('SELECT status FROM rental_bookings WHERE id=?').get(booking.id).status==='confirmed');
for(const step of ['collect','return'])await artist.post({action:'rental_step',id:booking.id,step,return:'/?p=work'});
check('rental returned',database.prepare('SELECT status FROM rental_bookings WHERE id=?').get(booking.id).status==='returned');
const commission=database.prepare("SELECT * FROM commission_requests WHERE buyer_id=3 AND status='quoted' LIMIT 1").get();
await buyer.post({action:'commission_step',id:commission.id,step:'accept',return:'/?p=work'});
check('quote accepted',database.prepare('SELECT status FROM commission_requests WHERE id=?').get(commission.id).status==='accepted');
await buyer.post({action:'special_pay',id:commission.id,type:'commission',request_token:'art-pay-'+Date.now(),return:'/?p=work'});
check('custom art paid and started',database.prepare('SELECT status FROM commission_requests WHERE id=?').get(commission.id).status==='in_progress');
const logo=fs.readFileSync(new URL('../public/assets/images/sikhaya-logo.png',import.meta.url));
r=await buyer.post({action:'message',id:commission.id,body:'Private test reference',image:new Blob([logo],{type:'image/png'}),return:'/?p=work'},{multipart:true});
const privateMedia=database.prepare('SELECT attachment_media_id FROM commission_messages WHERE request_id=? ORDER BY id DESC LIMIT 1').get(commission.id)?.attachment_media_id;
check('project image uploaded',Boolean(privateMedia));
check('guest cannot read private image',(await guest.get('/image.php?id='+privateMedia)).status===403);
check('participant can read private image',(await artist.get('/image.php?id='+privateMedia)).status===200);
await artist.post({action:'commission_step',id:commission.id,step:'deliver',return:'/?p=work'});
await buyer.post({action:'commission_step',id:commission.id,step:'revision',body:'Please adjust the light.',return:'/?p=work'});
check('revision counted once',database.prepare('SELECT revisions_used FROM commission_requests WHERE id=?').get(commission.id).revisions_used===1);
await artist.post({action:'commission_step',id:commission.id,step:'deliver',return:'/?p=work'});
await buyer.post({action:'commission_step',id:commission.id,step:'complete',return:'/?p=work'});
check('commission completed',database.prepare('SELECT status FROM commission_requests WHERE id=?').get(commission.id).status==='completed');
r=await buyer.post({action:'post_create',body:'HTTP test artwork post',image:new Blob([logo],{type:'image/png'}),return:'/?p=feed'},{multipart:true});
const post=database.prepare("SELECT id FROM posts WHERE body='HTTP test artwork post' ORDER BY id DESC LIMIT 1").get();check('artwork post saved',Boolean(post));
await buyer.post({action:'react',id:post.id,reaction:'love',return:'/?p=feed&post='+post.id});
await buyer.post({action:'react',id:post.id,reaction:'wow',return:'/?p=feed&post='+post.id});
check('one reaction per user',database.prepare('SELECT COUNT(*) AS n FROM post_reactions WHERE post_id=? AND user_id=3').get(post.id).n===1);
await buyer.post({action:'comment',id:post.id,body:'Saved comment test',return:'/?p=feed&post='+post.id});check('comment persisted',Boolean(database.prepare("SELECT id FROM comments WHERE body='Saved comment test'").get()));
await artist.post({action:'share',id:post.id,return:'/?p=feed'});await artist.post({action:'share',id:post.id,return:'/?p=feed'});check('share not duplicated',database.prepare('SELECT COUNT(*) AS n FROM posts WHERE shared_post_id=? AND author_id=2').get(post.id).n===1);
r=await buyer.post({action:'admin_moderate',id:post.id,type:'post',step:'hide',reason:'Unauthorized action test',return:'/?p=feed'});check('buyer cannot moderate',r.text.includes('for admin accounts'));
await admin.post({action:'admin_moderate',id:post.id,type:'post',step:'hide',reason:'Test moderation',return:'/?p=admin'});
check('admin moderation recorded',database.prepare("SELECT COUNT(*) AS n FROM moderation_actions WHERE post_id=? AND action='hide'").get(post.id).n===1);
// Capacity, original-stock, and cancellation checks exercise boundary rules.
const ownAuction=database.prepare("SELECT a.*,l.seller_id FROM auctions a JOIN listings l ON l.id=a.listing_id WHERE l.seller_id=2 AND a.status='live' LIMIT 1").get();
if(ownAuction){r=await artist.post({action:'bid',id:ownAuction.id,amount:99999,request_token:'self-'+Date.now(),return:'/?p=detail&id='+ownAuction.listing_id});check('artist cannot bid on own art',r.text.includes('cannot bid on your own'));}
const original=database.prepare("SELECT * FROM listings WHERE artwork_id IS NOT NULL AND section IN ('likha_lokal','museo') AND stock_qty=1 AND status='published' AND seller_id<>3 LIMIT 1").get();
database.prepare('DELETE FROM cart_items WHERE buyer_id=3').run();
await buyer.post({action:'cart_add',id:original.id,quantity:1,return:'/?p=cart'});
await buyer.post({action:'checkout',request_token:'original-'+Date.now(),['prices['+original.id+']']:original.unit_price_centavos,return:'/?p=checkout'});
check('original marked sold',database.prepare('SELECT sold_at FROM artworks WHERE id=?').get(original.artwork_id).sold_at!==null);
const secondBuyer=new Client();await secondBuyer.get('/?p=login');await secondBuyer.post({action:'login',email:'bea@sikhaya.example',password:'Sikhaya2026!'});
r=await secondBuyer.post({action:'cart_add',id:original.id,quantity:1,return:'/?p=detail&id='+original.id});check('sold original cannot sell twice',r.text.includes('no longer available'));
const offer=commission.offer_listing_id;
database.prepare('UPDATE commission_offers SET slot_capacity=1 WHERE listing_id=?').run(offer);
const iso=new Date().toISOString().replace(/\.\d{3}Z$/,'Z'),expires=new Date(Date.now()+3600000).toISOString().replace(/\.\d{3}Z$/,'Z');
const addQuote=database.prepare("INSERT INTO commission_requests(offer_listing_id,buyer_id,brief,quoted_price_centavos,quoted_due_date,agreed_revision_limit,quote_expires_at,status,created_at,updated_at) VALUES(?,?,?,250000,'2027-01-01',2,?,'quoted',?,?)");
const q1=Number(addQuote.run(offer,3,'Last-slot test A',expires,iso,iso).lastInsertRowid),q2=Number(addQuote.run(offer,11,'Last-slot test B',expires,iso,iso).lastInsertRowid);
await buyer.post({action:'commission_step',id:q1,step:'accept',return:'/?p=work'});
r=await secondBuyer.post({action:'commission_step',id:q2,step:'accept',return:'/?p=work'});check('last commission slot protected',r.text.includes('last slot was just taken')&&database.prepare('SELECT status FROM commission_requests WHERE id=?').get(q2).status==='quoted');
r=await buyer.post({action:'post_create',body:'Invalid upload test',image:new Blob(['not an actual image'],{type:'image/png'}),return:'/?p=feed'},{multipart:true});check('fake image upload rejected',r.text.includes('Use a valid JPEG'));
const cancellable=database.prepare("SELECT i.* FROM order_items i JOIN orders o ON o.id=i.order_id WHERE o.checkout_token=?").get(token);
const stockBeforeCancel=database.prepare('SELECT stock_qty FROM listings WHERE id=?').get(cancellable.listing_id).stock_qty;
await admin.post({action:'admin_cancel',id:cancellable.id,reason:'Test cancellation',return:'/?p=admin'});
await admin.post({action:'admin_cancel',id:cancellable.id,reason:'Repeated test cancellation',return:'/?p=admin'});
check('cancel restores stock once',database.prepare('SELECT stock_qty FROM listings WHERE id=?').get(cancellable.listing_id).stock_qty===stockBeforeCancel+1);
check('cancel keeps first reason and actor',database.prepare('SELECT cancellation_reason,cancelled_by FROM order_items WHERE id=?').get(cancellable.id).cancellation_reason==='Test cancellation');
const signup=new Client();const email='qa-'+Date.now()+'@sikhaya.example';await signup.get('/?p=register');await signup.post({action:'register',display_name:'Test Local Account',email,password:'TestLocal2026!',confirm_password:'TestLocal2026!',role:'admin'});
check('registration cannot grant admin',database.prepare('SELECT role FROM users WHERE email=?').get(email)?.role==='buyer');
check('database foreign keys valid',database.prepare('PRAGMA foreign_key_check').all().length===0);
check('order totals balanced',database.prepare('SELECT o.id FROM orders o JOIN order_items i ON i.order_id=o.id GROUP BY o.id HAVING o.total_centavos<>SUM(i.line_total_centavos)').all().length===0);
console.log(`\n${passed} checks passed. Demo database was not modified.`);
database.close();
