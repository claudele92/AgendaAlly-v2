import "./_group.css";
import { NativeHeader, NativeSearchField } from "./current-baseline";

export function CurrentHome4() {
  return (
    <div className="agendaally-stage2-current">
      <div className="relative bg-ui-2-bg bg-no-repeat bg-clip-content bg-center bg-contain bg-gray-ui4bg lg:h-[605px] h-[605px] aspect-[11/5]">
        <NativeHeader />
        <section className="w-full z-[2] absolute left-1/2 -translate-x-1/2 bottom-[-30px] flex justify-center items-center flex-col px-4 md:px-8 lg:px-0">
          <h1 className="text-center text-shadow mb-5 break-words max-w-[90vw] md:max-w-[800px] leading-tight">
            Book beauty services with ease
          </h1>
          <div className="w-full lg:w-auto drop-shadow-search">
            <NativeSearchField />
          </div>
        </section>
      </div>
    </div>
  );
}