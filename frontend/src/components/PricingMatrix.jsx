import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

// ── Region multipliers ────────────────────────────────────────────────────────
const NORTH_COUNTIES   = ['turkana','marsabit','wajir','mandera','garissa','isiolo','samburu','tana river']
const NAIROBI_COUNTIES = ['nairobi']
const NYERI_COUNTIES   = ['nyeri']

function getRegionMultiplier(county = '') {
  const c = county.toLowerCase()
  if (NORTH_COUNTIES.some(n => c.includes(n)))   return 1.15
  if (NAIROBI_COUNTIES.some(n => c.includes(n))) return 0.98
  if (NYERI_COUNTIES.some(n => c.includes(n)))   return 0.95
  return 1.0
}

// Packages are loaded from the API at runtime (see useEffect below)

const PAYMENT_TABS = [
  { id: 'rent',  label: 'Rent Only',    icon: '📅', sub: 'Low monthly fee · No ownership · Lifetime maintenance' },
  { id: 'rto',   label: 'Rent-to-Own', icon: '🏠', sub: 'Own it after 72 months · Medium monthly fee' },
  { id: 'cash',  label: 'Pay Cash',    icon: '💰', sub: 'Best long-term ROI · One-time investment' },
]

// Returns the id of the recommended package based on bill + borehole
// Works with any package list: picks largest if borehole/high-bill, else by capacity
function getRecommendedId(packages, bill, hasBorehole) {
  if (!packages.length) return null
  const sorted = [...packages].sort((a, b) => a.capacityNum - b.capacityNum)
  if (hasBorehole || bill > 18000) return sorted[sorted.length - 1].id
  if (bill >= 7500 && sorted.length >= 2) return sorted[Math.floor(sorted.length / 2)].id
  return sorted[0].id
}

function calcSavings(bill, coverageFraction, regionMultiplier) {
  const kwh = bill / 28.45
  return Math.round(kwh * 0.75 * regionMultiplier * coverageFraction * 28.45)
}

// ── Price block ───────────────────────────────────────────────────────────────
function PriceBlock({ pkg, tab }) {
  const content = tab === 'rent'
    ? { main: `KES ${pkg.rent.monthly.toLocaleString()}`, sub: 'per month · no ownership' }
    : tab === 'rto'
    ? { main: `KES ${pkg.rto.monthly.toLocaleString()}`, sub: `per month · ${pkg.rto.months} months to own` }
    : { main: `KES ${pkg.cash.price.toLocaleString()}`, sub: `one-time · ROI in ~${pkg.cash.roi}` }
  return (
    <div className="rounded-2xl px-5 py-4" style={{ background: 'rgba(0,0,0,0.2)' }}>
      <div className="text-3xl font-black text-white leading-tight">{content.main}</div>
      <div className="text-white/60 text-xs mt-1">{content.sub}</div>
    </div>
  )
}

// ── Industrial CTA ────────────────────────────────────────────────────────────
function IndustrialCTA({ propertyType }) {
  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
      className="max-w-2xl mx-auto text-center rounded-3xl overflow-hidden shadow-xl">
      <div className="p-10" style={{ background: 'linear-gradient(135deg, #0f2d52, #1e4d8c)' }}>
        <div className="text-5xl mb-4">{propertyType === 'warehouse' ? '🏭' : '⚙️'}</div>
        <h3 className="text-2xl font-extrabold text-white mb-2">
          {propertyType === 'warehouse' ? 'Warehouse' : 'Industrial'} Solar Solutions
        </h3>
        <p className="text-white/70 text-sm mb-8 max-w-md mx-auto leading-relaxed">
          {propertyType === 'warehouse'
            ? 'Large roof areas and consistent daytime loads make warehouses ideal for solar. We design bespoke systems from 10 kW to 500 kW.'
            : 'Heavy machinery, three-phase requirements, and peak demand management need custom engineering. Our industrial team handles projects from 20 kW to multi-MW installations.'
          }
        </p>
        <div className="grid grid-cols-3 gap-4 mb-8 text-white">
          {[['Custom sizing', '10kW–1MW+'], ['3-phase support', 'Full industrial'], ['Dedicated PM', 'Your own engineer']].map(([l, v]) => (
            <div key={l} className="rounded-2xl p-3" style={{ background: 'rgba(255,255,255,0.1)' }}>
              <div className="text-sm font-bold">{v}</div>
              <div className="text-xs text-white/50 mt-0.5">{l}</div>
            </div>
          ))}
        </div>
        <a href="#contact"
          className="inline-block font-black text-sm px-10 py-4 rounded-full text-white shadow-lg transition-opacity hover:opacity-90"
          style={{ background: '#f97316' }}>
          Request Custom Industrial Quote →
        </a>
      </div>
      <div className="px-8 py-4 flex justify-center gap-6 text-xs font-medium" style={{ background: '#0a1e38', color: '#94a3b8' }}>
        <span>✓ Free site assessment</span>
        <span>✓ Custom engineering report</span>
        <span>✓ 48-hr response</span>
      </div>
    </motion.div>
  )
}

