export const percentToPrice = (percent: number, price?: number, rounded = true) =>
  rounded
    ? Math.round((price ?? 0) * ((percent ?? 0) / 100))
    : (price ?? 0) * ((percent ?? 0) / 100);
