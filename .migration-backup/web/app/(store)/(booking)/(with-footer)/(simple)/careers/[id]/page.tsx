import { infoService } from "@/services/info";
import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { CareerDetailContent } from "./content";

const CareersDetailPage = async (props: { params: Promise<{ id: string }> }) => {
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  // Unlike the other static content pages, CareerDetailContent's own
  // client-side query is keyed off initialData.data.id and stays disabled
  // without it (there's no route param it can fall back to) - undefined
  // here means a permanently blank page, not a graceful degrade, so this
  // specific career is the page's subject: 404 rather than crash or blank.
  let data;
  try {
    data = await infoService.getCareer(params.id, { lang });
  } catch {
    notFound();
  }

  return <CareerDetailContent initialData={data} />;
};

export default CareersDetailPage;
