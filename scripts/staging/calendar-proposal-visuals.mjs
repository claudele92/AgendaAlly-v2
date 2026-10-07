import fs from 'node:fs';
import path from 'node:path';
import {palette as c,radius,themeCss} from './calendar-proposal-theme.mjs';
const out=path.resolve('deliverables/vendor-calendar-proposal');
fs.mkdirSync(out,{recursive:true});
const esc=s=>String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('"','&quot;');
function drawing(w,h){
  const a=[`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}"><rect width="${w}" height="${h}" fill="${c.bg}"/>`];
  const rect=(x,y,w,h,fill=c.surface,r=radius.md,stroke='none')=>a.push(`<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="${fill}" stroke="${stroke}"/>`);
  const text=(x,y,s,size=14,color=c.ink,weight=400)=>a.push(`<text x="${x}" y="${y}" fill="${color}" font-size="${size}" font-family="DejaVu Sans,sans-serif" font-weight="${weight}">${esc(s)}</text>`);
  const line=(x,y,x2,y2,color=c.line)=>a.push(`<path d="M${x} ${y}H${x2}" stroke="${color}"/>`);
  const button=(x,y,w,s,active=false)=>{rect(x,y,w,34,active?c.brand:c.surface,radius.sm,active?'none':c.line);text(x+12,y+22,s,12,active?'white':c.ink,600);};
  return {a,rect,text,line,button,save(name){a.push('</svg>');fs.writeFileSync(`${out}/${name}.svg`,a.join('\n'));}};
}
{
  const d=drawing(1440,930),{rect:r,text:t,line:l,button:b}=d;
  t(30,32,'PROPOSAL ONLY',12,c.brand,700);t(185,32,'Illustrative display data · no application behavior implemented',12,c.muted);
  r(20,52,180,844,'white');r(36,73,34,34,c.brand,10);t(44,96,'AA',14,'white',700);t(80,96,'AgendaAlly',16,c.ink,700);
  t(37,151,'BUSINESS WORKSPACE',10,c.muted,700);
  t(40,193,'Overview',13,c.muted);r(32,211,156,41,c.lav,8);t(45,237,'Calendar',14,c.brand,700);
  t(40,283,'Bookings',13,c.muted);t(40,327,'Services',13,c.muted);t(40,371,'Specialists',13,c.muted);t(40,415,'Clients',13,c.muted);
  t(39,828,'Authorized Shop A',12,c.ink,700);t(39,851,'No global Shop switch',11,c.muted);
  r(218,52,1202,116);t(240,85,'Calendar',23,c.ink,700);t(240,112,'5–11 October 2026',13,c.muted);
  b(420,84,75,'‹    ›');b(507,84,70,'Today');
  b(864,78,57,'Day');b(927,78,67,'Week',true);b(1000,78,82,'Agenda');b(1246,78,149,'+ Add Booking',true);
  r(240,128,298,28,c.bg,6);t(252,147,'Search authorized appointments',11,c.muted);
  t(563,147,'Specialist: All assigned  ▾',11,c.ink);t(754,147,'Status: All  ▾',11,c.ink);t(905,147,'More filters',11,c.brand);
  r(218,182,886,714);r(1120,182,300,714);
  t(240,211,'WEEK',11,c.muted,700);t(819,211,'Working hours 08:00–18:00',11,c.muted);
  const x0=284,cw=115,y0=291,hh=53;
  ['Mon 5','Tue 6','Wed 7','Thu 8','Fri 9','Sat 10','Sun 11'].forEach((day,i)=>{
    if(i===0)r(x0+i*cw,229,cw-5,49,c.lav,8);
    t(x0+i*cw+14,258,day,13,i===0?c.brand:c.ink,600);
    if(i>0)d.a.push(`<path d="M${x0+i*cw-4} 283V850" stroke="${c.line}"/>`);
  });
  for(let hour=8;hour<=18;hour++){t(235,y0+(hour-8)*hh+5,`${String(hour).padStart(2,'0')}:00`,11,c.muted);l(x0,y0+(hour-8)*hh,1090,y0+(hour-8)*hh);}
  function card(day,hour,height,lines,fill=c.lav){
    const x=x0+day*cw,y=y0+(hour-8)*hh;
    r(x,y+3,cw-9,height,fill,8);r(x,y+3,3,height,c.brand,1);
    lines.forEach((s,i)=>t(x+9,y+20+i*16,s,i?10:11,c.ink,i===1?700:400));
  }
  card(0,9,98,['09:00–11:00','Haircut + colour','Sample client','Specialist: A','New · Unpaid']);
  card(1,10,70,['10:00–11:30','Consultation','Sample client','Specialist: B'],c.green);
  card(2,9.5,43,['09:30–10:30','Check-up'],c.green);
  card(0,13,47,['13:00–14:00','Blocked time'],c.line);
  card(3,12,70,['12:00–13:30','Service visit','Sample client','Specialist: A']);
  card(4,14,43,['14:00–15:00','Follow-up'],c.green);
  card(1,15.25,23,['15:15 · Review'],c.orange);
  const nowY=y0+2.35*hh;d.a.push(`<path d="M${x0} ${nowY}H${x0+cw-7}" stroke="${c.brand}" stroke-width="2"/><circle cx="${x0}" cy="${nowY}" r="4" fill="${c.brand}"/>`);
  t(237,875,'One-hour lines only · compact cards expand in saved details · blocked time is distinct',10,c.muted);
  t(1143,215,'BOOKING DETAILS',11,c.muted,700);t(1378,216,'×',22,c.muted);
  t(1143,255,'Haircut + colour',18,c.ink,700);t(1143,281,'Saved reference #1234',12,c.muted);
  r(1143,299,119,25,c.lav,6);t(1154,317,'New',12,c.brand,600);
  r(1143,342,251,55,c.orange,9);t(1156,365,'UNPAID / UNCOLLECTED',11,c.ink,700);t(1156,384,'Local client · no payment recorded',10,c.muted);
  [['Client','Sample client'],['Specialist','Sample specialist A'],['Shop','Authorized Shop A'],['Date','Monday, 5 October'],['Time','09:00–11:00 · 120 min'],['Saved total','$100.00'],['Notes','Existing authorized saved notes']].forEach((p,i)=>{
    t(1143,429+i*50,p[0],10,c.muted);t(1143,449+i*50,p[1],12,c.ink,i===5?700:400);
  });
  b(1143,807,119,'Reschedule');b(1274,807,120,'Cancel');
  t(1143,867,'Actions shown only when authorized',10,c.muted);
  t(28,922,'Approval boundary: layout, cards and workflows are a visual proposal. Native availability, duration, capacity and accounting remain authoritative.',11,c.muted);
  d.save('desktop-week');
}
function phone(width,mode){
  const d=drawing(width,mode==='agenda'?900:940),{rect:r,text:t,button:b}=d;
  const w=width-32;
  t(16,26,`${width}px · PROPOSAL`,11,c.brand,700);
  r(0,42,width,86,'white',0);t(16,73,'AgendaAlly',14,c.brand,700);t(16,109,mode==='agenda'?'Calendar':'Add Booking',24,c.ink,700);
  if(mode==='agenda'){
    b(16,145,44,'‹');b(67,145,width-150,'Mon, 5 Oct');b(width-76,145,60,'Today');
    b(16,195,88,'Agenda',true);b(110,195,57,'Day');b(width-143,195,56,'More');b(width-81,195,65,'Filters');
    t(16,238,'Agenda by default · optional Week in More',9,c.muted);
    r(16,249,w,37,'white',8,c.line);t(28,273,'Search appointments',12,c.muted);
    t(16,316,'TODAY · 4 APPOINTMENTS',11,c.muted,700);
    const rows=[
      ['09:00–10:00','Haircut','Sample client','Specialist A','New · Unpaid',c.lav],
      ['10:30–11:00','Consultation','Alexandria Sample-Longsurname','Specialist B','Booked',c.green],
      ['13:00–14:00','Blocked time','Not bookable','Specialist A','Unavailable',c.line],
      ['15:15–15:30','Quick review','Sample client','Specialist B','New',c.lav]];
    rows.forEach((v,i)=>{
      const y=337+i*116;r(16,y,w,102,'white',11,c.line);r(16,y,5,102,v[5],2);
      t(31,y+23,v[0],12,c.brand,700);t(31,y+46,v[1],15,c.ink,700);
      t(31,y+65,v[2],width===320?10:11,c.muted);t(31,y+84,v[3],10,c.muted);
      t(width-119,y+84,v[4],10,v[4]==='Booked'?c.success:c.ink,600);
    });
    r(0,817,width,83,'white',0);b(16,833,w,'+ Add Booking',true);t(16,883,'Safe-area footer; list has matching bottom space',9,c.muted);
  }else{
    t(16,158,'1 Client     2 Service & time     3 Review',width===320?10:12,c.muted);
    r(16,181,w,65,c.lav,10);t(29,205,'STEP 1 · CLIENT',10,c.brand,700);t(29,229,'Who is this booking for?',16,c.ink,700);
    b(16,267,(w-8)/2,'Existing client',true);b(20+w/2,267,(w-8)/2,'New local client');
    t(16,332,'Search your authorized client directory',11,c.muted);
    r(16,345,w,43,'white',8,c.line);t(29,372,'Name, phone or email',12,c.muted);
    r(16,405,w,91,'white',10,c.brand);t(29,432,'Sample local client',14,c.ink,700);t(29,453,'Shop A directory only',11,c.muted);t(29,477,'Selected · no platform account',11,c.brand);
    t(16,531,'Local client · no platform account created',10,c.muted);
    t(16,554,'Payment state will be shown on Review.',10,c.muted);
    t(16,677,'Next: Service → assigned Specialist',11,c.ink);t(16,702,'Date → backend availability → time',11,c.ink);t(16,727,'Review booking details before confirmation',10,c.muted);
    r(0,851,width,89,'white',0);b(16,867,w,'Continue to service & time',true);t(16,921,'Keyboard: sticky footer lifts above the input',9,c.muted);
  }
  d.save(`mobile-${width}-${mode}`);
}
phone(390,'agenda');phone(320,'agenda');phone(320,'add-booking');
{
  const d=drawing(1280,710),{rect:r,text:t,button:b}=d;
  t(24,30,'PROPOSAL · MOBILE DETAILS + NEW CLIENT + REVIEW',13,c.brand,700);
  const panels=[
    {x:24,title:'Saved detail · full-screen',rows:['Haircut','Saved reference #1234','Mon 5 Oct · 09:00–10:00','Sample client','Specialist A · Authorized Shop A','Saved total $100.00','UNPAID / UNCOLLECTED','Existing notes; no recalculation'],footer:'Authorized lifecycle actions'},
    {x:439,title:'New local client · retry',rows:['Name * · Sample local client','Phone / email optional','Verified Shop / branch context','Save not confirmed — input retained','Retry keeps the same save reference','Names never merge different people','No account or booking is created','New person = explicit new save'],footer:'Retry same client save'},
    {x:854,title:'Step 3 · Review',rows:['Service: Haircut','Specialist: assigned Specialist A','Date: 5 Oct · Time: available 09:00','Duration: backend 60 minutes','Client: local / Shop A','UNPAID / UNCOLLECTED','Backend rechecks slot on confirmation','Conflict → preserve form, choose again'],footer:'Confirm unpaid booking'},
  ];
  panels.forEach(p=>{
    r(p.x,57,389,605,'white',16,c.line);t(p.x+19,94,p.title,17,c.ink,700);t(p.x+19,122,'Close / Back · calendar position retained',11,c.muted);
    p.rows.forEach((s,i)=>{if(s==='UNPAID / UNCOLLECTED')r(p.x+14,152+i*48,361,38,c.orange,7);t(p.x+20,176+i*48,s,12,s==='UNPAID / UNCOLLECTED'?c.ink:c.muted,s==='UNPAID / UNCOLLECTED'?700:400);});
    b(p.x+18,598,351,p.footer,true);
  });
  t(24,694,'Proposal only: durable same-intent retry protection is specified, not implemented. Payment state is prominent on final Review, not early entry steps.',11,c.muted);
  d.save('detail-client-review');
}
for(const width of [390,320]){
  const d=drawing(width,900),{rect:r,text:t,button:b}=d,w=width-32;
  t(16,26,`${width}px · REVIEW PROPOSAL`,11,c.brand,700);
  r(0,42,width,86);t(16,73,'AgendaAlly',14,c.brand,700);t(16,109,'Review booking',24,c.ink,700);
  t(16,158,'1 Client     2 Service & time     3 Review',width===320?10:12,c.muted);
  r(16,183,w,65,c.lav,10);t(29,208,'STEP 3 · REVIEW',10,c.brand,700);t(29,231,'Check before confirming',17,c.ink,700);
  [['Client','Sample local client'],['Service / Specialist','Haircut / authorized Specialist A'],
    ['Date / time','5 Oct · 09:00–10:00'],['Duration / total','Native 60 minutes / $100.00']]
    .forEach(([label,value],i)=>{t(16,285+i*53,label,10,c.muted);t(16,306+i*53,value,width===320?11:13,c.ink,600);});
  r(16,512,w,117,c.lav,10,c.line);t(29,538,'PAYMENT / COLLECTION STATE',10,c.brand,700);
  t(29,567,'UNPAID / UNCOLLECTED',width===320?15:18,c.ink,700);
  t(29,591,'No payment is recorded for this local client.',10,c.muted);
  t(29,610,'Confirmation does not collect money.',10,c.muted);
  t(16,668,'Availability rechecked on confirmation',11,c.ink);
  t(16,693,'Conflict: retain details, choose another slot',10,c.muted);
  t(16,741,'Back to service & time',12,c.brand,600);
  r(0,817,width,83);b(16,833,w,'Confirm unpaid booking',true);
  t(16,883,'Proposal only · no booking or collection created',9,c.muted);
  d.save(`mobile-${width}-review`);
}
{
  const d=drawing(320,900),{rect:r,text:t,button:b}=d;
  t(16,26,'320px · OPTIONAL WEEK PROPOSAL',10,c.brand,700);
  r(0,42,320,86);t(16,73,'AgendaAlly',14,c.brand,700);t(16,109,'Calendar',24,c.ink,700);
  b(16,146,137,'Return to Agenda');b(167,146,137,'5–11 Oct');
  t(16,213,'Week is opt-in from More, not the default.',10,c.muted);
  r(16,247,288,450,c.surface,10,c.line);
  d.a.push('<defs><clipPath id="week-window"><rect x="24" y="257" width="272" height="425"/></clipPath></defs><g clip-path="url(#week-window)">');
  for(let i=0;i<7;i++){
    const x=24+i*100;t(x+9,280,['Mon 5','Tue 6','Wed 7','Thu 8','Fri 9','Sat 10','Sun 11'][i],12,c.ink,600);
    for(let j=0;j<7;j++)d.a.push(`<path d="M${x} ${312+j*48}h100" stroke="${c.line}"/>`);
    d.a.push(`<path d="M${x+98} 292v366" stroke="${c.line}"/>`);
  }
  r(29,343,87,72,c.lav,10);t(37,365,'09:00',11,c.brand,700);t(37,388,'Haircut',12,c.ink,600);
  r(128,438,87,72,c.lav,10);t(137,461,'11:00',11,c.brand,700);t(137,484,'Consultation',10,c.ink,600);
  d.a.push('</g>');
  r(31,705,258,5,c.line,2);r(31,705,90,5,c.brand,2);
  t(16,742,'Swipe only inside the bounded Week panel.',10,c.muted);
  t(16,764,'Page / body width stays fixed at 320px.',10,c.muted);
  r(0,817,320,83);b(16,833,288,'+ Add Booking',true);
  t(16,883,'Agenda remains the primary mobile experience',9,c.muted);
  d.save('mobile-320-week-contained');
}
const files=['desktop-week','mobile-390-agenda','mobile-320-agenda','mobile-320-add-booking','detail-client-review','mobile-390-review','mobile-320-review','mobile-320-week-contained'];
const images=files.map(f=>({name:f,svg:fs.readFileSync(`${out}/${f}.svg`,'utf8')}));
const gallery=images.map(i=>`<section><h2>${i.name.replaceAll('-',' ')}</h2><img alt="${i.name}" src="data:image/svg+xml;base64,${Buffer.from(i.svg).toString('base64')}"></section>`).join('');
fs.writeFileSync(`${out}/visual-proposal.html`,`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgendaAlly Calendar — Revised proposal</title><style>${themeCss}body{margin:0;padding:32px;background:var(--canvas);color:var(--ink);font:16px system-ui}main{max-width:1440px;margin:auto}h1{font-size:30px}p{max-width:900px;line-height:1.6}section{padding:24px 0;border-top:1px solid var(--border)}img{max-width:100%;height:auto;background:var(--surface);border-radius:var(--radius-md)}.badge{color:var(--accent);font-weight:700}</style><main><span class="badge">REVISED PROPOSAL — FINAL APPROVAL REQUIRED</span><h1>AgendaAlly appointment workspace</h1><p>Existing AgendaAlly canvas, surface, ink, bronze/accent, border and radius tokens are reused from agendaally-stage1-tokens.scss. No new mandatory purple palette or application theme change. These SVG wireframes use a renderer font fallback; implementation retains the app's Inter font token.</p><p>Mobile: Agenda first, Day secondary, Week opt-in inside a bounded panel. Early Add Booking is quiet; payment state is prominent on final Review. New Local Client retry protection is specified in the revised report, not implemented. Illustrative data only.</p>${gallery}<p>No visual redesign, client-creation persistence changes, provider or financial changes have been implemented. Final approval is required.</p></main></html>`);
console.log('Created eight revised approval-only wireframes using existing AgendaAlly theme tokens.');