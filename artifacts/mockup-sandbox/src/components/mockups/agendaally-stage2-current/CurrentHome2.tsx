import "./_group.css";
import {
  Home2HeroPeople,
  NativeHeader,
  NativeSearchField,
  NativeServiceCategories,
} from "./current-baseline";

export function CurrentHome2() {
  return (
    <div className="agendaally-stage2-current">
      <NativeHeader />
      <main>
        <section className="flex flex-col items-center justify-center xl:container mb-24">
          <div className="grid md:grid-cols-2 lg:gap-48 mb-10">
            <div className="px-4 xl:px-0">
              <h1 className="lg:text-[65px] md:text-5xl text-3xl break-words leading-normal text-center md:text-start">
                <span className="font-bold text-giantsOrange">Book service</span>{" "}
                in the best salon near you
              </h1>
              <p className="text-xl my-5 hidden md:block">home-2.hero.description</p>
              <Home2HeroPeople />
            </div>
            <div className="relative h-[300px] md:h-full px-4 xl:px-0 mt-10 md:mt-0 mb-32 md:mb-0">
              <div className="hero-1 relative h-full w-1/2" />
              <div className="hero-2 absolute w-44 aspect-square top-0 rounded-full z-[2]" />
              <div className="hero-3 absolute aspect-[1/1.5] w-36 top-48 z-[2]" />
              <div className="hero-4 absolute aspect-square w-28 top-16 z-[2] rounded-full" />
            </div>
          </div>
          <NativeSearchField variant="home2" />
        </section>
        <section className="xl:container px-4">
          <NativeServiceCategories uiType={2} />
        </section>
      </main>
    </div>
  );
}