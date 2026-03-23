import { useState } from 'react'
import Header        from './components/Header'
import Hero          from './components/Hero'
import Stats         from './components/Stats'
import HowItWorks    from './components/HowItWorks'
import UseCases      from './components/UseCases'
import WhyUs         from './components/WhyUs'
import DiscoveryWizard from './components/DiscoveryWizard'
import PricingMatrix from './components/PricingMatrix'
import Packages      from './components/Packages'
import Testimonials  from './components/Testimonials'
import MoreServices  from './components/MoreServices'
import WhatsApp      from './components/WhatsApp'
import FAQ           from './components/FAQ'
import ContactForm   from './components/ContactForm'
import Footer        from './components/Footer'

export default function App() {
  const [locationData, setLocationData]   = useState(null)
  const [showWizard, setShowWizard]       = useState(false)
  const [wizardAnswers, setWizardAnswers] = useState(null)
  const [selectedPkg, setSelectedPkg]     = useState({ tier: null, model: null })

  function handleLocationConfirmed(lat, lng, county) {
    setLocationData({ lat, lng, county })
    setWizardAnswers(null)
    setShowWizard(true)
  }

  function handleWizardComplete(answers) {
    setWizardAnswers(answers)
    setSelectedPkg({ tier: null, model: null })   // reset on new wizard run
    setShowWizard(false)
    setTimeout(() => {
      document.getElementById('pricing')?.scrollIntoView({ behavior: 'smooth' })
    }, 150)
  }

  function handleSelectPackage(tier, model) {
    setSelectedPkg({ tier, model })
  }

  // Derive recommended tier from wizard answers (same logic as PricingMatrix)
  function calcRecommendedTier(answers) {
    if (!answers) return null
    if (answers.hasBorehole || answers.monthlyBill > 18000) return 'Premium'
    if (answers.monthlyBill >= 7500) return 'Standard'
    return 'Essential'
  }

  // Pre-fill data for the contact form derived from wizard + location + package selection
  const contactPrefill = (locationData || wizardAnswers) ? {
    county:       locationData?.county        || '',
    lat:          locationData?.lat           || null,
    lng:          locationData?.lng           || null,
    propertyType: wizardAnswers?.propertyType || '',
    ownership:    wizardAnswers?.ownership    || null,
    billType:     wizardAnswers?.billType     || null,
    hasBorehole:  wizardAnswers?.hasBorehole  ?? null,
    monthlyBill:  wizardAnswers?.monthlyBill  || null,
    // Use clicked package if available, otherwise auto-calculate from wizard answers
    packageTier:  selectedPkg.tier || calcRecommendedTier(wizardAnswers),
    paymentModel: selectedPkg.model,
  } : null

  return (
    <div className="min-h-screen bg-white font-sans">
      {/* Fixed header */}
      <Header />

      {/* Hero — search bar + satellite map */}
      <Hero onLocationConfirmed={handleLocationConfirmed} />

      {/* Stats bar */}
      <Stats />

      {/* How it works */}
      <HowItWorks />

      {/* Use cases — real images */}
      <UseCases />

      {/* Why Waterlift */}
      <WhyUs />

      {/* Discovery wizard modal */}
      {showWizard && (
        <DiscoveryWizard
          location={locationData}
          onComplete={handleWizardComplete}
          onClose={() => setShowWizard(false)}
        />
      )}

      {/* 3×3 Pricing matrix — only after wizard complete */}
      {wizardAnswers && (
        <section id="pricing">
          <PricingMatrix location={locationData} answers={wizardAnswers} onSelectPackage={handleSelectPackage} />
        </section>
      )}

      {/* Always-visible packages section */}
      <Packages />

      {/* Cross-promotion — full website */}
      <MoreServices />

      {/* Testimonials */}
      <Testimonials />

      {/* FAQ */}
      <FAQ />

      {/* Contact / Lead form */}
      <ContactForm prefill={contactPrefill} />

      {/* Footer */}
      <Footer />

      {/* Floating WhatsApp button */}
      <WhatsApp />
    </div>
  )
}
