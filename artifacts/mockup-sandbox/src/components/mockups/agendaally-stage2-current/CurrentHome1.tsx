import "./_group.css";
import { NativeHeader, NativeSearchField, NativeServiceCategories } from "./current-baseline";

export function CurrentHome1() {
  return (
    <div className="agendaally-stage2-current">
      <div className="bg-ui-1-bg bg-no-repeat bg-clip-content bg-cover mb-20">
        <NativeHeader showLinks />
        <section className="flex justify-center items-center flex-col lg:h-[60vh] px-4 md:px-8 lg:px-0">
          <h1 className="md:text-6xl text-3xl font-semibold text-center my-10 max-h-max">
            Book Services. Shop Favorites. Simple.
          </h1>
          <NativeSearchField />
        </section>
      </div>
      <main>
        <div className="mt-10 xl:container">
          <NativeServiceCategories uiType={1} />
        </div>
      </main>
    </div>
  );
}