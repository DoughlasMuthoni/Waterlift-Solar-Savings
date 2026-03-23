import { useState, useEffect } from 'react'
import { motion } from 'framer-motion'

const KENYA_COUNTIES = [
  'Baringo','Bomet','Bungoma','Busia','Elgeyo-Marakwet','Embu','Garissa',
  'Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi',
  'Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos',
  'Makueni','Mandera','Marsabit','Meru','Migori','Mombasa','Murang\'a',
  'Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu',
  'Siaya','Taita-Taveta','Tana River','Tharaka-Nithi','Trans-Nzoia','Turkana',
  'Uasin Gishu','Vihiga','Wajir','West Pokot',
]

const PROPERTY_TYPES = [
  { value: 'residential', label: 'House / Villa / Apartment' },
  { value: 'commercial',  label: 'Commercial / Office / Hotel' },
  { value: 'warehouse',   label: 'Warehouse' },
  { value: 'industrial',  label: 'Industrial / Factory' },
]

const INTEREST = [
  { value: 'rent',  label: 'Rent Only' },
  { value: 'rto',   label: 'Rent-to-Own' },
  { value: 'cash',  label: 'Pay Cash' },
  { value: 'info',  label: 'Just getting info' },
]

const CONTACT_INFO = [
  {
    icon: (
      <svg viewBox="0 0 32 32" className="w-5 h-5 fill-white" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm7.27 19.406c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/>
      </svg>
    ),
    label: 'WhatsApp',
    value: '+254 768 117 070',
    href: 'https://wa.me/254768117070',
    bg: '#25D366',
  },
  {
    icon: <span className="text-lg">📞</span>,
    label: 'Phone',
    value: '+254 768 117 070',
    href: 'tel:+254768117070',
    bg: '#f97316',
  },
  {
    icon: <span className="text-lg">✉️</span>,
    label: 'Email',
    value: 'info@waterlift.co.ke',
    href: 'mailto:info@waterlift.co.ke',
    bg: '#0f2d52',
  },
  {
    icon: <span className="text-lg">📍</span>,
    label: 'Coverage',
    value: 'All 47 Counties — Kenya',
    href: null,
    bg: '#06b6d4',
  },
]

function InputField({ label, required, prefilled, children }) {
  return (
    <div>
      <label className="flex items-center gap-2 text-xs font-bold mb-1.5 uppercase tracking-wide" style={{ color: '#475569' }}>
        {label} {required && <span style={{ color: '#f97316' }}>*</span>}
        {prefilled && (
          <span className="normal-case font-semibold px-1.5 py-0.5 rounded-full text-[10px]"
            style={{ background: '#dcfce7', color: '#16a34a' }}>
            ✓ from assessment
          </span>
        )}
      </label>
      {children}
    </div>
  )
}

function StarPicker({ value, onChange }) {
  const [hovered, setHovered] = useState(0)
  return (
    <div className="flex gap-1">
      {[1,2,3,4,5].map(s => (
        <button key={s} type="button"
          onMouseEnter={() => setHovered(s)} onMouseLeave={() => setHovered(0)}
          onClick={() => onChange(s)}
          className="text-2xl transition-transform hover:scale-110"
          style={{ color: s <= (hovered || value) ? '#f59e0b' : '#d1d5db' }}>★</button>
      ))}
    </div>
  )
}

