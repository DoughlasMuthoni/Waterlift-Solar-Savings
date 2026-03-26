const STEPS = [
  {
    step: '01',
    icon: '📍',
    title: 'Tell Us Where You Are',
    desc: 'Enter your address and drop a pin on the map. We use satellite data to assess your roof and solar potential — instantly.',
  },
  {
    step: '02',
    icon: '☀️',
    title: 'Get Your Personalised Solar Plan',
    desc: 'We calculate your savings, recommend the right system size, and show you flexible payment options — rent, rent-to-own, or pay cash.',
  },
  {
    step: '03',
    icon: '🔧',
    title: 'We Install. You Start Saving.',
    desc: 'Our certified engineers handle everything — survey, mounting, wiring, and commissioning — at zero extra cost. Savings begin from day one.',
  },
]

export default function HowItWorks() {
  return (
    <section id="how-it-works" className="py-20 px-4 bg-white">
      <div className="max-w-6xl mx-auto">
        <div className="text-center mb-14">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#fff7ed', color: '#f97316' }}>
            Your Path to Energy Independence
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Three Simple Steps.<br />Real Savings From Month One.
          </h2>
          <p className="mt-3 text-gray-500 max-w-xl mx-auto text-sm">
            From your first click to powered home — the entire journey takes less than five minutes online.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-8">
          {STEPS.map((s, i) => (
            <div key={s.step} className="relative flex flex-col items-center text-center group">
              {/* Connector line */}
              {i < STEPS.length - 1 && (
                <div className="hidden sm:block absolute top-8 left-[60%] w-full h-0.5" style={{ background: 'linear-gradient(to right, #f97316, #f59e0b)' }} />
              )}
              {/* Icon circle */}
              <div className="relative z-10 w-16 h-16 rounded-full flex items-center justify-center text-2xl shadow-lg mb-4 group-hover:scale-110 transition-transform"
                style={{ background: '#0f2d52' }}>
                {s.icon}
              </div>
              <span className="text-xs font-black tracking-widest mb-1" style={{ color: '#f97316' }}>STEP {s.step}</span>
              <h3 className="text-base font-bold mb-2" style={{ color: '#0f2d52' }}>{s.title}</h3>
              <p className="text-gray-500 text-sm leading-relaxed">{s.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
