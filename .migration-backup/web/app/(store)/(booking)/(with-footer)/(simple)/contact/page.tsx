import { Translate } from "@/components/translate";
import { globalService } from "@/services/global";

const ContactsPage = async () => {
  const settings = await globalService.settings().catch(() => undefined);

  return (
    <main className="aa-s2">
      <section className="mx-auto max-w-7xl px-4 py-7 md:py-10">
        <div className="mb-7 border-b border-[#e8e3d9] pb-6 dark:border-gray-bold">
        <p className="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
          Help
        </p>
        <h1 className="text-3xl font-bold text-[#26241f] dark:text-white md:text-4xl">
          <Translate value="contact" />
        </h1>
      </div>
        <div className="mx-auto max-w-3xl rounded-2xl border border-[#e8e3d9] bg-[#f2eee6] p-6 dark:border-gray-bold dark:bg-darkBgUi3 md:p-8">
        <h2 className="text-xl font-semibold text-[#26241f] dark:text-white">
          Contact information unavailable
        </h2>
        {settings ? (
          <p
            className="mt-3 text-base leading-relaxed text-[#46433d] dark:text-gray-200"
            role="status"
            data-testid="status-contact-not-configured"
          >
            No official support channel can be verified from the available public settings. Phone,
            address, social, and other fields are not shown unless their official identity is
            confirmed.
          </p>
        ) : (
          <>
            <p
              className="mt-3 text-base leading-relaxed text-[#46433d] dark:text-gray-200"
              role="alert"
              data-testid="status-contact-settings-error"
            >
              Contact information could not be loaded. Please try again later.
            </p>
          </>
        )}
        <p className="mt-4 text-sm leading-relaxed text-[#625d54] dark:text-gray-300">
          This page has no message form or submission action.
        </p>
        </div>
      </section>
    </main>
  );
};

export default ContactsPage;
