export const formatBlogDate = (value?: string | null, locale?: string): string | undefined => {
  if (!value) {
    return undefined;
  }

  const dateValue = /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T12:00:00Z` : value;
  const date = new Date(dateValue);

  if (Number.isNaN(date.getTime())) {
    return undefined;
  }

  const format = (resolvedLocale: string) =>
    new Intl.DateTimeFormat(resolvedLocale, {
      day: "numeric",
      month: "long",
      timeZone: "UTC",
      year: "numeric",
    }).format(date);

  try {
    return format(locale || "en");
  } catch {
    return format("en");
  }
};