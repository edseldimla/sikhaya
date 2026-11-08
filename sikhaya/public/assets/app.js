// Small enhancements. Every form also works without JavaScript.
const menu = document.querySelector('.menu-toggle');
menu?.addEventListener('click', () => {
  const open = menu.getAttribute('aria-expanded') !== 'true';
  menu.setAttribute('aria-expanded', String(open));
  menu.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  document.querySelector('.sidebar').classList.toggle('is-open', open);
  document.body.classList.toggle('menu-open', open);
});
document.addEventListener('keydown', e => { if(e.key === 'Escape' && document.body.classList.contains('menu-open')) menu.click(); });
document.addEventListener('click', e => { if(document.body.classList.contains('menu-open') && !e.target.closest('.sidebar, .menu-toggle')) menu.click(); });
document.querySelectorAll('[data-dismiss]').forEach(b => b.addEventListener('click', () => b.closest('.flash').remove()));
document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => {
  const link = new URL(b.dataset.copy, location.origin).href;
  try { await navigator.clipboard.writeText(link); b.textContent = 'Link copied'; }
  catch { window.prompt('Copy this post link:', link); }
}));
const money = cents => new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP',maximumFractionDigits:2}).format(cents / 100);
const quote = document.querySelector('[data-daily]');
if(quote) {
  const form = quote.closest('form');
  const update = () => {
    const start = form.elements.start_date.value, end = form.elements.end_date.value;
    const days = Math.round((new Date(end + 'T00:00:00Z') - new Date(start + 'T00:00:00Z')) / 86400000);
    if(!start || !end || days < 1 || days > 30) { quote.textContent = 'Choose pickup and return dates, from 1 to 30 days.'; return; }
    const rate = JSON.parse(quote.dataset.rates).find(t => days >= t.min_days && (!t.max_days || days <= t.max_days));
    const total = Number(quote.dataset.daily) * days;
    quote.textContent = `${days} day${days===1?'':'s'} · Rental total ${money(total)} · Provider commission ${rate.commission_bps/100}% (${money(Math.round(total*rate.commission_bps/10000))}), deducted from provider proceeds.`;
  };
  form.addEventListener('input', update);
}
const select = document.querySelector('#new-section');
if(select) {
  const update = () => document.querySelectorAll('[data-section-fields]').forEach(el => el.hidden = el.dataset.sectionFields !== select.value);
  select.addEventListener('change', update); update();
}
document.querySelectorAll('[data-preview]').forEach(input => input.addEventListener('change', () => {
  const img = document.getElementById(input.dataset.preview);
  if(img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);
  const f=input.files[0]; img.hidden = !f;
  if(f) { img.dataset.objectUrl=URL.createObjectURL(f); img.src=img.dataset.objectUrl; }
}));
let serverOffset = 0;
function timers() {
  document.querySelectorAll('[data-end]').forEach(el => {
    const remaining = new Date(el.dataset.end).getTime() - (Date.now()+serverOffset);
    if(remaining <= 0) { el.textContent = 'Auction ended'; return; }
    const d=Math.floor(remaining/86400000), h=Math.floor(remaining/3600000)%24, m=Math.floor(remaining/60000)%60,s=Math.floor(remaining/1000)%60;
    el.textContent = `${d ? d+'d ' : ''}${h}h ${m}m ${s}s left`;
  });
}
timers();setInterval(timers,1000);
const auction=document.querySelector('[data-auction]');
if(auction) {
  const poll=async()=>{if(document.hidden)return;try{const r=await fetch('/api.php?auction='+auction.dataset.auction);if(!r.ok)return;const data=await r.json();serverOffset=new Date(data.server_time).getTime()-Date.now();document.querySelector('[data-current-bid]').textContent=money(data.highest_centavos);const amount=document.querySelector('input[name=amount]');if(amount)amount.min=data.minimum_centavos/100;if(data.status==='ended'){const bidForm=document.querySelector('form:has(input[value="bid"])');if(bidForm){bidForm.replaceChildren(Object.assign(document.createElement('p'),{textContent:'Auction ended. The winner can find checkout in My orders.',className:'notice'}));}}}catch{/* Next poll can retry. */}};
  poll();setInterval(poll,5000);
}
