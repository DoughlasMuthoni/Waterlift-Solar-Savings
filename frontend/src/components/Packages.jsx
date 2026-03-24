import { useState, useEffect } from 'react'
import { motion } from 'framer-motion'

const TABS = [
  { id: 'rent',  label: '📅 Rent Only',   sub: 'No ownership · Lifetime maintenance included' },
  { id: 'rto',   label: '🏠 Rent-to-Own', sub: 'Own it after 72 months · Medium monthly fee' },
  { id: 'cash',  label: '💰 Pay Cash',     sub: 'Best long-term ROI · One-time investment' },
]

function getPrice(pkg, tab) {
  if (tab === 'rent')  return { main: `KES ${pkg.rent.monthly.toLocaleString()}`, sub: 'per month' }
  if (tab === 'rto')   return { main: `KES ${pkg.rto.monthly.toLocaleString()}`, sub: `per month · ${pkg.rto.months} months` }
  return { main: `KES ${pkg.cash.price.toLocaleString()}`, sub: `one-time · ROI ${pkg.cash.roi}` }
}

export default function Packages() {
  const [activeTab, setActiveTab] = useState('rent')
  const [packages, setPackages]   = useState([])
  const [loading, setLoading]     = useState(true)

  useEffect(() => {
    fetch('/api/get_packages.php')
      .then(r => r.json())
      .then(data => { setPackages(data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  return (
    <section id="packages" className="py-20 px-4" style={{ background: '#f0f4f8' }}>
      <div className="max-w-6xl mx-auto">

        {/* Heading */}
        <div className="text-center mb-12">
          <span className="inline-block text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-3"
            style={{ background: '#fff7ed', color: '#f97316' }}>
            Our Packages
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mb-3" style={{ color: '#0f2d52' }}>
            Transparent Pricing. Zero Surprises.
          </h2>
          <p className="text-gray-500 text-sm max-w-xl mx-auto">
            Choose the payment model that suits you — all include installation and professional commissioning.
          </p>
        </div>

        {/* Tabs */}
        <div className="flex justify-center mb-10">
          <div className="inline-flex rounded-2xl p-1.5 gap-1 shadow-sm" style={{ background: 'white', border: '1px solid #e2e8f0' }}>
            {TABS.map(tab => (
              <button key={tab.id} onClick={() => setActiveTab(tab.id)}
                className="flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                style={activeTab === tab.id
                  ? { background: '#f97316', color: 'white', boxShadow: '0 4px 12px rgba(249,115,22,0.35)' }
                  : { color: '#64748b' }
                }>
                {tab.label}
              </button>
            ))}
          </div>
        </div>
        <p className="text-center text-xs text-gray-400 mb-10">
          {TABS.find(t => t.id === activeTab)?.sub}
        </p>

        {/* Loading skeleton */}
        {loading && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[1,2,3].map(i => (
              <div key={i} className="rounded-3xl overflow-hidden bg-white shadow-sm animate-pulse">
                <div className="h-48 bg-slate-200" />
                <div className="p-6 space-y-3">
                  <div className="h-3 bg-slate-100 rounded-full w-3/4" />
                  <div className="h-3 bg-slate-100 rounded-full w-1/2" />
                  <div className="h-3 bg-slate-100 rounded-full w-5/6" />
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Cards */}
        {!loading && (
          <div className={`grid grid-cols-1 gap-8 ${packages.length === 2 ? 'md:grid-cols-2 max-w-3xl mx-auto' : packages.length === 1 ? 'max-w-sm mx-auto' : 'md:grid-cols-3'}`}>
            {packages.map((pkg, idx) => {
              const price = getPrice(pkg, activeTab)
              const isPopular = pkg.badge === 'Most Popular'

              return (
                <motion.div
                  key={pkg.id}
                  initial={{ opacity: 0, y: 24 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.4, delay: idx * 0.1 }}
                  className="relative flex flex-col rounded-3xl overflow-hidden"
                  style={{
                    boxShadow: isPopular
                      ? '0 24px 64px rgba(8,145,178,0.22), 0 4px 16px rgba(0,0,0,0.1)'
                      : '0 4px 20px rgba(0,0,0,0.08)',
                    transform: isPopular ? 'scale(1.03)' : 'scale(1)',
                  }}
                >
                  {/* Gradient header */}
                  <div className="relative px-7 pt-8 pb-10" style={{ background: pkg.gradient }}>
                    {pkg.badge && (
                      <div className="absolute top-4 right-4 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide"
                        style={{ background: '#f59e0b', color: '#0f2d52' }}>
                        ★ {pkg.badge}
                      </div>
                    )}
                    <div className="text-4xl mb-3">{pkg.icon}</div>
                    <h3 className="text-2xl font-extrabold text-white">{pkg.name}</h3>
                    <div className="flex items-center gap-2 mt-1 mb-6">
                      <span className="text-xs font-bold px-2.5 py-1 rounded-full"
                        style={{ background: 'rgba(255,255,255,0.2)', color: 'white' }}>
                        {pkg.capacity}
                      </span>
                      <span className="text-white/60 text-xs">{pkg.tagline}</span>
                    </div>
                    <div className="text-center">
                      <div className="text-4xl font-black text-white">{price.main}</div>
                      <div className="text-white/60 text-xs mt-1">{price.sub}</div>
                    </div>
                  </div>

                  {/* White body */}
                  <div className="flex-1 flex flex-col bg-white px-7 pb-7">
                    {pkg.savingsLabel && (
                      <div className="flex items-center justify-between -mt-5 mb-6 rounded-2xl px-5 py-3.5 shadow-md"
                        style={{ background: 'white', border: '2px solid #dcfce7' }}>
                        <div>
                          <p className="text-xs font-semibold" style={{ color: '#16a34a' }}>Estimated savings</p>
                          <p className="text-base font-extrabold" style={{ color: '#15803d' }}>{pkg.savingsLabel}</p>
                        </div>
                        <span className="text-2xl">📉</span>
                      </div>
                    )}

                    <ul className="space-y-2.5 mb-7 flex-1">
                      {pkg.features.map((f, i) => (
                        <li key={i} className="flex items-center gap-3 text-sm text-gray-600">
                          <span className="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                            style={{ background: '#fff7ed', color: '#f97316' }}>✓</span>
                          {f}
                        </li>
                      ))}
                    </ul>

                    <a href="#contact"
                      className="block text-center font-extrabold py-3.5 rounded-2xl text-sm transition-all hover:opacity-90"
                      style={isPopular
                        ? { background: pkg.gradient, color: 'white', boxShadow: '0 4px 14px rgba(8,145,178,0.3)' }
                        : { background: '#f1f5f9', color: '#0f2d52' }
                      }>
                      Get Started with {pkg.name} →
                    </a>
                  </div>
                </motion.div>
              )
            })}
          </div>
        )}

        {/* Personalized CTA */}
        <div className="mt-14 text-center rounded-3xl px-8 py-10 shadow-lg"
          style={{ background: 'linear-gradient(135deg, #0f2d52, #1e4d8c)' }}>
          <p className="text-white/70 text-sm mb-2">Want a plan based on your actual roof and bill?</p>
          <h3 className="text-2xl font-extrabold text-white mb-5">Get Your Personalized Solar Plan</h3>
          <a href="#"
            onClick={e => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }) }}
            className="inline-block font-bold px-8 py-3.5 rounded-full text-white text-sm transition-opacity hover:opacity-90"
            style={{ background: '#f97316' }}>
            ☀️ Start My Free Assessment →
          </a>
        </div>

        <p className="text-center text-gray-400 text-xs mt-8">
          All prices include installation &amp; VAT. Subject to free site survey.
          Savings estimated at KES 28.45/kWh · 75% solar yield.
        </p>
      </div>
    </section>
  )
}
