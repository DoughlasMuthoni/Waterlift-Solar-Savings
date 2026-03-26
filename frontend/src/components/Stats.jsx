const STATS = [
  { value: '47',    unit: '',    label: 'Counties Covered',   icon: '🗺️' },
  { value: '105+',   unit: '',  label: 'Systems Running',     icon: '⚡' },
  { value: '75%',   unit: '',   label: 'Average Savings',     icon: '📉' },
  { value: '28.45', unit: 'KES/kWh', label: 'Per kWh You Keep', icon: '💡' },
]

export default function Stats() {
  return (
    <section style={{ background: '#0f2d52' }} className="py-12 px-4">
      <div className="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6">
        {STATS.map(s => (
          <div key={s.label} className="text-center">
            <div className="text-3xl mb-1">{s.icon}</div>
            <div className="text-3xl sm:text-4xl font-extrabold" style={{ color: '#f97316' }}>
              {s.value}
              {s.unit && <span className="text-base font-semibold text-white/60 ml-1">{s.unit}</span>}
            </div>
            <div className="text-white/70 text-sm mt-1">{s.label}</div>
          </div>
        ))}
      </div>
    </section>
  )
}
