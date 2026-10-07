const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const ts = require("typescript");

const bookingCardPath = path.resolve(
  __dirname,
  "../app/(store)/(booking)/components/booking-card/booking-card.tsx",
);
const appointmentListPath = path.resolve(
  __dirname,
  "../app/(store)/(booking)/(with-footer)/(simple)/appointments/components/list/list.tsx",
);
const appointmentPagePath = path.resolve(
  __dirname,
  "../app/(store)/(booking)/(with-footer)/(simple)/appointments/page.tsx",
);
const modalPath = path.resolve(__dirname, "../components/modal/modal.tsx");
const bookingCard = readFileSync(bookingCardPath, "utf8");
const appointmentList = readFileSync(appointmentListPath, "utf8");
const appointmentPage = readFileSync(appointmentPagePath, "utf8");
const modal = readFileSync(modalPath, "utf8");

test("appointment cards never nest native buttons and keep card selection keyboard-operable", () => {
  const source = ts.createSourceFile(
    bookingCardPath,
    bookingCard,
    ts.ScriptTarget.Latest,
    true,
    ts.ScriptKind.TSX,
  );
  let nativeButtons = 0;

  const visit = (node, hasNativeButtonAncestor = false) => {
    if (ts.isJsxElement(node)) {
      const isButton = node.openingElement.tagName.getText(source) === "button";
      if (isButton) {
        nativeButtons += 1;
        assert.equal(
          hasNativeButtonAncestor,
          false,
          "A native button must not contain another native button.",
        );
      }
      node.children.forEach((child) => visit(child, hasNativeButtonAncestor || isButton));
      return;
    }

    if (ts.isJsxSelfClosingElement(node)) {
      const isButton = node.tagName.getText(source) === "button";
      if (isButton) {
        nativeButtons += 1;
        assert.equal(hasNativeButtonAncestor, false);
      }
      return;
    }

    ts.forEachChild(node, (child) => visit(child, hasNativeButtonAncestor));
  };

  visit(source);
  assert.ok(nativeButtons >= 1);
  assert.match(bookingCard, /<button\s+type="button"\s+onClick=\{onClick\}/);
  assert.match(bookingCard, /<div\s+className=\{clsx\(/);
});

test("empty appointment responses do not select or open an undefined booking", () => {
  assert.match(appointmentList, /const firstBooking = res\.pages\[0\]\?\.data\?\.\[0\]/);
  assert.match(appointmentList, /if \(firstBooking\)\s*\{\s*onSelectBooking\(firstBooking\)/);
  assert.doesNotMatch(appointmentList, /onSelectBooking\(res\.pages\[0\]\.data\?\.\[0\]\)/);
});

test("closing appointment details restores focus to the booking card that opened it", () => {
  assert.match(bookingCard, /onClick: \(event: MouseEvent<HTMLButtonElement>\) => void/);
  assert.match(
    appointmentList,
    /onClick=\{\(event\) => onSelectBooking\(booking, event\.currentTarget\)\}/,
  );
  assert.match(appointmentPage, /detailTriggerRef\.current = trigger \|\| null/);
  assert.match(appointmentPage, /onAfterClose=\{\(\) => \{[\s\S]*?trigger\.focus\(\)/);
  assert.match(modal, /afterLeave=\{onAfterClose\}/);
});

test("closing a service detail returns focus to its actual card or extras trigger", () => {
  const directory = "../app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/services";
  const card = readFileSync(path.resolve(__dirname, directory, "service-card.tsx"), "utf8");
  const services = readFileSync(path.resolve(__dirname, directory, "services.tsx"), "utf8");

  for (const source of [card, services]) {
    const result = ts.transpileModule(source, { compilerOptions: { jsx: ts.JsxEmit.ReactJSX }, reportDiagnostics: true });
    assert.deepEqual(result.diagnostics?.filter((diagnostic) => diagnostic.category === ts.DiagnosticCategory.Error), []);
  }
  assert.match(card, /onClick=\{\(event\) => onCardClick\?\.\(event\.currentTarget\)\}/);
  assert.match(card, /onCardClick\?\.\(e\.currentTarget\)/);
  assert.match(card, /handleButtonClick\(e\.currentTarget\)/);
  assert.match(card, /onCardClick\(trigger\)/);
  assert.match(services, /detailTriggerRef\.current = trigger/);
  assert.match(services, /onAfterClose=\{\(\) => \{[\s\S]*?if \(trigger\?\.isConnected\) trigger\.focus\(\)/);
});