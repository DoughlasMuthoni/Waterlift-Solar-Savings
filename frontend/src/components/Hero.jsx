import { useState, useRef, useCallback, useEffect } from 'react'
import { MapContainer, TileLayer, Marker, useMap } from 'react-leaflet'
import L from 'leaflet'

// ── Fix Leaflet's default marker icon paths broken by Vite bundling ──────────
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

const NAIROBI = [-1.286389, 36.817223]

// ── Forces Leaflet to recalculate its container size after mount ─────────────
function InvalidateSize() {
  const map = useMap()
  useEffect(() => { map.invalidateSize() }, [map])
  return null
}

// ── Smoothly flies the map to `target` at the given zoom ─────────────────────
function FlyToTarget({ target, zoom }) {
  const map = useMap()
  useEffect(() => {
    if (target) map.flyTo(target, zoom, { animate: true, duration: 1.5 })
  }, [target, zoom, map])
  return null
}

export default function Hero({ onLocationConfirmed }) {
  const [query, setQuery]                   = useState('')
  const [suggestions, setSuggestions]       = useState([])
  const [showSuggestions, setShowSuggestions] = useState(false)
  const [mapVisible, setMapVisible]         = useState(false)
  const [position, setPosition]             = useState(NAIROBI)
  const [flyTarget, setFlyTarget]           = useState(null)
  const [detectedCounty, setDetectedCounty] = useState('')
  const [searching, setSearching]           = useState(false)
  const [searchError, setSearchError]       = useState('')
  const markerRef  = useRef(null)
  const debounceRef = useRef(null)

  // ── Fetch suggestions as user types (debounced 300ms) ────────────────────
  function handleQueryChange(e) {
    const val = e.target.value
    setQuery(val)
    setSearchError('')
    clearTimeout(debounceRef.current)
    if (!val.trim()) {
      setSuggestions([])
      setShowSuggestions(false)
      return
    }
    debounceRef.current = setTimeout(async () => {
      try {
        const res = await fetch(
          `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(val)}&countrycodes=ke&format=json&limit=6&addressdetails=1`,
          { headers: { 'Accept-Language': 'en' } }
        )
        const data = await res.json()
        setSuggestions(data)
        setShowSuggestions(data.length > 0)
      } catch {
        // silently ignore suggestion errors
      }
    }, 300)
  }

  // ── User picks a suggestion → open map immediately ───────────────────────
  async function handleSuggestionClick(item) {
    setQuery(item.display_name)
    setSuggestions([])
    setShowSuggestions(false)
    await openMap(parseFloat(item.lat), parseFloat(item.lon))
  }

  // ── Form submit (SEARCH button or Enter) ─────────────────────────────────
  async function handleSearch(e) {
    e.preventDefault()
    if (!query.trim()) return
    setSuggestions([])
    setShowSuggestions(false)
    setSearching(true)
    setSearchError('')
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&countrycodes=ke&format=json&limit=1`,
        { headers: { 'Accept-Language': 'en' } }
      )
      const data = await res.json()
      if (!data.length) {
        setSearchError('Address not found. Try a town name or landmark.')
        setSearching(false)
        return
      }
      await openMap(parseFloat(data[0].lat), parseFloat(data[0].lon))
    } catch {
      setSearchError('Could not reach the map service. Check your connection.')
    } finally {
      setSearching(false)
    }
  }

  // ── Shared: resolve county and show map ──────────────────────────────────
  async function openMap(lat, lon) {
    const newPos = [lat, lon]
    setPosition(newPos)
    setFlyTarget(newPos)
    const county = await reverseGeocode(lat, lon)
    setDetectedCounty(county)
    setMapVisible(true)
  }

  // ── Nominatim reverse-geocode → county name ───────────────────────────────
  async function reverseGeocode(lat, lng) {
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
        { headers: { 'Accept-Language': 'en' } }
      )
      const data = await res.json()
      const raw = data?.address?.county ?? data?.address?.state_district ?? ''
      return raw.replace(/\s*county$/i, '').trim()
    } catch {
      return ''
    }
  }

  // ── Marker drag end → update position + county ───────────────────────────
  const handleDragEnd = useCallback(async () => {
    const marker = markerRef.current
    if (marker) {
      const { lat, lng } = marker.getLatLng()
      setPosition([lat, lng])
      const county = await reverseGeocode(lat, lng)
      setDetectedCounty(county)
    }
  }, [])

  // ── Confirm roof location → pass data up, scroll to calculator ───────────
  function handleConfirm() {
    onLocationConfirmed?.(position[0], position[1], detectedCounty)
    setMapVisible(false)
    setTimeout(() => {
      document.getElementById('solar-calculator')?.scrollIntoView({ behavior: 'smooth' })
    }, 100)
  }

  return (
    <>
      {/* ════════════════════════════════════════════════════════════════════
          HERO SECTION
      ════════════════════════════════════════════════════════════════════ */}
      <section
        id="hero"
        className="relative min-h-screen flex flex-col bg-cover bg-center"
        style={{ backgroundImage: "url('/images/solar-panels.jpg')" }}
      >
        {/* Gradient overlay — navy at bottom for readability */}
        <div className="absolute inset-0" style={{ background: 'linear-gradient(to bottom, rgba(15,45,82,0.72) 0%, rgba(15,45,82,0.55) 50%, rgba(15,45,82,0.80) 100%)' }} />

        {/* Centered content */}
        <div className="relative z-10 flex-1 flex flex-col items-center justify-center px-4 pb-16 pt-28">

          {/* Tag pill */}
          <span className="inline-block text-xs font-bold tracking-widest uppercase px-4 py-1.5 rounded-full mb-6" style={{ background: '#f97316', color: 'white' }}>
            Trusted by 105+ Homes, Schools &amp; Farms
          </span>

          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white text-center leading-tight max-w-3xl drop-shadow-lg">
            Stop Overpaying for<br />
            <span style={{ color: '#f59e0b' }}>Unreliable Power</span>
          </h1>
          <p className="mt-5 text-lg sm:text-xl text-white/80 font-medium text-center max-w-xl">
            Waterlift Solar installs, maintains, and guarantees your solar system — with rent, rent-to-own, or cash options. You save up to 75% on your power bill from month one.
          </p>

          {/* 3-step indicators */}
          <div className="flex flex-wrap justify-center gap-4 mt-6 mb-10">
            {[
              { num: '1', text: 'Enter Your Location' },
              { num: '2', text: 'Get Your Personalised Quote' },
              { num: '3', text: 'Start Saving' },
            ].map(s => (
              <span key={s.num} className="flex items-center gap-2 text-xs font-semibold text-white/90 bg-white/10 backdrop-blur-sm border border-white/20 px-3 py-1.5 rounded-full">
                <span className="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0" style={{ background: '#f97316' }}>{s.num}</span>
                {s.text}
              </span>
            ))}
          </div>

          {/* Search bar + autocomplete dropdown */}
          <div className="relative w-full max-w-2xl">
            <form
              onSubmit={handleSearch}
              className="flex rounded-full overflow-hidden shadow-2xl border-2"
              style={{ borderColor: '#f97316' }}
            >
              <input
                type="text"
                value={query}
                onChange={handleQueryChange}
                onBlur={() => setTimeout(() => setShowSuggestions(false), 150)}
                onFocus={() => suggestions.length > 0 && setShowSuggestions(true)}
                placeholder="Enter your address or landmark in Kenya…"
                className="flex-1 px-6 py-4 text-base text-gray-800 bg-white focus:outline-none"
                autoComplete="off"
              />
              <button
                type="submit"
                disabled={searching}
                className="font-bold px-8 py-4 text-sm tracking-widest transition-colors uppercase disabled:opacity-60"
                style={{ background: '#f97316', color: 'white' }}
              >
                {searching ? '…' : 'Search'}
              </button>
            </form>

            {/* Autocomplete dropdown */}
            {showSuggestions && (
              <ul className="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl overflow-hidden z-20">
                {suggestions.map((s, i) => (
                  <li
                    key={i}
                    onMouseDown={() => handleSuggestionClick(s)}
                    className="flex items-start gap-3 px-5 py-3 cursor-pointer border-b border-gray-100 last:border-0 hover:bg-orange-50"
                  >
                    <span className="mt-0.5 shrink-0" style={{ color: '#f97316' }}>📍</span>
                    <span className="text-sm text-gray-700 leading-snug">{s.display_name}</span>
                  </li>
                ))}
              </ul>
            )}
          </div>

          {searchError && (
            <p className="mt-3 text-red-300 text-sm text-center">{searchError}</p>
          )}

          <p className="mt-4 text-white/50 text-xs italic">
            Every month you wait, you are paying Kenya Power instead of yourself.
          </p>
        </div>

        {/* Scroll cue */}
        <div className="relative z-10 flex justify-center pb-8 animate-bounce">
          <svg className="w-6 h-6 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </section>

      {/* ════════════════════════════════════════════════════════════════════
          FULL-SCREEN SATELLITE MAP OVERLAY
      ════════════════════════════════════════════════════════════════════ */}
      {mapVisible && (
        <div className="fixed inset-0" style={{ zIndex: 9999 }}>
          {/* Back button */}
          <button
            onClick={() => setMapVisible(false)}
            className="absolute top-4 left-4 bg-white/90 hover:bg-white text-gray-800 font-semibold text-sm px-4 py-2 rounded-full shadow-lg transition-colors"
            style={{ zIndex: 10001 }}
          >
            ← Back
          </button>

          {/* County badge */}
          {detectedCounty && (
            <div
              className="absolute top-4 left-1/2 -translate-x-1/2 bg-white/90 text-gray-800 text-sm font-medium px-4 py-2 rounded-full shadow-lg whitespace-nowrap"
              style={{ zIndex: 10001 }}
            >
              📍 {detectedCounty} County
            </div>
          )}

          {/* Leaflet map — ESRI World Imagery satellite */}
          <MapContainer
            center={position}
            zoom={17}
            style={{ height: '100vh', width: '100vw' }}
            zoomControl={true}
          >
            <InvalidateSize />
            <TileLayer
              attribution="Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community"
              url="https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
              maxNativeZoom={19}
              maxZoom={21}
            />
            <FlyToTarget target={flyTarget} zoom={17} />
            <Marker
              position={position}
              draggable={true}
              eventHandlers={{ dragend: handleDragEnd }}
              ref={markerRef}
            />
          </MapContainer>

          {/* Floating Confirm My Roof button */}
          <div
            className="absolute bottom-8 left-1/2 -translate-x-1/2 text-center"
            style={{ zIndex: 10001 }}
          >
            <button
              onClick={handleConfirm}
              className="font-bold text-base px-10 py-4 rounded-full shadow-2xl transition-opacity tracking-wide text-white hover:opacity-90"
              style={{ background: '#f97316' }}
            >
              Confirm My Roof ✓
            </button>
            <p className="text-white/80 text-xs text-center mt-2 drop-shadow">
              Drag the pin to your exact roof, then confirm.
            </p>
          </div>
        </div>
      )}
    </>
  )
}
