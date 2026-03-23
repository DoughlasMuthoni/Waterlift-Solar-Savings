const BENEFITS = [
  { icon: '🔧', title: 'Free Installation',     desc: 'Our certified engineers handle everything — site survey, mounting, wiring, and commissioning.' },
  { icon: '🛡️', title: 'Lifetime Maintenance',  desc: 'All Rent and Rent-to-Own customers receive ongoing maintenance at no extra cost, for life.' },
  { icon: '📡', title: '24/7 Remote Monitoring', desc: 'Our systems transmit live performance data so we can detect and fix issues before you notice them.' },
  { icon: '💧', title: 'Borehole Specialists',   desc: 'We are one of the few providers that properly sizes solar for borehole pump surge loads.' },
  { icon: '🗺️', title: 'Truly National',         desc: 'Serving all 47 counties — from Mombasa to Mandera. No area is too remote for our team.' },
  { icon: '📋', title: 'Flexible Payment',        desc: 'Rent from as low as KES 3,500/mo, Rent-to-Own for 72 months, or save long-term with Cash.' },
]

export default function WhyUs() {
  return (
    <section className="py-20 px-4 bg-white">
      <div className="max-w-6xl mx-auto">
        <div className="flex flex-col lg:flex-row gap-14 items-center">
          {/* Left — image with overlay badge */}
          <div className="lg:w-2/5 relative flex-shrink-0">
            <img
              src="/images/installation.jpg"
              alt="Waterlift installation team"
              className="rounded-3xl w-full object-cover h-96 shadow-xl"
            />
            {/* Floating badge */}
            <div className="absolute -bottom-5 -right-5 text-white text-center px-6 py-4 rounded-2xl shadow-lg" style={{ background: '#f97316' }}>
              <div className="text-3xl font-extrabold">10+</div>
              <div className="text-xs font-semibold opacity-90">Years Experience</div>
            </div>
          </div>

          {/* Right — benefits grid */}
          <div className="lg:w-3/5">
            <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#fff7ed', color: '#f97316' }}>
              Why Choose Us
            </span>
            <h2 className="text-3xl sm:text-4xl font-extrabold mt-3 mb-8" style={{ color: '#0f2d52' }}>
              More Than Just Solar Panels
            </h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {BENEFITS.map(b => (
                <div key={b.title} className="flex gap-4 p-4 rounded-2xl hover:bg-orange-50 transition-colors">
                  <div className="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0" style={{ background: '#fff7ed' }}>
                    {b.icon}
                  </div>
                  <div>
                    <h4 className="font-bold text-sm mb-0.5" style={{ color: '#0f2d52' }}>{b.title}</h4>
                    <p className="text-gray-500 text-xs leading-relaxed">{b.desc}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
