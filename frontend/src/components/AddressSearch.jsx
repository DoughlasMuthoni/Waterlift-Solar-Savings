import { useState, useRef, useCallback } from 'react'
import { MapContainer, TileLayer, Marker, useMap } from 'react-leaflet'
import L from 'leaflet'

// ── Fix Leaflet's default marker icon paths broken by Vite bundling ──────────
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

// Kenya centre
const KENYA_CENTER = [-0.023559, 37.906193]
const KENYA_ZOOM = 6

// ── Internal component: pans the map when `target` changes ──────────────────
function FlyToTarget({ target }) {
  const map = useMap()
  if (target) {
    map.flyTo(target, 16, { animate: true, duration: 1.2 })
  }
  return null
}

export default function AddressSearch({ onLocationConfirmed }) {
  const [query, setQuery] = useState('')
  const [position, setPosition] = useState(KENYA_CENTER)
  const [flyTarget, setFlyTarget] = useState(null)
  const [pinDropped, setPinDropped] = useState(false)
  const [searching, setSearching] = useState(false)
  const [searchError, setSearchError] = useState('')
  const [detectedCounty, setDetectedCounty] = useState('')
  const markerRef = useRef(null)

  // ── Nominatim geocode (OpenStreetMap, no API key) ────────────────────────
  async function handleSearch(e) {
    e.preventDefault()
    if (!query.trim()) return
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
        return
      }
      const { lat, lon } = data[0]
      const newPos = [parseFloat(lat), parseFloat(lon)]
      setPosition(newPos)
      setFlyTarget(newPos)
      setPinDropped(false) // require user to confirm by dragging
    } catch {
      setSearchError('Could not reach the map service. Check your connection.')
    } finally {
      setSearching(false)
    }
  }

  // ── Reverse-geocode to extract county name ───────────────────────────────
  async function reverseGeocode(lat, lng) {
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
        { headers: { 'Accept-Language': 'en' } }
      )
      const data = await res.json()
      const raw = data?.address?.county ?? data?.address?.state_district ?? ''
      // Strip " County" suffix → "Kiambu County" → "Kiambu"
      return raw.replace(/\s*county$/i, '').trim()
    } catch {
      return ''
    }
  }

  // ── Called when user finishes dragging the marker ────────────────────────
  const handleDragEnd = useCallback(async () => {
    const marker = markerRef.current
    if (marker) {
      const { lat, lng } = marker.getLatLng()
      setPosition([lat, lng])
      setPinDropped(true)
      const county = await reverseGeocode(lat, lng)
      setDetectedCounty(county)
    }
  }, [])

  function handleConfirm() {
    onLocationConfirmed?.(position[0], position[1], detectedCounty)
    document.getElementById('savings-section')?.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <section id="address-search" className="py-12 px-4 bg-gray-50">
      <div className="max-w-3xl mx-auto">
        <h2 className="text-2xl font-bold text-gray-800 text-center mb-2">
          Where is your property?
        </h2>
        <p className="text-center text-gray-500 mb-6">
          Search your address, then drag the pin to your exact roof.
        </p>

        {/* ── Search bar ── */}
        <form onSubmit={handleSearch} className="flex gap-2 mb-4">
          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="e.g. Ngong Road, Nairobi"
            className="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-base shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
          />
          <button
            type="submit"
            disabled={searching}
            className="bg-yellow-400 hover:bg-yellow-500 disabled:opacity-60 text-gray-900 font-semibold px-6 py-3 rounded-xl shadow transition-colors"
          >
            {searching ? 'Searching…' : 'Search'}
          </button>
        </form>

        {searchError && (
          <p className="text-red-500 text-sm text-center mb-3">{searchError}</p>
        )}

        {/* ── Map ── */}
        <div className="rounded-2xl overflow-hidden shadow-lg border border-gray-200 h-[400px]">
          <MapContainer
            center={KENYA_CENTER}
            zoom={KENYA_ZOOM}
            style={{ height: '100%', width: '100%' }}
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <FlyToTarget target={flyTarget} />
            <Marker
              position={position}
              draggable={true}
              eventHandlers={{ dragend: handleDragEnd }}
              ref={markerRef}
            />
          </MapContainer>
        </div>

        {/* ── Drag hint ── */}
        {!pinDropped && (
          <p className="text-center text-sm text-gray-400 mt-3">
            Drag the pin to your roof to confirm your location.
          </p>
        )}

        {/* ── Calculate My Savings button — visible only after pin is dragged ── */}
        {pinDropped && (
          <div className="mt-6 text-center">
            <p className="text-sm text-green-600 font-medium mb-3">
              ✓ Location confirmed{detectedCounty ? ` — ${detectedCounty} County` : ''} ({position[0].toFixed(5)}, {position[1].toFixed(5)})
            </p>
            <button
              onClick={handleConfirm}
              className="w-full sm:w-auto bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold text-lg px-10 py-4 rounded-2xl shadow-lg transition-colors"
            >
              Calculate My Savings →
            </button>
          </div>
        )}
      </div>
    </section>
  )
}
