import HeroBannerServer from "@/components/home/HeroBannerServer";
import CategoryScrollServer from "@/components/home/CategoryScrollServer";
import HomePageServer from "@/components/home/HomePageServer";
import type { Metadata } from "next";

// Always fetch fresh data from the backend so homepage updates are reflected immediately.
export const dynamic = "force-dynamic";
export const revalidate = 0;

export const metadata: Metadata = {
  title: "Shopio - Premium E-Commerce | Your Premium Online Shopping Destination",
  description:
    "Shopio is your premium online shopping destination. Discover top-quality products at unbeatable prices with fast, reliable delivery across Bangladesh.",
  openGraph: {
    title: "Shopio - Premium E-Commerce",
    description:
      "Your premium online shopping destination for top-quality products with unbeatable prices and fast delivery.",
  },
};

export default function Home() {
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "WebSite",
    name: "Shopio",
    url: "https://shopio.com",
    description:
      "Your premium online shopping destination for top-quality products with unbeatable prices and fast delivery.",
    potentialAction: {
      "@type": "SearchAction",
      target: {
        "@type": "EntryPoint",
        urlTemplate: "https://shopio.com/search?q={search_term_string}",
      },
      "query-input": "required name=search_term_string",
    },
  };

  const organizationJsonLd = {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: "Shopio",
    url: "https://shopio.com",
    logo: "https://shopio.com/icon-192.png",
    contactPoint: {
      "@type": "ContactPoint",
      telephone: "(800) 123-4567",
      contactType: "customer service",
      availableLanguage: ["English"],
    },
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify(organizationJsonLd),
        }}
      />
      <div className="flex flex-col">
        <HeroBannerServer />
        <CategoryScrollServer />
        <HomePageServer />
      </div>
    </>
  );
}