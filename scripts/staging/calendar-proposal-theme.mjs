import fs from 'node:fs';

// Proposal-only: read the existing theme; never change application tokens.
const source=fs.readFileSync('.migration-backup/admin/src/styles/agendaally-stage1-tokens.scss','utf8');
const token=name=>{
  const match=source.match(new RegExp(`--agendaally-stage1-${name}:\\s*([^;]+);`));
  if(!match)throw new Error(`Missing existing AgendaAlly theme token: ${name}`);
  return match[1].trim();
};
export const palette={
  ink:token('ink'),muted:token('muted'),brand:token('bronze-ink'),
  bg:token('canvas'),surface:token('surface'),line:token('border'),
  lav:token('bronze-wash'),green:token('bronze-wash'),orange:token('bronze-wash'),
  success:token('success'),error:token('error'),
};
export const radius={sm:parseInt(token('radius-sm')),md:parseInt(token('radius-md'))};
export const themeCss=`:root{--canvas:${palette.bg};--surface:${palette.surface};--ink:${palette.ink};--muted:${palette.muted};--border:${palette.line};--accent:${palette.brand};--wash:${palette.lav};--radius-sm:${radius.sm}px;--radius-md:${radius.md}px}`;