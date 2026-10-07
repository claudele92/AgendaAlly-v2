import Image from "next/image";
import "./home-pathways.css";

const pathways = [
  {
    eyebrow: "For customers",
    title: "Find and book your next appointment",
    description: "Discover services and specialists that fit your needs.",
    cta: "Explore services",
    href: "/services",
    image: "/stage2/stage2-tailoring.jpg",
    alt: "Illustrative tailoring consultation between an African tailor and an older customer.",
    imagePosition: "52% center",
    imageClass: "customer",
    icon: (
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
        <path d="M7 3v3m10-3v3M4.5 9h15M5.5 5h13A1.5 1.5 0 0 1 20 6.5v12a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-12A1.5 1.5 0 0 1 5.5 5Z" />
        <path d="m9 14 2 2 4-4" />
      </svg>
    ),
  },
  {
    eyebrow: "For businesses",
    title: "Grow your business with AgendaAlly",
    description: "Manage bookings, services, products, staff and your business operations.",
    cta: "Explore AgendaAlly for Business",
    href: "/for-business",
    image: "/stage2/agendaally-business-cta-tablet.jpg",
    alt: "Illustrative African woman entrepreneur reviewing a tablet in a contemporary commerce workspace.",
    imagePosition: "50% center",
    imageClass: "business",
    icon: (
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
        <path d="M4 20V8.5L12 4l8 4.5V20M2.5 20h19M8 20v-6h8v6M8 9h.01M12 9h.01M16 9h.01" />
      </svg>
    ),
  },
];

export function HomePathways() {
  return (
    <div className="aa-s2-home-pathways">
      {pathways.map((pathway) => (
        <article className="aa-s2-home-pathway" key={pathway.href}>
          <div className="aa-s2-home-pathway-copy">
            <div className="aa-s2-home-pathway-eyebrow">
              <span className="aa-s2-home-pathway-icon">{pathway.icon}</span>
              <span>{pathway.eyebrow}</span>
            </div>
            <h2>{pathway.title}</h2>
            <p>{pathway.description}</p>
            <a className="aa-s2-btn aa-s2-btn-primary aa-s2-home-pathway-link" href={pathway.href}>
              <span>{pathway.cta}</span>
              <svg viewBox="0 0 20 20" aria-hidden="true" fill="none">
                <path d="M3.5 10h12m-5-5 5 5-5 5" />
              </svg>
            </a>
          </div>
          <div className={`aa-s2-home-pathway-media aa-s2-home-pathway-media-${pathway.imageClass}`}>
            <Image
              src={pathway.image}
              alt={pathway.alt}
              fill
              sizes="(max-width: 767px) 100vw, (max-width: 1220px) 22vw, 260px"
              loading="lazy"
              style={{ objectPosition: pathway.imagePosition }}
            />
          </div>
        </article>
      ))}
    </div>
  );
}