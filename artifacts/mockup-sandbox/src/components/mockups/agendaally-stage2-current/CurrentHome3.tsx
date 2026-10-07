import "./_group.css";
import { NativeHeader, NativeSearchField, NativeServiceCategories } from "./current-baseline";
import { NativeCanvas } from "./NativeCanvas";

export function CurrentHome3() {
  return (
    <div className="agendaally-stage2-current">
      <section className="lg:h-full relative pb-12 mb-10">
        <NativeCanvas />
        <NativeHeader showLinks />
        <div className="flex justify-center items-center flex-col px-4">
          <h1 className="md:text-[65px] text-white text-3xl font-semibold text-center my-10 max-w-[702px] leading-tight">
            Find and book the best beauty services near you
          </h1>
          <NativeSearchField variant="home3" />
        </div>
      </section>
      <main>
        <div className="xl:container">
          <NativeServiceCategories uiType={3} />
        </div>
      </main>
    </div>
  );
}