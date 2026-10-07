import { NextRequest, NextResponse } from "next/server";
import { cookies } from "next/headers";
import { BASE_URL } from "@/config/global";
import { isDevelopmentServer, resolveApiBaseUrl } from "@/lib/api-transport";
import { DefaultResponse, Language } from "@/types/global";
import { approvedHomeRedirectUrl } from "./config/approved-home-redirect";

// Server components across the app read the `lang` cookie and pass it
// straight to backend endpoints validated by FilterParamsRequest's
// 'lang' => 'string|exists:languages,locale' rule. A cookie can outlive the
// language it names (an admin deactivates/removes a locale after a visitor
// already picked it), so by the time any page reads it, the value itself
// can be invalid, not just absent. Checking it once here, before it reaches
// any page, is the one choke point that actually closes that gap.
const activeLocales = async (): Promise<string[]> => {
  const apiBaseUrl = resolveApiBaseUrl({
    developmentServer: isDevelopmentServer(
      process.env.NODE_ENV,
      process.env.NEXT_PUBLIC_APP_ENV,
      process.env.NEXT_PUBLIC_DEVELOPMENT_MODE,
    ),
    isBrowser: false,
    publicBaseUrl: BASE_URL,
    devApiTarget: process.env.AGENDAALLY_DEV_API_TARGET,
  });
  const res = await fetch(`${apiBaseUrl}v1/rest/languages/active`, {
    next: { revalidate: Number(process.env.NEXT_PUBLIC_CACHE_TIME) || 60 },
  });

  if (!res.ok) {
    throw new Error(`languages/active responded ${res.status}`);
  }

  const { data } = (await res.json()) as DefaultResponse<Language[]>;

  return data.map((language) => language.locale);
};

const hasStaleLangCookie = async (request: NextRequest): Promise<boolean> => {
  const lang = request.cookies.get("lang")?.value;

  if (!lang) {
    return false;
  }

  try {
    const locales = await activeLocales();
    return !locales.includes(lang);
  } catch {
    // Backend unreachable or errored — fail open rather than stripping a
    // cookie that may well still be valid, and rather than blocking every
    // page load on a flaky dependency call.
    return false;
  }
};

export const middleware = async (request: NextRequest) => {
  const { pathname } = request.nextUrl;
  const staleLangCookie = await hasStaleLangCookie(request);
  const withLangCookieCleared = (response: NextResponse) => {
    if (staleLangCookie) {
      response.cookies.delete("lang");
    }
    return response;
  };

  if (
    !(await cookies()).has("token") &&
    (pathname.includes("/profile") || pathname.includes("/orders"))
  ) {
    const loginUrl = request.nextUrl.clone();
    loginUrl.pathname = "/login";
    loginUrl.searchParams.set("redirect", `${pathname}${request.nextUrl.search}`);
    return withLangCookieCleared(NextResponse.redirect(loginUrl, 302));
  }

  // Stage 2 makes the approved marketplace the root route. Keep legacy
  // storefront URLs as bookmarks, but send them to the approved homepage.
  if (["/home-2", "/home-3", "/home-4"].includes(pathname)) {
    return withLangCookieCleared(
      NextResponse.redirect(approvedHomeRedirectUrl(request.nextUrl), 302),
    );
  }

  return withLangCookieCleared(NextResponse.next());
};

export const config = {
  matcher: ["/((?!api|_next/static|_next/image|assets|favicon.ico|sw.js).*)"],
};
