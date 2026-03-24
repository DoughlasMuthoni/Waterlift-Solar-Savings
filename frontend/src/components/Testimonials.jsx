import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

const STARS = [5, 4, 3, 2, 1]

function StarPicker({ value, onChange }) {
  const [hovered, setHovered] = useState(0)
  return (
    <div className="flex gap-1">
      {[1,2,3,4,5].map(s => (
        <button
          key={s}
          type="button"
          onMouseEnter={() => setHovered(s)}
          onMouseLeave={() => setHovered(0)}
          onClick={() => onChange(s)}
          className="text-2xl transition-transform hover:scale-110"
          style={{ color: s <= (hovered || value) ? '#f59e0b' : '#d1d5db' }}>
          ★
        </button>
      ))}
    </div>
  )
}

export default function Testimonials() {
  const [reviews, setReviews]       = useState([])
  const [loading, setLoading]       = useState(true)
  const [showForm, setShowForm]     = useState(false)
  const [submitted, setSubmitted]   = useState(false)
  const [sending, setSending]       = useState(false)
  const [formError, setFormError]   = useState('')
  const [form, setForm] = useState({ name: '', location: '', packageLabel: '', stars: 5, message: '' })

  useEffect(() => {
    fetch('/api/get_testimonials.php')
      .then(r => r.json())
      .then(data => { setReviews(data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  const handleChange = e => setForm(f => ({ ...f, [e.target.name]: e.target.value }))

  const handleSubmit = async e => {
    e.preventDefault()
    if (!form.name.trim() || !form.message.trim()) {
      setFormError('Please enter your name and a message.')
      return
    }
    setFormError('')
    setSending(true)
    try {
      const res = await fetch('/api/submit_testimonial.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      })
      const data = await res.json()
      if (data.success) {
        setSubmitted(true)
        setShowForm(false)
        setForm({ name: '', location: '', packageLabel: '', stars: 5, message: '' })
      } else {
        setFormError('Something went wrong. Please try again.')
      }
    } catch {
      setFormError('Network error. Please try again.')
    } finally {
      setSending(false)
    }
  }

  return (
    <section className="py-20 px-4" style={{ background: '#f8fafc' }}>
      <div className="max-w-6xl mx-auto">

        {/* Heading */}
        <div className="text-center mb-14">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#e0f2fe', color: '#06b6d4' }}>
            Customer Stories
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Trusted Across Kenya
          </h2>
          <p className="mt-3 text-gray-500 text-sm max-w-md mx-auto">
            Real experiences from real customers. Have one to share?
          </p>
        </div>

        {/* Loading skeleton */}
        {loading && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-7">
            {[1,2,3].map(i => (
              <div key={i} className="bg-white rounded-3xl p-7 shadow-md animate-pulse space-y-3">
                <div className="h-3 bg-slate-100 rounded-full w-1/3" />
                <div className="h-3 bg-slate-100 rounded-full w-full" />
                <div className="h-3 bg-slate-100 rounded-full w-5/6" />
                <div className="h-3 bg-slate-100 rounded-full w-4/6" />
                <div className="flex items-center gap-3 pt-4 border-t border-gray-100 mt-4">
                  <div className="w-11 h-11 rounded-full bg-slate-200 shrink-0" />
                  <div className="space-y-1.5 flex-1">
                    <div className="h-3 bg-slate-100 rounded-full w-1/2" />
                    <div className="h-2.5 bg-slate-100 rounded-full w-1/3" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Review cards */}
        {!loading && reviews.length > 0 && (
          <div className={`grid grid-cols-1 gap-7 ${reviews.length === 1 ? 'max-w-sm mx-auto' : reviews.length === 2 ? 'md:grid-cols-2 max-w-3xl mx-auto' : 'md:grid-cols-3'}`}>
            {reviews.map(r => {
              const initials = r.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0,2)
              return (
                <motion.div
                  key={r.id}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.4 }}
                  className="bg-white rounded-3xl p-7 shadow-md hover:shadow-lg transition-shadow flex flex-col">
                  {/* Stars */}
                  <div className="flex gap-0.5 mb-4">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <span key={i} style={{ color: i < r.stars ? '#f59e0b' : '#e2e8f0' }}>★</span>
                    ))}
                  </div>
                  <p className="text-gray-600 text-sm leading-relaxed flex-1 mb-5">"{r.message}"</p>
                  <div className="flex items-center gap-3 pt-4 border-t border-gray-100">
                    {r.avatar_url
                      ? <img src={r.avatar_url} alt={r.name} className="w-11 h-11 rounded-full object-cover shrink-0" />
                      : (
                        <div className="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-black shrink-0"
                             style={{ background: '#0f2d52' }}>
                          {initials}
                        </div>
                      )
                    }
                    <div>
                      <div className="font-bold text-sm" style={{ color: '#0f2d52' }}>{r.name}</div>
                      {r.location && <div className="text-xs text-gray-400">{r.location}</div>}
                      {r.package_label && (
                        <div className="text-xs font-semibold mt-0.5" style={{ color: '#f97316' }}>{r.package_label}</div>
                      )}
                    </div>
                  </div>
                </motion.div>
              )
            })}
          </div>
        )}

        {/* Submit-a-review CTA */}
        <div className="mt-14 text-center">
          <AnimatePresence mode="wait">
            {submitted ? (
              <motion.div
                key="thanks"
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="inline-flex flex-col items-center gap-3 rounded-3xl px-10 py-8 shadow-md"
                style={{ background: 'linear-gradient(135deg,#f0fdf4,#dcfce7)' }}>
                <span className="text-4xl">🎉</span>
                <p className="font-extrabold text-lg" style={{ color: '#15803d' }}>Thank you for your review!</p>
                <p className="text-sm text-green-600">It will appear here after our team approves it — usually within 24 hours.</p>
              </motion.div>
            ) : !showForm ? (
              <motion.div key="cta" initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
                <p className="text-gray-400 text-sm mb-3">Happy with WaterliftSolar Savings? Let others know.</p>
                <button
                  onClick={() => setShowForm(true)}
                  className="inline-flex items-center gap-2 font-bold px-8 py-3.5 rounded-full text-white text-sm transition-opacity hover:opacity-90 shadow-md"
                  style={{ background: '#f97316' }}>
                  ✍️ Leave a Review
                </button>
              </motion.div>
            ) : (
              <motion.div
                key="form"
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -8 }}
                className="max-w-lg mx-auto text-left">
                <div className="bg-white rounded-3xl shadow-lg border border-slate-100 p-7">
                  <div className="flex items-center justify-between mb-5">
                    <h3 className="text-lg font-extrabold" style={{ color: '#0f2d52' }}>Share Your Experience</h3>
                    <button onClick={() => setShowForm(false)} className="text-slate-400 hover:text-slate-600">
                      <span className="text-xl leading-none">✕</span>
                    </button>
                  </div>

                  {formError && (
                    <div className="mb-4 px-4 py-3 rounded-xl text-sm bg-red-50 text-red-600 border border-red-200">
                      {formError}
                    </div>
                  )}

                  <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Stars */}
                    <div>
                      <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Rating</label>
                      <StarPicker value={form.stars} onChange={v => setForm(f => ({ ...f, stars: v }))} />
                    </div>

                    {/* Name + Location */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <div>
                        <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Name *</label>
                        <input
                          name="name" value={form.name} onChange={handleChange} required
                          placeholder="e.g. James Mwangi"
                          className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">County / Town</label>
                        <input
                          name="location" value={form.location} onChange={handleChange}
                          placeholder="e.g. Nairobi"
                          className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                      </div>
                    </div>

                    {/* Package */}
                    <div>
                      <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Package (optional)</label>
                      <input
                        name="packageLabel" value={form.packageLabel} onChange={handleChange}
                        placeholder="e.g. Standard · Rent-to-Own"
                        className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
                    </div>

                    {/* Message */}
                    <div>
                      <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Your Review *</label>
                      <textarea
                        name="message" value={form.message} onChange={handleChange} required
                        rows={4}
                        placeholder="Tell others about your experience with Waterlift Solar…"
                        className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 resize-none" />
                    </div>

                    <p className="text-xs text-gray-400">Your review will be visible after our team approves it.</p>

                    <button
                      type="submit" disabled={sending}
                      className="w-full py-3.5 rounded-2xl text-white font-extrabold text-sm transition-opacity hover:opacity-90 disabled:opacity-60"
                      style={{ background: '#f97316' }}>
                      {sending ? 'Submitting…' : 'Submit Review →'}
                    </button>
                  </form>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

      </div>
    </section>
  )
}
