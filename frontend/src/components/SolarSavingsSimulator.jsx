import { useState } from 'react'

// ── Kenya county sunlight multipliers ────────────────────────────────────────
const SUNLIGHT = {
  Garissa: 1.15,
  Turkana: 1.12,
  Mandera: 1.12,
  Marsabit: 1.10,
  Wajir: 1.10,
  'Tana River': 1.06,
  Kilifi: 1.05,
  'Taita Taveta': 1.05,
  Mombasa: 1.03,
  Kwale: 1.03,
  Makueni: 1.02,
  Kitui: 1.01,
  Machakos: 1.00,
  Kajiado: 1.00,
  Nairobi: 0.98,
  Kiambu: 0.97,
  "Murang'a": 0.97,
  Kirinyaga: 0.96,
  Nyeri: 0.95,
  Nyandarua: 0.93,
}

// ── Package definitions ───────────────────────────────────────────────────────
const PACKAGES = [
  {
    id: 'essential',
    name: 'Essential',
    systemKw: 1.5,
    maxBill: 7500,
    tagline: 'Perfect for small homes & apartments',
    icon: '🏠',
  },
  {
    id: 'standard',
    name: 'Standard',
    systemKw: 3.0,
    maxBill: 18000,
    tagline: 'Ideal for medium households',
    icon: '⚡',
  },
  {
    id: 'premium',
    name: 'Premium',
    systemKw: 5.0,
    maxBill: Infinity,
    tagline: 'Borehole Friendly — total energy independence',
    icon: '🌟',
  },
]

const KPLC_RATE = 22     // KES per kWh
const SUN_HOURS = 5      // base daily sun hours in Kenya

function getRecommendedPackage(bill) {
  if (bill <= 7500) return 'essential'
  if (bill <= 18000) return 'standard'
  return 'premium'
}

function calcSavings(bill, pkg, multiplier) {
  const monthlyKwh = bill / KPLC_RATE
  const systemOutput = pkg.systemKw * SUN_HOURS * 30 * multiplier
  const savedKwh = Math.min(systemOutput, monthlyKwh)
  const monthlySaving = Math.round(savedKwh * KPLC_RATE)
  const afterBill = Math.max(0, bill - monthlySaving)
  return { monthlySaving, afterBill, annualSaving: monthlySaving * 12 }
}

function fmt(n) {
  return n.toLocaleString('en-KE')
}

// ── Bar component ─────────────────────────────────────────────────────────────
function BillBar({ label, amount, maxAmount, color }) {
  const pct = maxAmount > 0 ? Math.round((amount / maxAmount) * 100) : 0
  return (
    <div className="flex items-center gap-3 mb-3">
      <span className="w-28 text-sm font-medium text-gray-600 text-right shrink-0">{label}</span>
      <div className="flex-1 bg-gray-200 rounded-full h-7 overflow-hidden">
        <div
          className={`h-full rounded-full flex items-center px-3 transition-all duration-500 ${color}`}
          style={{ width: `${Math.max(pct, 4)}%` }}
        >
          <span className="text-xs font-bold text-white whitespace-nowrap drop-shadow">
            KES {fmt(amount)}
          </span>
        </div>
      </div>
    </div>
  )
}

// ── Package card ──────────────────────────────────────────────────────────────
function PackageCard({ pkg, saving, isActive, isRecommended, onSelect }) {
  return (
    <div
      onClick={onSelect}
      className={`relative rounded-2xl p-5 cursor-pointer transition-all duration-200 flex flex-col gap-3
        ${isActive
          ? 'border-2 border-orange-500 bg-orange-50 shadow-lg shadow-orange-100'
          : 'border border-gray-200 bg-white hover:border-orange-300 hover:shadow-md'
        }`}
    >
      {isRecommended && (
        <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs font-bold px-3 py-0.5 rounded-full whitespace-nowrap">
          Recommended for you
        </span>
      )}

      <div className="text-center">
        <div className="text-3xl mb-1">{pkg.icon}</div>
        <h3 className="text-lg font-extrabold text-[#1a2e6c]">{pkg.name}</h3>
        <p className="text-xs text-gray-500 mt-0.5">{pkg.tagline}</p>
      </div>

      <div className="text-center bg-white rounded-xl py-3 border border-gray-100">
        <p className="text-xs text-gray-400 uppercase tracking-wide">System size</p>
        <p className="text-xl font-bold text-[#1a2e6c]">{pkg.systemKw} kW</p>
      </div>

      <div className="text-center">
        <p className="text-xs text-gray-400 uppercase tracking-wide">Monthly saving</p>
        <p className="text-2xl font-extrabold text-green-600">KES {fmt(saving)}</p>
      </div>

      <button
        className="mt-auto w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-colors shadow-sm"
        onClick={(e) => {
          e.stopPropagation()
          onSelect()
          document.getElementById('lead-form')?.scrollIntoView({ behavior: 'smooth' })
        }}
      >
        Get This Package
      </button>
    </div>
  )
}

