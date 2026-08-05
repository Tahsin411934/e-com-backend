// ============================================================
// Middleware - Route protection for authenticated/guest pages
// Uses the httpOnly token cookie to determine auth status.
// ============================================================

import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

const TOKEN_COOKIE_NAME = "token";

const protectedRoutes = [
  "/dashboard",
  "/profile",
  "/change-password",
  "/orders",
  "/checkout",
  "/wishlist",
];

const guestRoutes = ["/login", "/register", "/forgot-password", "/reset-password"];

function isRouteMatch(pathname: string, routes: string[]) {
  return routes.some((route) => pathname === route || pathname.startsWith(`${route}/`));
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const authToken = request.cookies.get(TOKEN_COOKIE_NAME)?.value;

  const isProtectedRoute = isRouteMatch(pathname, protectedRoutes);
  const isGuestRoute = isRouteMatch(pathname, guestRoutes);

  if (isProtectedRoute && !authToken) {
    const loginUrl = new URL("/login", request.url);
    loginUrl.searchParams.set("redirect", pathname);
    return NextResponse.redirect(loginUrl);
  }

  // Do not automatically redirect authenticated users away from guest pages here.
  // Client-side components (AuthGuard) will perform proper validation and redirects
  // so middleware should avoid making assumptions based solely on cookie presence.

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/((?!_next/static|_next/image|favicon.ico|public|api).*)",
  ],
};