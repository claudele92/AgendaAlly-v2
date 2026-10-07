const assert = require("node:assert/strict");
const fs = require("node:fs");
const vm = require("node:vm");
const ts = require("typescript");
const { test } = require("node:test");

// Execute the real component with a deterministic hook/query boundary.
// No provider, booking, transaction or browser data is created.
function harness() {
  let hooks = [], cursor = 0, effects = [], changes = [], walletChanges = [];
  let query = { data: { data: [] }, isLoading: false, isFetching: false, isError: false };
  const react = {
    createElement: (type, props, ...children) => ({ type, props: props || {}, children }),
    useRef(value) {
      const i = cursor++;
      return hooks[i] ||= { current: value };
    },
    useEffect(fn, deps) {
      const i = cursor++;
      if (!hooks[i] || deps.some((value, index) => value !== hooks[i][index])) effects.push(fn);
      hooks[i] = deps;
    },
  };
  const RadioGroup = Object.assign(function RadioGroup() {}, { Label() {}, Option() {} });
  const exports = {};
  const source = fs.readFileSync(`${__dirname}/payment-list.tsx`, "utf8");
  const code = ts.transpileModule(source, {
    compilerOptions: { module: ts.ModuleKind.CommonJS, jsx: ts.JsxEmit.React, esModuleInterop: true },
  }).outputText;
  vm.runInNewContext(code, {
    exports, React: react,
    require(id) {
      if (id === "react") return react;
      if (id === "@tanstack/react-query") return { useQuery: (key) => key[0] === "profile" ? {} : query };
      if (id === "@headlessui/react") return { RadioGroup };
      if (id === "react-i18next") return { useTranslation: () => ({ t: (key) => key }) };
      if (id === "@/hook/use-settings") return { useSettings: () => ({ currency: h.currency }) };
      if (id === "@/global-store/user") return { __esModule: true, default: (selector) => selector({ user: {}, signIn() {} }) };
      return {};
    },
  });
  const props = {
    onChange: (value) => changes.push(value),
    onChangeWalletPrice: (value) => walletChanges.push(value),
  };
  function find(node, type) {
    if (!node || typeof node !== "object") return;
    if (node.type === type) return node;
    for (const child of (node.children || []).flat(Infinity)) {
      const found = find(child, type);
      if (found) return found;
    }
  }
  const h = {
    currency: { id: 1 },
    props, changes, walletChanges,
    setQuery(next) { query = { isLoading: false, isFetching: false, isError: false, ...next }; },
    render(next = {}) {
      Object.assign(props, next);
      cursor = 0;
      effects = [];
      const tree = exports.PaymentList(props);
      effects.forEach((effect) => effect());
      return find(tree, RadioGroup);
    },
  };
  return h;
}

const cash = { id: 1, tag: "cash" };
const wallet = { id: 2, tag: "wallet" };

test("async first render and clearing remain controlled without a default payment", () => {
  const h = harness();
  h.setQuery({ isLoading: true });
  assert.equal(h.render(), undefined);
  h.setQuery({ data: { data: [cash, wallet] } });
  let radio = h.render();
  assert.equal(radio.props.value, null);
  assert.equal(radio.props.by, "id");
  assert.deepEqual(h.changes, []);
  radio.props.onChange(cash);
  assert.equal(h.changes.pop(), cash);
  radio = h.render({ value: cash });
  assert.equal(radio.props.value, cash);
  radio = h.render({ value: undefined });
  assert.equal(radio.props.value, null);
});

test("zero methods, errors, invalid context and filtered-out selections clear without fallback", () => {
  for (const query of [
    { data: { data: [] } },
    { isError: true },
    { data: { data: [cash], meta: { payment_context: { valid: false } } } },
    { data: { data: [cash] } },
  ]) {
    const h = harness();
    h.setQuery(query);
    h.render({ value: cash, filter: (payment) => payment.tag !== "cash", fromWalletPrice: 10 });
    assert.equal(h.changes.at(-1), undefined);
    assert.equal(h.walletChanges.at(-1), undefined);
  }
});

test("new shop or currency clears selection during loading; same-context refetch preserves it", () => {
  const h = harness();
  h.setQuery({ data: { data: [cash] } });
  h.render({ shopId: 501, value: cash });
  assert.equal(h.changes.length, 0);
  h.setQuery({ isFetching: true });
  h.render();
  assert.equal(h.changes.length, 0);
  h.render({ shopId: 502 });
  assert.deepEqual(h.changes, [undefined]);
  h.changes.length = 0;
  h.currency = { id: 2 };
  h.render();
  assert.deepEqual(h.changes, [undefined]);
});

test("platform-level shared consumer remains optional and never chooses cash", () => {
  const h = harness();
  h.setQuery({ data: { data: [cash, wallet] } });
  const radio = h.render({ filter: (payment) => payment.tag !== "cash" });
  assert.equal(radio.props.value, null);
  assert.equal(h.changes.length, 0);
  assert.equal(h.walletChanges.length, 0);
});