export default function ContactForm({ prefill }) {
  const [form, setForm] = useState({
    name: '', phone: '', email: '', county: '', propertyType: '', interest: '', message: '',
  })
  const [status, setStatus] = useState(null)
  const [submittedName, setSubmittedName] = useState('')
  const [reviewStep, setReviewStep]       = useState(null)   // null | 'form' | 'done'
  const [reviewSending, setReviewSending] = useState(false)
  const [review, setReview] = useState({ stars: 5, message: '' })

  // Map payment model label ↔ interest dropdown value
  const MODEL_TO_INTEREST         = { 'Rent Only': 'rent', 'Rent-to-Own': 'rto', 'Pay Cash': 'cash' }
  const MODEL_TO_INTEREST_REVERSE = { rent: 'Rent Only', rto: 'Rent-to-Own', cash: 'Pay Cash' }

  // When prefill changes (wizard complete or package selected), repopulate fields
  useEffect(() => {
    if (!prefill) return
    setForm(f => ({
      ...f,
      county:       prefill.county       || f.county,
      propertyType: prefill.propertyType || f.propertyType,
      interest:     MODEL_TO_INTEREST[prefill.paymentModel] || f.interest,
    }))
  }, [prefill])

  async function submitReview(e) {
    e.preventDefault()
    if (!review.message.trim()) return
    setReviewSending(true)
    const pkgLabel = prefill?.packageTier
      ? `${prefill.packageTier}${prefill.paymentModel ? ' · ' + prefill.paymentModel : ''}`
      : ''
    try {
      await fetch('/api/submit_testimonial.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: submittedName,
          location: prefill?.county || '',
          packageLabel: pkgLabel,
          stars: review.stars,
          message: review.message,
        }),
      })
    } catch { /* silent — lead is already saved */ }
    setReviewStep('done')
    setReviewSending(false)
  }

  function handleChange(e) {
    setForm(f => ({ ...f, [e.target.name]: e.target.value }))
  }

  async function handleSubmit(e) {
    e.preventDefault()
    setStatus('sending')
    try {
      const payload = {
        ...form,
        source: prefill ? 'wizard_then_form' : 'contact_form',
        // Wizard-collected data (null-safe)
        ownership:    prefill?.ownership    ?? null,
        billType:     prefill?.billType     ?? null,
        hasBorehole:  prefill?.hasBorehole  ?? null,
        monthlyBill:  prefill?.monthlyBill  ?? null,
        lat:          prefill?.lat          ?? null,
        lng:          prefill?.lng          ?? null,
        // Package tier — from CTA click OR auto-calculated from wizard answers
        packageTier:  prefill?.packageTier ?? null,
        // Payment model — from CTA click OR fallback to the interest dropdown value
        paymentModel: prefill?.paymentModel || MODEL_TO_INTEREST_REVERSE[form.interest] || null,
      }
      const res = await fetch('/api/save_lead.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
      if (res.ok) {
        setSubmittedName(form.name)
        setStatus('success')
        setForm({ name: '', phone: '', email: '', county: '', propertyType: '', interest: '', message: '' })
      } else {
        setStatus('error')
      }
    } catch {
      setStatus('error')
    }
  }

  const inputBase = {
    width: '100%',
    border: '1.5px solid #e2e8f0',
    borderRadius: '12px',
    padding: '11px 16px',
    fontSize: '14px',
    outline: 'none',
    background: '#f8fafc',
    color: '#1e293b',
    transition: 'border-color 0.2s',
  }

  return (
    <section id="contact" className="py-20 px-4" style={{ background: '#f0f4f8' }}>
      <div className="max-w-6xl mx-auto">

        {/* Heading */}
        <div className="text-center mb-12">
          <span className="inline-block text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-3"
            style={{ background: '#fff7ed', color: '#f97316' }}>
            Get In Touch
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mb-2" style={{ color: '#0f2d52' }}>
            Ready to Go Solar?
          </h2>
          <p className="text-gray-500 text-sm max-w-md mx-auto">
            Fill in the form and our team will reach out within 24 hours with your personalised quote.
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">

          {/* ── LEFT: Info cards ─────────────────────────────────────────── */}
          <div className="lg:col-span-2 space-y-4">
            {/* Contact cards */}
            {CONTACT_INFO.map(c => (
              <motion.div key={c.label}
                whileHover={{ x: 4 }}
                className="flex items-center gap-4 bg-white rounded-2xl px-5 py-4 shadow-sm"
                style={{ border: '1px solid #e2e8f0' }}>
                <div className="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                  style={{ background: c.bg }}>
                  {c.icon}
                </div>
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: '#94a3b8' }}>{c.label}</p>
                  {c.href
                    ? <a href={c.href} className="text-sm font-bold hover:underline" style={{ color: '#0f2d52' }}>{c.value}</a>
                    : <p className="text-sm font-bold" style={{ color: '#0f2d52' }}>{c.value}</p>
                  }
                </div>
              </motion.div>
            ))}

            {/* Business hours */}
            <div className="bg-white rounded-2xl px-5 py-4 shadow-sm" style={{ border: '1px solid #e2e8f0' }}>
              <p className="text-xs font-bold uppercase tracking-wide mb-3" style={{ color: '#94a3b8' }}>Business Hours</p>
              <div className="space-y-1.5 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-500">Mon – Fri</span>
                  <span className="font-semibold" style={{ color: '#0f2d52' }}>8:00 AM – 6:00 PM</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Saturday</span>
                  <span className="font-semibold" style={{ color: '#0f2d52' }}>8:00 AM – 4:00 PM</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Sunday</span>
                  <span className="font-semibold text-red-400">Closed</span>
                </div>
              </div>
            </div>

            {/* WhatsApp CTA */}
            <a href="https://wa.me/254768117070"
              target="_blank" rel="noopener noreferrer"
              className="flex items-center justify-center gap-3 w-full rounded-2xl py-4 font-bold text-sm text-white transition-opacity hover:opacity-90 shadow-lg"
              style={{ background: '#25D366' }}>
              <svg viewBox="0 0 32 32" className="w-5 h-5 fill-white"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm7.27 19.406c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/></svg>
              Chat on WhatsApp Now
            </a>
          </div>

          {/* ── RIGHT: Form ──────────────────────────────────────────────── */}
          <div className="lg:col-span-3 bg-white rounded-3xl shadow-xl overflow-hidden"
            style={{ border: '1px solid #e2e8f0' }}>

            {/* Form header */}
            <div className="px-8 py-5" style={{ background: 'linear-gradient(135deg, #0f2d52, #1e4d8c)' }}>
              <h3 className="text-lg font-extrabold text-white">Request a Free Quote</h3>
              <p className="text-white/60 text-xs mt-0.5">We respond within 24 hours · No obligation</p>
            </div>

            <div className="p-8">
              {status === 'success' ? (
                <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }}
                  className="py-8 space-y-6">

                  {/* ── Confirmation ── */}
                  <div className="text-center">
                    <div className="w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-4"
                      style={{ background: '#dcfce7' }}>🎉</div>
                    <h3 className="text-xl font-extrabold mb-2" style={{ color: '#0f2d52' }}>Message Received!</h3>
                    <p className="text-gray-500 text-sm mb-4">Our team will contact you within 24 hours with a personalised solar plan.</p>
                    <a href="https://wa.me/254768117070" target="_blank" rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 font-bold px-6 py-3 rounded-full text-white text-sm"
                      style={{ background: '#25D366' }}>
                      <svg viewBox="0 0 32 32" className="w-4 h-4 fill-white"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm7.27 19.406c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/></svg>
                      Also chat with us on WhatsApp
                    </a>
                  </div>

                  {/* ── Review prompt ── */}
                  <div className="rounded-2xl border-2 border-dashed border-amber-200 p-5"
                       style={{ background: '#fffbeb' }}>
                    {reviewStep === null && (
                      <div className="text-center">
                        <p className="text-2xl mb-2">⭐</p>
                        <p className="font-extrabold text-sm mb-1" style={{ color: '#0f2d52' }}>
                          Would you like to leave a quick review?
                        </p>
                        <p className="text-xs text-gray-500 mb-4">
                          Help other Kenyans make the switch to solar.
                        </p>
                        <div className="flex justify-center gap-3">
                          <button onClick={() => setReviewStep('form')}
                            className="px-5 py-2 rounded-full text-white text-sm font-bold transition-opacity hover:opacity-90"
                            style={{ background: '#f97316' }}>
                            Yes, I'll share →
                          </button>
                          <button onClick={() => setReviewStep('done')}
                            className="px-5 py-2 rounded-full text-sm font-semibold text-slate-500 hover:bg-amber-100 transition-colors">
                            Maybe later
                          </button>
                        </div>
                      </div>
                    )}

                    {reviewStep === 'form' && (
                      <motion.form onSubmit={submitReview} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }}
                        className="space-y-4">
                        <p className="font-extrabold text-sm" style={{ color: '#0f2d52' }}>
                          ✍️ Share your experience, {submittedName.split(' ')[0]}
                        </p>
                        <div>
                          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Rating</label>
                          <StarPicker value={review.stars} onChange={v => setReview(r => ({ ...r, stars: v }))} />
                        </div>
                        <div>
                          <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Review *</label>
                          <textarea
                            value={review.message}
                            onChange={e => setReview(r => ({ ...r, message: e.target.value }))}
                            required rows={3}
                            placeholder="Tell others what made you choose solar and how it's going…"
                            className="w-full border border-amber-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 resize-none"
                            style={{ background: 'white' }} />
                        </div>
                        <p className="text-xs text-gray-400">Your review appears after our team approves it.</p>
                        <div className="flex gap-2">
                          <button type="submit" disabled={reviewSending}
                            className="flex-1 py-2.5 rounded-xl text-white font-bold text-sm disabled:opacity-60 transition-opacity hover:opacity-90"
                            style={{ background: '#f97316' }}>
                            {reviewSending ? 'Submitting…' : 'Submit Review →'}
                          </button>
                          <button type="button" onClick={() => setReviewStep('done')}
                            className="px-4 py-2.5 rounded-xl text-sm text-slate-500 border border-slate-200 hover:bg-slate-50 transition-colors">
                            Skip
                          </button>
                        </div>
                      </motion.form>
                    )}

                    {reviewStep === 'done' && (
                      <div className="text-center py-2">
                        <p className="text-2xl mb-1">🙏</p>
                        <p className="font-bold text-sm" style={{ color: '#15803d' }}>Thank you!</p>
                        <p className="text-xs text-gray-500 mt-0.5">Your review will be published after approval.</p>
                      </div>
                    )}
                  </div>

                  <button onClick={() => { setStatus(null); setReviewStep(null) }}
                    className="block mx-auto text-xs text-gray-400 hover:text-gray-600">
                    Submit another enquiry
                  </button>
                </motion.div>
              ) : (
                <form onSubmit={handleSubmit} className="space-y-5">

                  {/* Pre-fill notice — shown only after wizard */}
                  {prefill && (
                    <motion.div
                      initial={{ opacity: 0, y: -8 }} animate={{ opacity: 1, y: 0 }}
                      className="flex items-start gap-3 rounded-2xl px-4 py-3 text-sm"
                      style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0' }}>
                      <span className="text-lg mt-0.5">✅</span>
                      <div>
                        <p className="font-bold text-xs" style={{ color: '#15803d' }}>Assessment data pre-filled</p>
                        <p className="text-xs mt-0.5" style={{ color: '#16a34a' }}>
                          We've filled in your county, property type{prefill?.paymentModel ? `, and payment preference (${prefill.paymentModel})` : ''}{prefill?.packageTier ? ` for the ${prefill.packageTier} package` : ''} from your solar assessment. Just add your contact details.
                        </p>
                      </div>
                    </motion.div>
                  )}

                  {/* Row 1: Name + Phone */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <InputField label="Full Name" required>
                      <input name="name" type="text" value={form.name} onChange={handleChange}
                        placeholder="John Kamau" required style={inputBase} />
                    </InputField>
                    <InputField label="Phone Number" required>
                      <input name="phone" type="tel" value={form.phone} onChange={handleChange}
                        placeholder="+254 7XX XXX XXX" required style={inputBase} />
                    </InputField>
                  </div>

                  {/* Row 2: Email + County */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <InputField label="Email Address">
                      <input name="email" type="email" value={form.email} onChange={handleChange}
                        placeholder="john@example.com" style={inputBase} />
                    </InputField>
                    <InputField label="Your County" required prefilled={!!(prefill?.county && form.county === prefill.county)}>
                      <select name="county" value={form.county} onChange={handleChange} required style={inputBase}>
                        <option value="">Select county…</option>
                        {KENYA_COUNTIES.map(c => <option key={c} value={c}>{c}</option>)}
                      </select>
                    </InputField>
                  </div>

                  {/* Row 3: Property Type + Interest */}
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <InputField label="Property Type" prefilled={!!(prefill?.propertyType && form.propertyType === prefill.propertyType)}>
                      <select name="propertyType" value={form.propertyType} onChange={handleChange} style={inputBase}>
                        <option value="">Select type…</option>
                        {PROPERTY_TYPES.map(p => <option key={p.value} value={p.value}>{p.label}</option>)}
                      </select>
                    </InputField>
                    <InputField label="I'm Interested In" prefilled={!!(prefill?.paymentModel && MODEL_TO_INTEREST[prefill.paymentModel] === form.interest)}>
                      <select name="interest" value={form.interest} onChange={handleChange} style={inputBase}>
                        <option value="">Select option…</option>
                        {INTEREST.map(i => <option key={i.value} value={i.value}>{i.label}</option>)}
                      </select>
                    </InputField>
                  </div>

                  {/* Message */}
                  <InputField label="Message">
                    <textarea name="message" value={form.message} onChange={handleChange} rows={3}
                      placeholder="Tell us about your property, monthly bill, or any specific needs…"
                      style={{ ...inputBase, resize: 'none' }} />
                  </InputField>

                  {status === 'error' && (
                    <p className="text-red-500 text-xs flex items-center gap-1">
                      ⚠️ Something went wrong. Please try again or WhatsApp us directly.
                    </p>
                  )}

                  <button type="submit" disabled={status === 'sending'}
                    className="w-full font-extrabold py-4 rounded-2xl text-white text-sm transition-all disabled:opacity-60 hover:opacity-90"
                    style={{ background: 'linear-gradient(135deg, #f97316, #c2410c)', boxShadow: '0 8px 24px rgba(249,115,22,0.35)' }}>
                    {status === 'sending' ? '⏳ Sending…' : '☀️ Submit — Get My Free Solar Quote →'}
                  </button>

                  <p className="text-center text-gray-400 text-xs">
                    🔒 Your details are private. No spam, ever.
                  </p>
                </form>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
