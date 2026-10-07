export const approvedHomeRedirectUrl = (url: URL): URL => {
  const redirectUrl = new URL(url);
  redirectUrl.pathname = "/";
  return redirectUrl;
};