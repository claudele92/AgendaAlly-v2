import fs from 'node:fs';
import {themeCss} from './calendar-proposal-theme.mjs';
const out='deliverables/vendor-calendar-proposal';
const md=fs.readFileSync(`${out}/audit-and-design.md`,'utf8');
const escape=s=>s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
const inline=s=>escape(s).replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/`([^`]+)`/g,'<code>$1</code>');
let table=false;
const body=md.split('\n').map(line=>{
  if(line.startsWith('|')){
    if(/^\|[-| ]+\|$/.test(line))return '';
    const row=`<tr>${line.split('|').slice(1,-1).map(x=>`<td>${inline(x.trim())}</td>`).join('')}</tr>`;
    if(!table){table=true;return `<div class="table"><table>${row}`;}
    return row;
  }
  const close=table?'</table></div>':'';
  table=false;
  if(!line.trim())return close;
  const heading=line.match(/^(#{1,3}) (.*)$/);
  if(heading)return `${close}<h${heading[1].length}>${inline(heading[2])}</h${heading[1].length}>`;
  if(line.startsWith('- '))return `${close}<p class="bullet">• ${inline(line.slice(2))}</p>`;
  return `${close}<p>${inline(line)}</p>`;
}).join('\n')+(table?'</table></div>':'');
const visuals=[
  ['desktop-week','Desktop Week: workspace + saved detail'],
  ['mobile-390-agenda','390px Agenda'],
  ['mobile-320-agenda','320px Agenda'],
  ['mobile-320-add-booking','320px Add Booking'],
  ['detail-client-review','Saved detail, safe client retry and final Review'],
  ['mobile-390-review','390px final Review — payment state'],
  ['mobile-320-review','320px final Review — payment state'],
  ['mobile-320-week-contained','320px optional contained Week'],
].map(([file,title])=>{
  const svg=fs.readFileSync(`${out}/${file}.svg`);
  return `<section><h2>${title}</h2><img alt="${title}" src="data:image/svg+xml;base64,${svg.toString('base64')}"></section>`;
}).join('');
const css=`${themeCss}body{margin:0;background:var(--canvas);color:var(--ink);font:16px/1.65 system-ui}main{max-width:1160px;margin:auto;padding:32px}h1{font-size:30px;line-height:1.25}h2{margin-top:44px;padding-top:18px;border-top:1px solid var(--border);font-size:23px}p{margin:10px 0}.bullet{padding-left:18px}code{font-size:13px;overflow-wrap:anywhere;background:var(--wash);padding:2px 4px}.table{overflow-x:auto}table{border-collapse:collapse;font-size:14px;width:100%}td{border:1px solid var(--border);padding:10px;min-width:160px}tr:first-child{background:var(--wash);font-weight:600}img{max-width:100%;height:auto;border-radius:var(--radius-md)}.notice{background:var(--wash);padding:18px;border-radius:var(--radius-md);color:var(--accent);font-weight:600}.evidence{background:var(--surface);padding:20px;border-radius:var(--radius-md);overflow-wrap:anywhere}@media(max-width:500px){main{padding:18px}h1{font-size:25px}h2{font-size:20px}}`;
fs.writeFileSync(`${out}/audit-and-design.html`,`<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgendaAlly — Revised calendar proposal for final approval</title><style>${css}</style></head><body><main>
<p class="notice">Revised proposal for final approval. Existing theme tokens reused. Calendar UI and client-save retry protection are not implemented.</p>
${body}<h1>Revised wireframes — proposal only</h1>${visuals}
<h2>Retained verification evidence and limits</h2><div class="evidence">
<p>The prior native API, protected fingerprint/schema and isolated PHP/helper checks are retained. This revision changes proposal assets only, not the application or database.</p>
<p>Prior saved-detail browser: 1280, 390, 320; exact GET #8; no recalculation POST; no overflow. Prior read-only Add Booking browser: authorized existing client/Service/Specialist selection, New Client modal at all three widths, long name, date/time selection, back/cancel; zero POSTs.</p>
<p>Not claimed: implemented or verified new retry protection, production login/CAPTCHA acceptance, new final-creation/lifecycle mutation acceptance from the read-only UI run, harmful foreign mutation or physical-device virtual-keyboard certification.</p>
<p>Full repository hardening is not green. Readiness remains in staging. No providers, payouts/refunds, calendar redesign implementation or production deployment.</p>
</div></main></body></html>`);
fs.copyFileSync('.local/staging-mvp/vendor-calendar-after.json',`${out}/vendor-calendar-api-evidence.json`);
fs.copyFileSync('.local/staging-mvp/final-preservation.json',`${out}/protected-preservation-evidence.json`);
console.log('Packaged the revised 21-section report and eight theme-based wireframes.');