// ── Tab id → payment model label ─────────────────────────────────────────────
const TAB_LABEL = { rent: 'Rent Only', rto: 'Rent-to-Own', cash: 'Pay Cash' }

// ── Main component ────────────────────────────────────────────────────────────
export default function PricingMatrix({ location, answers, onSelectPackage }) {
  const [activeTab, setActiveTab]   = useState('rent')
  const [PACKAGES, setPackages]     = useState([])
  const [pkgLoading, setPkgLoading] = useState(true)

  useEffect(() => {
    fetch('/api/get_packages.php')
      .then(r => r.json())
      .then(data => { setPackages(data); setPkgLoading(false) })
      .catch(() => setPkgLoading(false))
  }, [])

  const { monthlyBill, hasBorehole, propertyType } = answers
  const isIndustrial     = propertyType === 'warehouse' || propertyType === 'industrial'
  const regionMultiplier = getRegionMultiplier(location?.county)
  const recommended      = getRecommendedId(PACKAGES, monthlyBill, hasBorehole)

  // If borehole → only show the largest-capacity package
  const largestId       = PACKAGES.length ? [...PACKAGES].sort((a, b) => b.capacityNum - a.capacityNum)[0].id : null
  const visiblePackages = hasBorehole
    ? PACKAGES.filter(p => p.id === largestId)
    : PACKAGES

  return (
    <section className="py-20 px-4" style={{ background: '#f0f4f8' }}>
      <div className="max-w-6xl mx-auto">

        {/* Heading */}
        <div className="text-center mb-12">
          <span className="inline-block text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-3"
            style={{ background: '#fff7ed', color: '#f97316' }}>
            ☀️ Your Solar Plan
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mb-2" style={{ color: '#0f2d52' }}>
            {isIndustrial ? 'Custom Industrial Solution' : 'Choose Your Package'}
          </h2>
          <p className="text-gray-500 text-sm max-w-xl mx-auto">
            {isIndustrial
              ? `${propertyType === 'warehouse' ? 'Warehouse' : 'Industrial'} properties require a custom-engineered solution.`
              : `Based on your KES ${monthlyBill.toLocaleString()} monthly bill${location?.county ? ` · ${location.county} County` : ''}${hasBorehole ? ' · Borehole package applied' : ''}.`
            }
          </p>
        </div>

        {/* Industrial CTA */}
        {isIndustrial && <IndustrialCTA propertyType={propertyType} />}

        {/* Loading skeleton */}
        {pkgLoading && !isIndustrial && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {[1,2,3].map(i => (
              <div key={i} className="rounded-3xl overflow-hidden bg-white shadow-sm animate-pulse">
                <div className="h-44 bg-slate-200" />
                <div className="p-6 space-y-3">
                  <div className="h-3 bg-slate-100 rounded-full w-3/4" />
                  <div className="h-3 bg-slate-100 rounded-full w-1/2" />
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Standard packages */}
        {!isIndustrial && !pkgLoading && (
          <>
            {/* Borehole notice */}
            {hasBorehole && (
              <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}
                className="max-w-xl mx-auto mb-8 flex items-center gap-3 rounded-2xl px-5 py-4 text-sm font-medium"
                style={{ background: '#fff7ed', border: '2px solid #fed7aa', color: '#c2410c' }}>
                <span className="text-2xl">💧</span>
                <div>
                  <strong>Borehole mode active.</strong> Essential and Standard tiers hidden — only the Premium 5.0 kW+ package safely handles borehole pump surge current.
                </div>
              </motion.div>
            )}

            {/* Payment tabs */}
            <div className="flex justify-center mb-10">
              <div className="inline-flex rounded-2xl p-1.5 gap-1 shadow-sm" style={{ background: 'white', border: '1px solid #e2e8f0' }}>
                {PAYMENT_TABS.map(tab => (
                  <button key={tab.id} onClick={() => setActiveTab(tab.id)}
                    className="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                    style={activeTab === tab.id
                      ? { background: '#f97316', color: 'white', boxShadow: '0 4px 12px rgba(249,115,22,0.35)' }
                      : { color: '#64748b' }
                    }>
                    <span>{tab.icon}</span> {tab.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Tab description */}
            <p className="text-center text-xs text-gray-400 mb-10">
              {PAYMENT_TABS.find(t => t.id === activeTab)?.sub}
            </p>

            {/* Package cards */}
            <AnimatePresence mode="wait">
              <motion.div key={activeTab}
                initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -16 }}
                transition={{ duration: 0.25 }}
                className={`grid gap-8 items-stretch ${visiblePackages.length === 1 ? 'max-w-sm mx-auto' : 'grid-cols-1 md:grid-cols-3'}`}>

                {visiblePackages.map((pkg, idx) => {
                  const isRec   = pkg.id === recommended
                  const savings = calcSavings(monthlyBill, pkg.coverageFraction, regionMultiplier)
                  const delay   = idx * 0.08

                  return (
                    <motion.div key={pkg.id}
                      initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }}
                      transition={{ duration: 0.35, delay }}
                      className="relative flex flex-col rounded-3xl overflow-hidden bg-white"
                      style={{
                        border: isRec ? '2px solid #f97316' : '1px solid #e2e8f0',
                        boxShadow: isRec
                          ? '0 0 0 4px rgba(249,115,22,0.12), 0 24px 64px rgba(249,115,22,0.22), 0 4px 20px rgba(0,0,0,0.08)'
                          : '0 4px 20px rgba(0,0,0,0.06)',
                      }}>

                      {/* Recommended ribbon — replaces the overlapping badge */}
                      {isRec ? (
                        <div className="flex items-center justify-center gap-2 py-2.5"
                          style={{ background: 'linear-gradient(90deg, #ea580c, #f97316, #fb923c)', color: 'white' }}>
                          <span className="text-sm">⭐</span>
                          <span className="text-xs font-black tracking-widest uppercase">Recommended for You</span>
                          <span className="text-sm">⭐</span>
                        </div>
                      ) : (
                        <div className="py-2.5" style={{ background: '#f8fafc' }} />
                      )}

                      {/* Coloured header */}
                      <div className="px-7 pt-6 pb-6" style={{ background: pkg.gradient }}>
                        <div className="flex items-start justify-between mb-4">
                          <div className="text-4xl leading-none">{pkg.icon}</div>
                          <span className="text-xs font-bold px-3 py-1 rounded-full"
                            style={{ background: 'rgba(255,255,255,0.2)', color: 'white' }}>
                            {pkg.capacity}
                          </span>
                        </div>
                        <h3 className="text-xl font-extrabold text-white mb-1">{pkg.name}</h3>
                        {pkg.tagline && (
                          <p className="text-white/70 text-xs mb-4 leading-relaxed">{pkg.tagline}</p>
                        )}
                        <PriceBlock pkg={pkg} tab={activeTab} />
                      </div>

                      {/* White body */}
                      <div className="flex-1 flex flex-col px-7 pt-5 pb-7">

                        {/* Savings badge — clean, no negative margin */}
                        <div className="flex items-center gap-3 rounded-xl px-4 py-3 mb-5"
                          style={{ background: isRec ? '#fff7ed' : '#f0fdf4', border: `1px solid ${isRec ? '#fed7aa' : '#bbf7d0'}` }}>
                          <span className="text-xl leading-none">{isRec ? '🔥' : '📉'}</span>
                          <div>
                            <p className="text-xs font-semibold" style={{ color: isRec ? '#c2410c' : '#16a34a' }}>
                              {isRec ? 'Your best match — est. savings' : 'Est. monthly savings'}
                            </p>
                            <p className="text-base font-extrabold" style={{ color: isRec ? '#ea580c' : '#15803d' }}>
                              KES {savings.toLocaleString()} / mo
                            </p>
                          </div>
                        </div>

                        {/* Features */}
                        <ul className="space-y-3 mb-7 flex-1">
                          {pkg.features.map((f, i) => (
                            <li key={i} className="flex items-start gap-3 text-sm text-gray-600 leading-snug">
                              <span className="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
                                style={{ background: isRec ? '#fff7ed' : '#f0fdf4', color: isRec ? '#f97316' : '#16a34a' }}>✓</span>
                              {f}
                            </li>
                          ))}
                        </ul>

                        {/* CTA */}
                        <a href="#contact"
                          onClick={() => onSelectPackage?.(pkg.name, TAB_LABEL[activeTab])}
                          className="block text-center font-extrabold py-4 rounded-2xl text-sm transition-all hover:opacity-90"
                          style={isRec
                            ? { background: 'linear-gradient(135deg, #ea580c, #f97316)', color: 'white', boxShadow: '0 6px 20px rgba(249,115,22,0.4)', letterSpacing: '0.3px' }
                            : { background: '#0f2d52', color: 'white' }
                          }>
                          {isRec ? '⭐ ' : ''}Start Saving with {pkg.name} →
                        </a>
                      </div>
                    </motion.div>
                  )
                })}
              </motion.div>
            </AnimatePresence>

            <p className="text-center text-gray-400 text-xs mt-10">
              Prices include installation &amp; VAT. Subject to free site survey. Savings at KES 28.45/kWh · 75% solar yield.
            </p>
          </>
        )}
      </div>
    </section>
  )
}
