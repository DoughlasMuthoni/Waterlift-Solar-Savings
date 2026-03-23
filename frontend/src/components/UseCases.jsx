import { useState, useEffect } from 'react'

export default function UseCases() {
  const [cases, setCases]   = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch('/api/get_use_cases.php')
      .then(r => r.json())
      .then(data => { setCases(data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  return (
    <section id="use-cases" className="py-20 px-4" style={{ background: '#f8fafc' }}>
      <div className="max-w-6xl mx-auto">
        <div className="text-center mb-14">
          <span className="text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full" style={{ background: '#e0f2fe', color: '#06b6d4' }}>
            What We Power
          </span>
          <h2 className="text-3xl sm:text-4xl font-extrabold mt-3" style={{ color: '#0f2d52' }}>
            Solar for Every Need
          </h2>
          <p className="mt-3 text-gray-500 max-w-xl mx-auto text-sm">
            From single-room homes to large commercial setups — we have a package for every situation across Kenya.
          </p>
        </div>

        {/* Loading skeleton */}
        {loading && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[1,2,3].map(i => (
              <div key={i} className="rounded-3xl overflow-hidden bg-white shadow-md animate-pulse">
                <div className="h-52 bg-slate-200" />
                <div className="p-6 space-y-3">
                  <div className="h-3 bg-slate-100 rounded-full w-1/3" />
                  <div className="h-4 bg-slate-100 rounded-full w-2/3" />
                  <div className="h-3 bg-slate-100 rounded-full w-full" />
                  <div className="h-3 bg-slate-100 rounded-full w-4/5" />
                </div>
              </div>
            ))}
          </div>
        )}

        {!loading && cases.length > 0 && (
          <div className={`grid grid-cols-1 gap-8 ${cases.length === 1 ? 'max-w-sm mx-auto' : cases.length === 2 ? 'md:grid-cols-2 max-w-3xl mx-auto' : 'md:grid-cols-3'}`}>
            {cases.map(c => (
              <div key={c.id} className="bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-xl transition-shadow group">
                <div className="relative overflow-hidden h-52">
                  <img
                    src={c.image_url || 'https://placehold.co/600x400/e2e8f0/94a3b8?text=Use+Case'}
                    alt={c.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    onError={e => { e.target.src = 'https://placehold.co/600x400/e2e8f0/94a3b8?text=Use+Case' }}
                  />
                  <div className="absolute inset-0" style={{ background: 'linear-gradient(to top, rgba(15,45,82,0.7) 0%, transparent 60%)' }} />
                  <span className="absolute top-4 left-4 text-xs font-bold px-3 py-1 rounded-full" style={{ background: '#f97316', color: 'white' }}>
                    {c.tag}
                  </span>
                </div>
                <div className="p-6">
                  <h3 className="text-lg font-bold mb-2" style={{ color: '#0f2d52' }}>{c.title}</h3>
                  <p className="text-gray-500 text-sm mb-4 leading-relaxed">{c.description}</p>
                  <div className="flex items-center justify-between">
                    {c.stat_label && (
                      <span className="text-sm font-bold" style={{ color: '#f97316' }}>{c.stat_label}</span>
                    )}
                    <a href="#contact" className="text-xs font-bold px-4 py-2 rounded-full transition-colors text-white ml-auto" style={{ background: '#0f2d52' }}>
                      Learn more →
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  )
}
