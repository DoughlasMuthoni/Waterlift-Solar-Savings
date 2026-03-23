import { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

const WA_NUMBER = '254768117070'
const WA_MESSAGE = encodeURIComponent('Hello Waterlift Solar! I would like to get a free solar quote for my property.')
const WA_URL = `https://wa.me/${WA_NUMBER}?text=${WA_MESSAGE}`

export default function WhatsApp() {
  const [showTooltip, setShowTooltip] = useState(false)
  const [pulse, setPulse] = useState(true)

  return (
    <div className="fixed bottom-6 right-6 z-[10000] flex flex-col items-end gap-3">
      {/* Tooltip / chat bubble */}
      <AnimatePresence>
        {showTooltip && (
          <motion.div
            initial={{ opacity: 0, scale: 0.8, y: 10 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.8, y: 10 }}
            transition={{ duration: 0.2 }}
            className="flex items-start gap-3 bg-white rounded-2xl shadow-2xl px-4 py-3 max-w-[220px] border border-gray-100"
          >
            <div className="w-8 h-8 rounded-full shrink-0 flex items-center justify-center text-white text-sm font-bold"
              style={{ background: '#25D366' }}>W</div>
            <div>
              <p className="text-xs font-bold text-gray-800 mb-0.5">Waterlift Solar</p>
              <p className="text-xs text-gray-500 leading-snug">👋 Hi! Chat with us on WhatsApp — we reply fast.</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Main button */}
      <motion.a
        href={WA_URL}
        target="_blank"
        rel="noopener noreferrer"
        onMouseEnter={() => { setShowTooltip(true); setPulse(false) }}
        onMouseLeave={() => setShowTooltip(false)}
        whileHover={{ scale: 1.1 }}
        whileTap={{ scale: 0.95 }}
        className="relative w-16 h-16 rounded-full flex items-center justify-center shadow-2xl"
        style={{ background: '#25D366' }}
        aria-label="Chat on WhatsApp"
      >
        {/* Pulse ring */}
        {pulse && (
          <span className="absolute inset-0 rounded-full animate-ping opacity-40" style={{ background: '#25D366' }} />
        )}

        {/* WhatsApp SVG icon */}
        <svg viewBox="0 0 32 32" className="w-9 h-9 fill-white" xmlns="http://www.w3.org/2000/svg">
          <path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm0 29.333a13.28 13.28 0 01-6.773-1.855l-.485-.29-5.04 1.19 1.217-4.917-.318-.506A13.266 13.266 0 012.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.27-9.927c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/>
        </svg>
      </motion.a>
    </div>
  )
}
