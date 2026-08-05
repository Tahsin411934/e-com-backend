import type { Metadata, Viewport } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import NavbarServer from "@/components/layout/NavbarServer";
import MobileBottomNav from "@/components/layout/MobileBottomNav";
import { settingsService } from "@/services/settings.service";
import Footer from "@/components/layout/Footer";
import StoreProvider from "@/lib/StoreProvider";
import ScrollToTop from "@/components/ui/ScrollToTop";
import ToastProvider from "@/components/ui/ToastProvider";
import CartDrawer from "@/components/cart/CartDrawer";
import FloatingCartButton from "@/components/cart/FloatingCartButton";
export const dynamic = "force-dynamic";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
  display: "swap",
  preload: true,
  fallback: ["system-ui", "sans-serif"],
});

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  themeColor: "var(--color-primary)",
};

export const metadata: Metadata = {
  metadataBase: new URL("https://shopio.com"),
  title: {
    default: "Shopio - Premium E-Commerce | Your Premium Online Shopping Destination",
    template: "%s | Shopio",
  },
  description:
    "Shopio is your premium online shopping destination. Discover top-quality products at unbeatable prices with fast, reliable delivery across Bangladesh.",
  keywords: [
    "ecommerce", "online shopping", "Shopio", "premium products",
    "Bangladesh online store", "buy online", "discount shopping",
    "electronics", "clothing", "home decor",
  ],
  authors: [{ name: "Shopio" }],
  creator: "Shopio",
  publisher: "Shopio",
  robots: { index: true, follow: true, googleBot: { index: true, follow: true, "max-video-preview": -1, "max-image-preview": "large", "max-snippet": -1 } },
  openGraph: {
    type: "website", locale: "en_US", siteName: "Shopio",
    title: "Shopio - Premium E-Commerce",
    description: "Your premium online shopping destination for top-quality products with unbeatable prices and fast delivery.",
    url: "https://shopio.com",
    images: [{ url: "/og-image.jpg", width: 1200, height: 630, alt: "Shopio - Premium E-Commerce" }],
  },
  twitter: { card: "summary_large_image", title: "Shopio - Premium E-Commerce", description: "Your premium online shopping destination...", images: ["/og-image.jpg"] },
  alternates: { canonical: "https://shopio.com" },
  icons: {
    icon: [{ url: "/favicon.ico", sizes: "any" }, { url: "/icon.png", sizes: "32x32", type: "image/png" }, { url: "/icon-192.png", sizes: "192x192", type: "image/png" }],
    apple: [{ url: "/apple-icon.png", sizes: "180x180", type: "image/png" }],
  },
  manifest: "/manifest.json",
  category: "ecommerce",
};

export default async function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  let primaryColor = "var(--color-primary)";
  try {
    const settingsRes = await settingsService.getAll();
    if (settingsRes.success && settingsRes.data?.primary_color) {
      primaryColor = settingsRes.data.primary_color;
    }
  } catch {}
  return (
    <html lang="en" className={`${inter.variable} h-full antialiased`}>
      <head>
        <link rel="preconnect" href={process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"} crossOrigin="anonymous" />
        <link rel="dns-prefetch" href={process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"} />
        <link rel="preconnect" href="https://fonts.googleapis.com" crossOrigin="anonymous" />
        <link rel="dns-prefetch" href="https://fonts.googleapis.com" />
      </head>
      <body suppressHydrationWarning className="min-h-full flex flex-col bg-white font-sans m-0 p-0"
        style={{ "--color-primary": primaryColor } as React.CSSProperties}>
        <StoreProvider>
          <a href="#main-content"
            className="sr-only focus:not-sr-only focus:absolute focus:z-[9999] focus:top-4 focus:left-4 focus:bg-white focus:text-gray-900 focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
            Skip to main content
          </a>
          <NavbarServer />
          <main id="main-content" className="flex-1 pb-16 md:pb-0">
            {children}
          </main>
          <Footer />
          <MobileBottomNav />
          <CartDrawer />
          <FloatingCartButton />
          <ToastProvider />
          <ScrollToTop />
        </StoreProvider>
      </body>
    </html>
  );
}