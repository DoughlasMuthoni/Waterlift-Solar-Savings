import { useState } from 'react'

const FAQS = [
  { q: 'How long does installation take?', a: 'Most systems are installed in a single day. Larger commercial setups may take two to three days. Either way, our team handles everything from survey to switch-on — you do not need to lift a finger.' },
  { q: 'Do I own the system on the Rent plan?', a: 'On the Rent Only plan, Waterlift owns and maintains the system. You enjoy the savings with zero responsibility for repairs. If you want ownership, choose our Rent-to-Own option.' },
  { q: 'What happens after the 72-month Rent-to-Own period?', a: 'The system is yours. Fully paid. You continue enjoying free solar power with no monthly payments — for the entire remaining lifespan of the system.' },
  { q: 'Will solar work during the rainy season?', a: 'Yes. Solar panels generate power even on cloudy days. Our systems include battery backup so you have power day and night, rain or shine. Kenya receives excellent solar irradiance year-round.' },
  { q: 'Can solar power my borehole pump?', a: 'Absolutely. Our Premium package is specifically designed for borehole pump surge loads. We are one of the few companies in Kenya that specialise in sizing solar systems for high-draw water equipment.' },
  { q: 'Is there a site survey before installation?', a: 'Yes, and it is completely free. Our engineers visit your property, assess your roof, measure your energy needs, and design a system tailored to you — at no cost and no obligation.' },
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
            Questions Before You Switch?
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Everything You Need to Know
          </h2>
        </div>
        <div className="bg-white rounded-3xl shadow-md border border-gray-100 px-7">
          {FAQS.map(f => <FAQItem key={f.q} q={f.q} a={f.a} />)}
        </div>
      </div>
    </section>
  )
}
