import { useState, useEffect } from 'react'

export default function Header() {
  const [scrolled, setScrolled] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  const navLinks = [
    { label: 'How It Works', href: '#how-it-works' },
    { label: 'Use Cases',    href: '#use-cases' },
    { label: 'Packages',     href: '#packages' },
    { label: 'Contact',      href: '#contact' },
  ]

  return (
    <header
      className="fixed top-0 left-0 right-0 z-[9000] transition-all duration-300"
      style={{ background: scrolled ? '#0f2d52' : 'transparent' }}
    >
      <div className="max-w-7xl mx-auto px-5 py-3 flex items-center justify-between">
        {/* Real logo */}
        <a href="/">
          <img
            src="/images/logo.png"
            alt="Waterlift Solar"
            className="h-12 object-contain"
          />
        </a>

        {/* Desktop nav */}
        <nav className="hidden md:flex items-center gap-7">
          {navLinks.map(l => (
            <a
              key={l.href}
              href={l.href}
              className="text-sm font-medium transition-colors"
              style={{ color: scrolled ? '#e2e8f0' : 'white' }}
            >
              {l.label}
            </a>
          ))}
          {/* Full website link — divider + external link */}
          <span style={{ color: 'rgba(255,255,255,0.2)' }}>|</span>
          <a
            href="https://waterliftsolar.africa"
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-1.5 text-xs font-semibold transition-colors hover:opacity-100"
            style={{ color: scrolled ? '#94a3b8' : 'rgba(255,255,255,0.6)' }}
            title="Borehole drilling, water towers, hydrogeological surveys & more"
          >
            🌐 <span className="hidden lg:inline">waterliftsolar</span>.africa
          </a>
          <a
            href="#contact"
            className="text-sm font-bold px-5 py-2.5 rounded-full transition-colors"
            style={{ background: '#f97316', color: 'white' }}
          >
            Get Free Quote
          </a>
        </nav>

        {/* Mobile burger */}
        <button
          className="md:hidden text-white p-2"
          onClick={() => setMenuOpen(o => !o)}
          aria-label="Toggle menu"
        >
          <div className={`w-6 h-0.5 bg-white mb-1.5 transition-all ${menuOpen ? 'rotate-45 translate-y-2' : ''}`} />
          <div className={`w-6 h-0.5 bg-white mb-1.5 transition-all ${menuOpen ? 'opacity-0' : ''}`} />
          <div className={`w-6 h-0.5 bg-white transition-all ${menuOpen ? '-rotate-45 -translate-y-2' : ''}`} />
        </button>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden px-5 pb-5 pt-2 flex flex-col gap-4" style={{ background: '#0f2d52' }}>
          {navLinks.map(l => (
            <a
              key={l.href}
              href={l.href}
              onClick={() => setMenuOpen(false)}
              className="text-white/90 text-sm font-medium py-1 border-b border-white/10"
            >
              {l.label}
            </a>
          ))}
          <a
            href="https://waterliftsolar.africa"
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold border border-white/20 text-white/70 hover:text-white transition-colors"
          >
            🌐 waterliftsolar.africa — More Services
          </a>
          <a
            href="#contact"
            onClick={() => setMenuOpen(false)}
            className="text-center font-bold py-3 rounded-full text-white text-sm"
            style={{ background: '#f97316' }}
          >
            Get Free Quote
          </a>
        </div>
      )}
    </header>
  )
}
