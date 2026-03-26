export default function Footer() {
  const year = new Date().getFullYear()

  return (
    <footer style={{ background: '#091e38' }}>
      {/* Main footer body */}
      <div className="max-w-6xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-4 gap-10">

        {/* Brand col */}
        <div className="md:col-span-2">
          {/* Logo on white pill — visible on dark bg */}
          <div className="inline-block bg-white rounded-2xl px-4 py-2 mb-4 shadow-md">
            <img src="/images/logo.jpeg" alt="Waterlift Solar Savings" className="h-10 object-contain" />
          </div>
          <p className="text-white/60 text-sm leading-relaxed max-w-xs">
            Reliable solar energy and clean water for homes, schools, and farms across all 47 counties in Kenya.
          </p>
          {/* WhatsApp chip */}
          <a href="https://wa.me/254768117070" target="_blank" rel="noopener noreferrer"
            className="inline-flex items-center gap-2 mt-5 px-4 py-2.5 rounded-full text-white text-xs font-bold transition-opacity hover:opacity-80"
            style={{ background: '#25D366' }}>
            <svg viewBox="0 0 32 32" className="w-4 h-4 fill-white"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm7.27 19.406c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/></svg>
            WhatsApp Us: +254 768 117 070
          </a>
        </div>

        {/* Quick links */}
        <div>
          <h4 className="text-white font-bold text-sm mb-4 uppercase tracking-widest">Quick Links</h4>
          <ul className="space-y-2.5">
            {[
              ['How It Works',  '#how-it-works'],
              ['Use Cases',     '#use-cases'],
              ['Our Packages',  '#packages'],
              ['Testimonials',  '#testimonials'],
              ['FAQ',           '#faq'],
              ['Contact Us',    '#contact'],
            ].map(([l, h]) => (
              <li key={h}>
                <a href={h} className="text-white/55 hover:text-white text-sm transition-colors flex items-center gap-1.5">
                  <span style={{ color: '#f97316' }}>›</span> {l}
                </a>
              </li>
            ))}
          </ul>
        </div>

        {/* Services — links to main website */}
        <div>
          <h4 className="text-white font-bold text-sm mb-1 uppercase tracking-widest">All Services</h4>
          <a href="https://waterliftsolar.africa" target="_blank" rel="noopener noreferrer"
             className="inline-flex items-center gap-1 text-[10px] font-semibold mb-4 transition-colors hover:opacity-80"
             style={{ color: '#f97316' }}>
            🌐 waterliftsolar.africa ↗
          </a>
          <ul className="space-y-2.5 text-sm">
            {[
              ['Residential Solar',       '/solar'],
              ['Commercial Solar',        '/solar'],
              ['Borehole Drilling',       '/borehole-drilling'],
              ['Water Heating Solutions',  '/solar-energy-solutions-in-kenya/water-heating-solutions/'],
              ['Our Projects',            '/our-projects'],
              ['Water Storage Towers',    '/water-storage-towers'],
              ['Online Shop',             '/shop'],
            ].map(([label, path]) => (
              <li key={label}>
                <a href={`https://waterliftsolar.africa${path}`}
                   target="_blank" rel="noopener noreferrer"
                   className="flex items-center gap-1.5 transition-colors hover:text-white"
                   style={{ color: 'rgba(255,255,255,0.55)' }}>
                  <span style={{ color: '#06b6d4' }}>✓</span> {label}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t px-6 py-5" style={{ borderColor: 'rgba(255,255,255,0.07)' }}>
        <div className="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
          <p className="text-white/35 text-xs text-center">
            © {year} Waterlift Solar Limited. All rights reserved.
          </p>
          <div className="flex items-center gap-5 text-xs text-white/35">
            <a href="https://waterliftsolar.africa" target="_blank" rel="noopener noreferrer"
               className="hover:text-orange-400 transition-colors font-semibold">
              🌐 waterliftsolar.africa
            </a>
            <a href="#" className="hover:text-white/60 transition-colors">Privacy Policy</a>
            <a href="#" className="hover:text-white/60 transition-colors">Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  )
}
