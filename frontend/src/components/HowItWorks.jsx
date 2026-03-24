const STEPS = [
  {
    step: '01',
    icon: '📍',
    title: 'Pin Your Location',
    desc: 'Search your address and drop a pin on your exact roof using our satellite map. We use this to calculate your solar yield.',
  },
  {
    step: '02',
    icon: '🧩',
    title: 'Answer 4 Quick Questions',
    desc: 'Tell us your property type, how you pay for electricity, whether you have a borehole, and your monthly bill.',
  },
  {
    step: '03',
    icon: '☀️',
    title: 'Get Your Solar Plan',
    desc: 'Instantly see personalised packages across three payment models — Rent, Rent-to-Own, or Pay Cash.',
  },
  {
    step: '04',
    icon: '🔧',
    title: 'Installation',
    desc: 'Our certified engineers visit your site, install your system, and commission it — at zero extra cost.',
  },
]

export default function HowItWorks() {
  return (
    <section id="how-it-works" className="py-20 px-4 bg-white">
      <div className="max-w-6xl mx-auto">
        <div className="text-center mb-14">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#fff7ed', color: '#f97316' }}>
            Simple Process
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Go Solar in 4 Easy Steps
          </h2>
          <p className="mt-3 text-gray-500 max-w-xl mx-auto text-sm">
            From location pin to powered home — the entire journey takes less than 5 minutes online.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {STEPS.map((s, i) => (
            <div key={s.step} className="relative flex flex-col items-center text-center group">
              {/* Connector line */}
              {i < STEPS.length - 1 && (
                <div className="hidden lg:block absolute top-8 left-[60%] w-full h-0.5" style={{ background: 'linear-gradient(to right, #f97316, #f59e0b)' }} />
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
