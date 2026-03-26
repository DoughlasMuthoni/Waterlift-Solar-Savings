import { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

const STEP_COUNT = 5

const stepVariants = {
  enter:  { opacity: 0, x: 56 },
  center: { opacity: 1, x: 0 },
  exit:   { opacity: 0, x: -56 },
}

// ── Option card — horizontal layout for better readability ───────────────────
function OptionCard({ icon, label, sublabel, selected, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex items-center gap-4 p-4 rounded-2xl border-2 transition-all w-full text-left group"
      style={selected
        ? { borderColor: '#f97316', background: 'linear-gradient(135deg, #fff7ed, #fff)', boxShadow: '0 4px 20px rgba(249,115,22,0.18)' }
        : { borderColor: '#e5e7eb', background: 'white' }
      }
    >
      <span className="text-2xl w-10 h-10 flex items-center justify-center rounded-xl shrink-0"
        style={{ background: selected ? '#fff7ed' : '#f8fafc' }}>
        {icon}
      </span>
      <div className="flex-1 min-w-0">
        <span className="block text-sm font-bold leading-tight" style={{ color: selected ? '#f97316' : '#1e293b' }}>{label}</span>
        {sublabel && <span className="block text-xs mt-0.5" style={{ color: '#94a3b8' }}>{sublabel}</span>}
      </div>
      <span className="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center"
        style={{ borderColor: selected ? '#f97316' : '#d1d5db', background: selected ? '#f97316' : 'white' }}>
        {selected && <span className="w-2 h-2 bg-white rounded-full block" />}
      </span>
    </button>
  )
}

// ── Step header ───────────────────────────────────────────────────────────────
function StepHeader({ title, subtitle }) {
  return (
    <div className="mb-6">
      <h3 className="text-xl font-extrabold leading-tight" style={{ color: '#0f2d52' }}>{title}</h3>
      {subtitle && <p className="text-gray-400 text-sm mt-1">{subtitle}</p>}
    </div>
  )
}

export default function DiscoveryWizard({ location, onComplete, onClose }) {
  const [step, setStep]         = useState(1)
  const [direction, setDirection] = useState(1)
  const [ownership, setOwnership]         = useState(null)   // 'owner' | 'tenant'
  const [propertyType, setPropertyType]   = useState(null)   // 'residential' | 'commercial' | 'warehouse' | 'industrial'
  const [billType, setBillType]           = useState(null)   // 'prepaid' | 'monthly'
  const [hasBorehole, setHasBorehole]     = useState(null)   // true | false
  const [monthlyBill, setMonthlyBill]     = useState(5000)

  function goNext() { setDirection(1);  setStep(s => s + 1) }
  function goBack() { setDirection(-1); setStep(s => s - 1) }

  function handleFinish() {
    onComplete({ ownership, propertyType, billType, hasBorehole, monthlyBill: Number(monthlyBill) })
  }

  // canProceed per step
  const canProceed = [
    ownership     !== null,
    propertyType  !== null,
    billType      !== null,
    hasBorehole   !== null,
    true,
  ][step - 1]

  const progressPct = Math.round((step / STEP_COUNT) * 100)

  return (
    <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6"
      style={{ zIndex: 10500, background: 'rgba(10,20,40,0.75)', backdropFilter: 'blur(4px)' }}>

      <motion.div
        initial={{ opacity: 0, scale: 0.92, y: 20 }}
        animate={{ opacity: 1, scale: 1, y: 0 }}
        exit={{ opacity: 0, scale: 0.92, y: 20 }}
        transition={{ duration: 0.3, ease: 'easeOut' }}
        className="w-full bg-white rounded-3xl overflow-hidden shadow-2xl flex"
        style={{ maxWidth: '900px', maxHeight: '92vh' }}
      >

        {/* ── LEFT PANEL — Promo ───────────────────────────────────────────── */}
        <div
          className="hidden lg:flex flex-col justify-between w-[42%] shrink-0 p-9 text-white relative overflow-hidden"
          style={{
            backgroundImage: "url('/images/solar-panels.jpg')",
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        >
          {/* overlay */}
          <div className="absolute inset-0" style={{ background: 'linear-gradient(160deg, rgba(15,45,82,0.93) 0%, rgba(15,45,82,0.80) 100%)' }} />

          <div className="relative z-10">
            <div className="inline-block bg-white rounded-2xl px-4 py-2 mb-8 shadow-md">
              <img src="/images/logo.png" alt="Waterlift Solar" className="h-10 object-contain" />
            </div>

            <div className="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-full mb-5 uppercase tracking-widest"
              style={{ background: '#f97316' }}>
              🎁 Limited Offer
            </div>

            <h2 className="text-4xl font-extrabold leading-tight mb-3">
              Our Solutions Sets you Free<br /><span style={{ color: '#f59e0b' }}>100%</span>
            </h2>
            <p className="text-white/70 text-sm leading-relaxed">
              Fill all the steps to get a personalized quote.
            </p>

            {/* Progress indicator on left */}
            <div className="mt-8 space-y-2.5">
              {['Ownership', 'Property Type', 'Electricity', 'Borehole', 'Monthly Spend'].map((label, i) => (
                <div key={label} className="flex items-center gap-3">
                  <div className="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-all"
                    style={i + 1 < step
                      ? { background: '#f97316', color: 'white' }
                      : i + 1 === step
                        ? { background: 'white', color: '#0f2d52' }
                        : { background: 'rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.5)' }
                    }>
                    {i + 1 < step ? '✓' : i + 1}
                  </div>
                  <span className="text-xs font-medium transition-all"
                    style={{ color: i + 1 <= step ? 'white' : 'rgba(255,255,255,0.4)' }}>
                    {label}
                  </span>
                </div>
              ))}
            </div>
          </div>

          <div className="relative z-10 space-y-2.5 text-sm text-white/70">
            {['Free installation & commissioning', 'Lifetime maintenance included', '24/7 remote monitoring'].map(b => (
              <div key={b} className="flex items-center gap-2">
                <span style={{ color: '#06b6d4' }}>✓</span> {b}
              </div>
            ))}
            {location?.county && (
              <div className="mt-4 pt-3 border-t border-white/20 text-xs text-white/50">
                📍 Serving {location.county} County
              </div>
            )}
          </div>
        </div>

        {/* ── RIGHT PANEL — Questions ───────────────────────────────────────── */}
        <div className="flex-1 flex flex-col overflow-hidden">
          {/* Top bar */}
          <div className="flex items-center justify-between px-8 pt-7 pb-4 shrink-0">
            <div>
              <span className="text-xs font-bold uppercase tracking-widest" style={{ color: '#f97316' }}>
                Step {step} of {STEP_COUNT}
              </span>
              <div className="w-48 bg-gray-100 rounded-full h-1.5 mt-2">
                <motion.div className="h-1.5 rounded-full" style={{ background: '#f97316' }}
                  animate={{ width: `${progressPct}%` }} transition={{ duration: 0.4 }} />
              </div>
            </div>
            <button onClick={onClose}
              className="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all"
              aria-label="Close">
              ✕
            </button>
          </div>

          {/* Scrollable step content */}
          <div className="flex-1 overflow-y-auto px-8 pb-4">
            <AnimatePresence mode="wait" custom={direction}>

              {/* STEP 1 — Ownership */}
              {step === 1 && (
                <motion.div key="s1" custom={direction} variants={stepVariants}
                  initial="enter" animate="center" exit="exit" transition={{ duration: 0.22 }}>
                  <StepHeader title="Do you own or rent the property?" subtitle="This helps us tailor the right payment model for you." />
                  <div className="space-y-3">
                    <OptionCard icon="🏡" label="I Own the Property" sublabel="Eligible for all payment models" selected={ownership === 'owner'} onClick={() => setOwnership('owner')} />
                    <OptionCard icon="🔑" label="I Rent / I'm a Tenant" sublabel="Rent model recommended" selected={ownership === 'tenant'} onClick={() => setOwnership('tenant')} />
                  </div>
                </motion.div>
              )}

              {/* STEP 2 — Property Type */}
              {step === 2 && (
                <motion.div key="s2" custom={direction} variants={stepVariants}
                  initial="enter" animate="center" exit="exit" transition={{ duration: 0.22 }}>
                  <StepHeader title="What type of property is it?" subtitle="We size systems differently for each property type." />
                  <div className="space-y-3">
                    <OptionCard icon="🏠" label="House / Villa / Apartment" sublabel="Residential use" selected={propertyType === 'residential'} onClick={() => setPropertyType('residential')} />
                    <OptionCard icon="🏬" label="Commercial" sublabel="Shops, offices, hotels, schools" selected={propertyType === 'commercial'} onClick={() => setPropertyType('commercial')} />
                    <OptionCard icon="🏭" label="Warehouse" sublabel="Storage & distribution facilities" selected={propertyType === 'warehouse'} onClick={() => setPropertyType('warehouse')} />
                    <OptionCard icon="⚙️" label="Industrial" sublabel="Factories & heavy machinery" selected={propertyType === 'industrial'} onClick={() => setPropertyType('industrial')} />
                  </div>
                </motion.div>
              )}

              {/* STEP 3 — Electricity Type */}
              {step === 3 && (
                <motion.div key="s3" custom={direction} variants={stepVariants}
                  initial="enter" animate="center" exit="exit" transition={{ duration: 0.22 }}>
                  <StepHeader title="How do you pay for electricity?" subtitle="This affects how we calculate your potential savings." />
                  <div className="space-y-3">
                    <OptionCard icon="📱" label="Prepaid (Tokens / M-PESA)" sublabel="Buy units as needed from KPLC" selected={billType === 'prepaid'} onClick={() => setBillType('prepaid')} />
                    <OptionCard icon="📄" label="Monthly Postpaid Bill" sublabel="Receive a monthly KPLC invoice" selected={billType === 'monthly'} onClick={() => setBillType('monthly')} />
                  </div>
                </motion.div>
              )}

              {/* STEP 4 — Borehole */}
              {step === 4 && (
                <motion.div key="s4" custom={direction} variants={stepVariants}
                  initial="enter" animate="center" exit="exit" transition={{ duration: 0.22 }}>
                  <StepHeader title="Do you have a borehole?" subtitle="Boreholes need a high-surge inverter — we'll size correctly." />
                  <div className="space-y-3">
                    <OptionCard icon="💧" label="Yes, I have a borehole" sublabel="Premium package will be applied automatically" selected={hasBorehole === true} onClick={() => setHasBorehole(true)} />
                    <OptionCard icon="🚿" label="No borehole" sublabel="Standard sizing applies" selected={hasBorehole === false} onClick={() => setHasBorehole(false)} />
                  </div>
                  {hasBorehole === true && (
                    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}
                      className="mt-4 flex items-start gap-3 rounded-2xl px-4 py-3 text-sm"
                      style={{ background: '#fff7ed', border: '1px solid #fed7aa' }}>
                      <span className="text-lg shrink-0">⚡</span>
                      <p style={{ color: '#c2410c' }}>
                        <strong>Premium auto-selected.</strong> Borehole pumps draw 3–5× surge current at start-up — our 5kW+ Premium package handles this safely.
                      </p>
                    </motion.div>
                  )}
                </motion.div>
              )}

              {/* STEP 5 — Monthly Spend */}
              {step === 5 && (
                <motion.div key="s5" custom={direction} variants={stepVariants}
                  initial="enter" animate="center" exit="exit" transition={{ duration: 0.22 }}>
                  <StepHeader title="What's your average monthly electricity spend?" subtitle="Drag the slider or type an exact amount in KES." />

                  <div className="rounded-2xl p-6 text-center mb-6" style={{ background: 'linear-gradient(135deg, #0f2d52, #1e4d8c)' }}>
                    <p className="text-white/60 text-xs mb-1 uppercase tracking-widest">Monthly Spend</p>
                    <div className="text-5xl font-extrabold text-white">
                      KES <span style={{ color: '#f59e0b' }}>{Number(monthlyBill).toLocaleString()}</span>
                    </div>
                    <p className="text-white/50 text-xs mt-1">per month</p>
                  </div>

                  <input type="range" min={500} max={500000} step={500} value={monthlyBill}
                    onChange={e => setMonthlyBill(e.target.value)}
                    className="w-full cursor-pointer mb-2" style={{ accentColor: '#f97316' }} />
                  <div className="flex justify-between text-xs text-gray-400 mb-5">
                    <span>KES 500</span><span>KES 500,000</span>
                  </div>

                  <div className="flex items-center gap-3">
                    <label className="text-sm font-medium text-gray-500 shrink-0">Exact amount:</label>
                    <div className="relative flex-1">
                      <span className="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">KES</span>
                      <input type="number" min={500} max={500000} value={monthlyBill}
                        onChange={e => setMonthlyBill(Math.min(500000, Math.max(500, Number(e.target.value))))}
                        className="w-full border border-gray-200 rounded-xl pl-12 pr-4 py-3 text-sm font-semibold focus:outline-none"
                        style={{ '--ring-color': '#f97316' }} />
                    </div>
                  </div>

                  {/* Tier hint */}
                  <div className="mt-4 text-xs text-center font-medium" style={{ color: '#64748b' }}>
                    {Number(monthlyBill) < 7500 && '→ Essential package (1.5 kW) recommended for your spend'}
                    {Number(monthlyBill) >= 7500 && Number(monthlyBill) <= 18000 && '→ Standard package (3.0 kW) recommended for your spend'}
                    {Number(monthlyBill) > 18000 && '→ Premium package (5.0 kW+) recommended for your spend'}
                  </div>
                </motion.div>
              )}

            </AnimatePresence>
          </div>

          {/* Footer nav */}
          <div className="shrink-0 flex items-center justify-between px-8 py-5 border-t border-gray-100">
            {step > 1
              ? <button onClick={goBack} className="flex items-center gap-1.5 text-sm font-semibold text-gray-400 hover:text-gray-600 transition-colors">
                  ← Back
                </button>
              : <div />
            }

            {step < STEP_COUNT
              ? <button onClick={goNext} disabled={!canProceed}
                  className="flex items-center gap-2 font-bold px-8 py-3 rounded-full text-white text-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                  style={{ background: canProceed ? '#f97316' : '#94a3b8' }}>
                  Continue <span>→</span>
                </button>
              : <button onClick={handleFinish}
                  className="flex items-center gap-2 font-bold px-8 py-3 rounded-full text-white text-sm transition-opacity hover:opacity-90"
                  style={{ background: '#0f2d52' }}>
                  ☀️ Show My Results
                </button>
            }
          </div>
        </div>
      </motion.div>
    </div>
  )
}
