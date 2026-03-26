import { useState, useEffect } from 'react'

export default function Header() {
  const [scrolled, setScrolled] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 50)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  const navLinks = [
    { label: 'How It Works', href: '#how-it-works' },
    { label: 'Solutions',    href: '#use-cases' },
    { label: 'Pricing',      href: '#packages' },
    { label: 'Contact Us',   href: '#contact' },
  ]

  const headerStyle = {
    position: 'fixed', top: 0, left: 0, right: 0, zIndex: 9000,
    background: scrolled ? 'rgba(15,43,60,0.98)' : 'rgba(15,43,60,0.95)',
    backdropFilter: 'blur(20px)',
    WebkitBackdropFilter: 'blur(20px)',
    borderBottom: '1px solid rgba(255,255,255,0.05)',
    boxShadow: scrolled ? '0 4px 30px rgba(0,0,0,0.3)' : 'none',
    transition: 'box-shadow 0.3s ease, background 0.3s ease',
  }

  return (
    <header style={headerStyle}>
      <div className="max-w-7xl mx-auto pl-0 pr-6 flex items-center justify-between" style={{ height: 72 }}>

        {/* Logo */}
        <a href="/" className="shrink-0"
          style={{
            display: 'inline-flex', alignItems: 'center',
            background: 'white',
            borderRadius: '0 10px 10px 0',
            padding: '4px 16px 4px 0',
            boxShadow: '2px 2px 12px rgba(0,0,0,0.18)',
          }}>
          <img
            src="/images/logo.jpeg"
            alt="Waterlift Solar"
            style={{ height: 64, width: 'auto', objectFit: 'contain', display: 'block' }}
          />
        </a>

        {/* Desktop nav */}
        <nav className="hidden md:flex items-center gap-8">
          {navLinks.map(l => (
            <a
              key={l.href}
              href={l.href}
              style={{ color: '#C8D6DE', fontSize: 14, fontWeight: 500, letterSpacing: '0.2px', textDecoration: 'none', transition: 'color 0.2s' }}
              onMouseEnter={e => e.currentTarget.style.color = '#fff'}
              onMouseLeave={e => e.currentTarget.style.color = '#C8D6DE'}
            >
              {l.label}
            </a>
          ))}

          {/* External website link */}
          <span style={{ color: 'rgba(255,255,255,0.15)' }}>|</span>
          <a
            href="https://waterliftsolar.africa"
            target="_blank"
            rel="noopener noreferrer"
            style={{ color: 'rgba(200,214,222,0.6)', fontSize: 13, fontWeight: 600, textDecoration: 'none', transition: 'color 0.2s' }}
            onMouseEnter={e => e.currentTarget.style.color = '#fff'}
            onMouseLeave={e => e.currentTarget.style.color = 'rgba(200,214,222,0.6)'}
          >
            🌐 <span className="hidden lg:inline">waterliftsolar</span>.africa
          </a>

          {/* CTA button */}
          <a
            href="#hero"
            style={{
              display: 'inline-flex', alignItems: 'center', gap: 8,
              background: '#E8751A', color: '#fff',
              padding: '12px 28px', borderRadius: 50, fontWeight: 600,
              fontSize: 14, textDecoration: 'none',
              boxShadow: '0 4px 15px rgba(232,117,26,0.3)',
              transition: 'all 0.3s ease', letterSpacing: '0.2px',
            }}
            onMouseEnter={e => { e.currentTarget.style.background = '#F09030'; e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 8px 25px rgba(232,117,26,0.4)' }}
            onMouseLeave={e => { e.currentTarget.style.background = '#E8751A'; e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = '0 4px 15px rgba(232,117,26,0.3)' }}
          >
            Get My Free Solar Quote →
          </a>
        </nav>

        {/* Mobile burger */}
        <button
          className="md:hidden p-2"
          onClick={() => setMenuOpen(o => !o)}
          aria-label="Toggle menu"
          style={{ background: 'none', border: 'none', cursor: 'pointer' }}
        >
          <div style={{ width: 24, height: 2, background: '#fff', marginBottom: 5, transition: 'all 0.3s', transform: menuOpen ? 'rotate(45deg) translate(5px,5px)' : 'none' }} />
          <div style={{ width: 24, height: 2, background: '#fff', marginBottom: 5, transition: 'all 0.3s', opacity: menuOpen ? 0 : 1 }} />
          <div style={{ width: 24, height: 2, background: '#fff', transition: 'all 0.3s', transform: menuOpen ? 'rotate(-45deg) translate(5px,-5px)' : 'none' }} />
        </button>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden px-6 pb-6 pt-3 flex flex-col gap-4"
          style={{ background: 'rgba(15,43,60,0.98)', borderTop: '1px solid rgba(255,255,255,0.05)' }}>
          {navLinks.map(l => (
            <a
              key={l.href}
              href={l.href}
              onClick={() => setMenuOpen(false)}
              style={{ color: 'rgba(200,214,222,0.85)', fontSize: 14, fontWeight: 500, paddingBottom: 12, borderBottom: '1px solid rgba(255,255,255,0.08)', textDecoration: 'none' }}
            >
              {l.label}
            </a>
          ))}
          <a
            href="https://waterliftsolar.africa"
            target="_blank"
            rel="noopener noreferrer"
            style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, padding: '10px 0', border: '1px solid rgba(255,255,255,0.15)', borderRadius: 50, color: 'rgba(200,214,222,0.7)', fontSize: 13, fontWeight: 600, textDecoration: 'none' }}
          >
            🌐 waterliftsolar.africa
          </a>
          <a
            href="#hero"
            onClick={() => setMenuOpen(false)}
            style={{ textAlign: 'center', fontWeight: 700, padding: '14px 0', borderRadius: 50, color: '#fff', fontSize: 14, background: '#E8751A', textDecoration: 'none', boxShadow: '0 4px 15px rgba(232,117,26,0.3)' }}
          >
            Get My Free Solar Quote →
          </a>
        </div>
      )}
    </header>
  )
}