// ── Main component ────────────────────────────────────────────────────────────
export default function SolarSavingsSimulator({ county }) {
  const [bill, setBill] = useState(5000)
  const [selectedPackage, setSelectedPackage] = useState(null)

  const multiplier = SUNLIGHT[county] ?? 1.0
  const recommended = getRecommendedPackage(bill)
  const activeId = selectedPackage ?? recommended
  const activePkg = PACKAGES.find((p) => p.id === activeId)

  const { monthlySaving, afterBill, annualSaving } = calcSavings(bill, activePkg, multiplier)

  function handleBillChange(raw) {
    const val = Math.max(500, Math.min(50000, Number(raw) || 500))
    setBill(val)
    setSelectedPackage(null) // reset override on bill change so auto-select kicks in
  }

  return (
    <section id="savings-section" className="py-14 px-4 bg-white">
      <div className="max-w-4xl mx-auto">

        {/* ── Header ── */}
        <div className="text-center mb-8">
          <h2 className="text-3xl font-extrabold text-[#1a2e6c]">Your Solar Savings Estimate</h2>
          {county ? (
            <p className="mt-1 text-sm text-gray-500">
              Based on <span className="font-semibold text-[#1a2e6c]">{county} County</span> sunlight
              {' '}
              <span className="text-orange-500 font-bold">(×{multiplier.toFixed(2)})</span>
            </p>
          ) : (
            <p className="mt-1 text-sm text-gray-400">Using average Kenya sunlight</p>
          )}
        </div>

        {/* ── Bill input ── */}
        <div className="bg-gray-50 rounded-2xl p-6 mb-8">
          <label className="block text-sm font-semibold text-gray-700 mb-4 text-center">
            Average Monthly KPLC Bill (KES)
          </label>
          <div className="flex flex-col sm:flex-row items-center gap-4">
            <input
              type="range"
              min={500}
              max={50000}
              step={100}
              value={bill}
              onChange={(e) => handleBillChange(e.target.value)}
              className="flex-1 w-full accent-orange-500 h-2 cursor-pointer"
            />
            <input
              type="number"
              min={500}
              max={50000}
              value={bill}
              onChange={(e) => handleBillChange(e.target.value)}
              className="w-32 text-center border border-gray-300 rounded-xl px-3 py-2 text-lg font-bold text-[#1a2e6c] focus:outline-none focus:ring-2 focus:ring-orange-400"
            />
          </div>
          <div className="flex justify-between text-xs text-gray-400 mt-1 px-1">
            <span>KES 500</span>
            <span>KES 50,000</span>
          </div>
        </div>

        {/* ── Before vs After bars ── */}
        <div className="bg-gray-50 rounded-2xl p-6 mb-8">
          <h3 className="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-5">
            Before vs. After Solar
          </h3>
          <BillBar label="Before Solar" amount={bill} maxAmount={bill} color="bg-red-500" />
          <BillBar label="After Solar" amount={afterBill} maxAmount={bill} color="bg-green-500" />

          <div className="mt-5 text-center">
            <p className="text-xl font-extrabold text-green-600">
              You save KES {fmt(monthlySaving)}/month
            </p>
            <p className="text-sm text-gray-500 mt-0.5">
              That's <span className="font-bold text-green-600">KES {fmt(annualSaving)}</span> per year
            </p>
          </div>
        </div>

        {/* ── Package cards ── */}
        <h3 className="text-center text-sm font-semibold text-gray-600 uppercase tracking-wide mb-6">
          Choose Your Package
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
          {PACKAGES.map((pkg) => {
            const { monthlySaving: pkgSaving } = calcSavings(bill, pkg, multiplier)
            return (
              <PackageCard
                key={pkg.id}
                pkg={pkg}
                saving={pkgSaving}
                isActive={activeId === pkg.id}
                isRecommended={recommended === pkg.id}
                onSelect={() => setSelectedPackage(pkg.id)}
              />
            )
          })}
        </div>

      </div>
    </section>
  )
}
