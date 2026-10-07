import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const source = readFileSync(new URL('./shop-add-data.jsx', import.meta.url), 'utf8');
const uncommentedSource = source.replace(/\{\/\*[\s\S]*?\*\/\}/g, '');
const columns = [...uncommentedSource.matchAll(/<Col\b([^>]*)>/g)].map(
  ([, attributes]) => attributes.trim(),
);

test('My Shop fields stack at mobile widths while preserving desktop column spans', () => {
  const responsiveColumns = columns.filter((attributes) =>
    /\bmd=\{\d+\}/.test(attributes),
  );

  assert.ok(responsiveColumns.length > 0);
  assert.ok(
    responsiveColumns.every((attributes) => /\bxs=\{24\}/.test(attributes)),
    'every non-full-width field column should span all 24 grid columns on mobile',
  );
  assert.ok(
    responsiveColumns.some((attributes) => /\bmd=\{12\}/.test(attributes)),
    'the desktop two-column form layout should remain in place',
  );
  assert.ok(
    responsiveColumns.some((attributes) => /\bmd=\{4\}/.test(attributes)) &&
      responsiveColumns.some((attributes) => /\bmd=\{10\}/.test(attributes)),
    'the desktop image/status row proportions should remain in place',
  );
  assert.ok(
    columns.every((attributes) => !/\bspan=\{(?:4|10|12)\}/.test(attributes)),
    'all non-full-width field columns should use explicit responsive breakpoints',
  );
});

test('My Shop description expands within a bounded row range', () => {
  assert.match(
    source,
    /name=\{`description\[\$\{item\.locale\}\]`\}[\s\S]*?<TextArea autoSize=\{\{ minRows: 4, maxRows: 8 \}\} \/>/,
  );
});