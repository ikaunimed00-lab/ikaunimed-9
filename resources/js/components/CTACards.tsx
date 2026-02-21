const CTACards = ({ content }: { content?: any }) => {
  const cards = content?.cards?.map((card: any, index: number) => ({
    ...card,
    imageName: card.imageName ? (card.imageName.startsWith('http') || card.imageName.startsWith('/') ? card.imageName : `/storage/${card.imageName}`) : `/images/favicon_ikaunimed.png`
  })) || [];

  if (cards.length === 0) return null;

  return (
    <section className="py-6 -mt-3 relative z-10">
      <div className="container">
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
          {cards.map((card: any, index: number) => (
            <a
              key={card.imageName || `cta-${index}`}
              href={card.href || "#"}
              className="block rounded-xl md:rounded-2xl overflow-hidden shadow-lg hover:shadow-xl hover:scale-[1.03] transition-all duration-300 group"
            >
              <img 
                src={card.imageName} 
                alt={card.altText || "CTA Card"} 
                className="w-full h-auto object-cover" 
              />
            </a>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CTACards;
