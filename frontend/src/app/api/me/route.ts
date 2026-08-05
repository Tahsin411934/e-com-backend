import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { buildApiUrl } from "@/lib/api-url";

export async function GET() {
  try {
    const cookieStore = await cookies();
    const token = cookieStore.get("token")?.value;
    if (!token) return NextResponse.json({ success: false, message: "Not authenticated." }, { status: 401 });

    const res = await fetch(buildApiUrl("/api/v1/me"), {
      method: "GET",
      headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      cache: "no-store",
    });

    if (!res.ok) {
      const body = await res.text().catch(() => "{}");
      return NextResponse.json({ success: false, message: "Not authenticated.", details: body }, { status: res.status });
    }

    const data = await res.json();
    return NextResponse.json({ status: "success", user: data.user });
  } catch (err) {
    return NextResponse.json({ success: false, message: "Error fetching user." }, { status: 500 });
  }
}
