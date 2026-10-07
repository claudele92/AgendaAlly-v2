import { store } from '../redux/store';

// symbol/position default to whatever the platform's current default
// currency actually is (e.g. XAF's "FCFA"), not a hardcoded '$' - a caller
// that doesn't pass these explicitly must still show a symbol that matches
// the raw number it's rendering, or it's the same label-vs-number
// mismatch bug already found and fixed once for Subscription/AdsPackage
// (see 82014a0's commit message).
export default function numberToPrice(number = 0, symbol, position) {
  const defaultCurrency = store.getState()?.currency?.defaultCurrency;
  const resolvedSymbol = symbol ?? defaultCurrency?.symbol ?? '$';
  const resolvedPosition = position ?? defaultCurrency?.position ?? 'after';

  const price = Number(number)
    .toFixed(2)
    .replace(/\d(?=(\d{3})+\.)/g, '$&,');

  return resolvedPosition === 'after'
    ? `${price} ${resolvedSymbol}`
    : `${resolvedSymbol} ${price}`;
}
