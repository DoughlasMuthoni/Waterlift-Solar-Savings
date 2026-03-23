import { motion } from 'framer-motion'

const SERVICES = [
  {
    icon: '💧',
    title: 'Borehole Drilling',
    desc: 'Site selection to full installation across all 47 counties.',
    path: '/borehole-drilling',
  },
  {
    icon: '🔬',
    title: 'Hydrogeological Survey',
    desc: 'Scientific groundwater assessment before you drill.',
    path: '/hydrogeological-survey',
  },
  {
    icon: '🧪',
    title: 'Test Pumping',
    desc: 'Yield testing and water quality analysis.',
    path: '/test-pumping',
  },
  {
    icon: '🗼',
    title: 'Water Storage Towers',
    desc: 'Elevated tanks and distribution systems.',
    path: '/water-storage-towers',
  },
  {
    icon: '🛒',
    title: 'Online Shop',
    desc: 'Pumps, tanks, solar equipment — delivered to your door.',
    path: '/shop',
  },
]

export default function MoreServices() {
  return (
    <section className="py-16 px-4" style={{ background: '#0a1e38' }}>
      <div className="max-w-6xl mx-auto">

        {/* Heading */}
        <div className="text-center mb-10">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full"
                style={{ background: 'rgba(249,115,22,0.15)', color: '#f97316' }}>
            More from Waterlift
          </span>
          <h2 className="text-2xl sm:text-3xl font-extrabold text-white mt-3">
            We're More Than Solar
          </h2>
          <p className="text-white/50 text-sm mt-2 max-w-md mx-auto">
            Visit our full website to explore borehole drilling, water towers, and more —
            everything you need for water and energy independence.
          </p>
        </div>

        {/* Service cards */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-10">
          {SERVICES.map((s, i) => (
            <motion.a
              key={s.title}
              href={`https://waterliftsolar.africa${s.path}`}
              target="_blank"
              rel="noopener noreferrer"
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.35, delay: i * 0.07 }}
              className="flex flex-col items-center text-center rounded-2xl p-5 transition-all hover:scale-105 group"
              style={{ background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.08)' }}>
              <span className="text-3xl mb-3">{s.icon}</span>
              <h3 className="text-white text-xs font-extrabold mb-1 leading-tight">{s.title}</h3>
              <p className="text-white/40 text-[11px] leading-relaxed">{s.desc}</p>
              <span className="mt-3 text-[10px] font-bold opacity-0 group-hover:opacity-100 transition-opacity"
                    style={{ color: '#f97316' }}>
                Learn more →
              </span>
            </motion.a>
          ))}
        </div>

        {/* CTA */}
        <div className="text-center">
          <a
            href="https://waterliftsolar.africa"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 font-extrabold px-8 py-4 rounded-full text-white text-sm transition-all hover:opacity-90 shadow-lg"
            style={{ background: 'linear-gradient(135deg,#f97316,#c2410c)', boxShadow: '0 8px 24px rgba(249,115,22,0.3)' }}>
            <span>🌐</span> Visit waterliftsolar.africa
            <span className="text-white/70">→</span>
          </a>
          <p className="text-white/30 text-xs mt-3">
            Full catalogue of water & energy services across Kenya
          </p>
        </div>

      </div>
    </section>
  )
}
