import { useState } from 'react'

const FAQS = [
  { q: 'How long does installation take?', a: 'A standard residential installation takes 1–2 days. Commercial or borehole setups may take 2–4 days depending on the site complexity.' },
  { q: 'Do I own the system on the Rent plan?', a: 'No — on the Rent plan, Waterlift retains ownership and is responsible for all maintenance and replacements. You simply pay a fixed monthly fee and enjoy clean power.' },
  { q: 'What happens after the 72-month Rent-to-Own period?', a: 'After 72 months you own the system outright at no additional cost. The system typically has a 25-year lifespan, so you enjoy free solar for over 15 more years.' },
  { q: 'Will solar work during the rainy season?', a: 'Yes. Solar panels generate electricity from daylight, not direct sunlight. Output reduces slightly on overcast days but your battery bank bridges the gap.' },
  { q: 'Can solar power my borehole pump?', a: 'Absolutely — this is one of our specialties. Borehole pumps require high surge current at start-up. Our Premium package is engineered specifically for this load profile.' },
  { q: 'Is there a site survey before installation?', a: 'Yes. After you sign up, our engineers conduct a free site survey to assess your roof structure, shade conditions, and load requirements before any work begins.' },
]

function FAQItem({ q, a }) {
  const [open, setOpen] = useState(false)
  return (
    <div className="border-b border-gray-100 last:border-0">
      <button
        onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between py-5 text-left gap-4"
      >
        <span className="font-semibold text-sm" style={{ color: '#0f2d52' }}>{q}</span>
        <span className="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold transition-transform"
          style={{ background: open ? '#f97316' : '#0f2d52', transform: open ? 'rotate(45deg)' : 'none' }}>
          +
        </span>
      </button>
      {open && (
        <p className="pb-5 text-gray-500 text-sm leading-relaxed pr-10">{a}</p>
      )}
    </div>
  )
}

export default function FAQ() {
  return (
    <section className="py-20 px-4 bg-white">
      <div className="max-w-3xl mx-auto">
        <div className="text-center mb-12">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#fff7ed', color: '#f97316' }}>
            FAQ
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Common Questions
          </h2>
        </div>
        <div className="bg-white rounded-3xl shadow-md border border-gray-100 px-7">
          {FAQS.map(f => <FAQItem key={f.q} q={f.q} a={f.a} />)}
        </div>
      </div>
    </section>
  )
}
