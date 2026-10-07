import { store } from '../redux/store';

// Every existing caller actually passes a currency symbol as the second
// argument (e.g. nFormatter(statistic.total_price, defaultCurrency.symbol)),
// not a digit count - this function had no parameter for that at all, so it
// silently coerced the symbol string into .toFixed()'s digits argument
// (NaN -> treated as 0) and always rendered the hardcoded '$' regardless.
// Fixed to actually accept and use the symbol callers were already passing.
export function nFormatter(num, symbol, digits) {
  const defaultCurrency = store.getState()?.currency?.defaultCurrency;
  const resolvedSymbol = symbol ?? defaultCurrency?.symbol ?? '$';

  const lookup = [
    { value: 1, symbol: '' },
    { value: 1e3, symbol: 'k' },
    { value: 1e6, symbol: 'M' },
    { value: 1e9, symbol: 'G' },
    { value: 1e12, symbol: 'T' },
    { value: 1e15, symbol: 'P' },
    { value: 1e18, symbol: 'E' },
  ];
  const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
  var item = lookup
    .slice()
    .reverse()
    .find(function (item) {
      return num >= item.value;
    });
  return item
    ? resolvedSymbol + (num / item.value).toFixed(digits).replace(rx, '$1') + item.symbol
    : '0';
}
