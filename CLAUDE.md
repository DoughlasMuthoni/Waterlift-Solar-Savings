# Project: Waterlift Solar Savings
**Role:** Senior Full-Stack Engineer (PHP/MySQL/React)
**Stack:** PHP (Backend), MySQL (Database), React + Tailwind CSS (Frontend)
**Hosting:** XAMPP (Local) / Hostinger or Truehost (Production)
**Service Area:** National (Kenya - 47 Counties)

## UI/UX Workflow (Strict Sequence)
1. **Sticky Header:** - Logo: "Waterlift Solar Savings" (Placeholder: `https://placehold.co/200x50?text=Waterlift+Logo`).
   - Must remain fixed/sticky at the top of the viewport during all scroll actions.
2. **Hero Section:**
   - Background: `https://placehold.co/1920x1080?text=Solar+Hero+Background`.
   - Content: Headline ("Take Control of Your Power Bill") + Search Input.
3. **Location Selection (Search Trigger):**
   - User clicks 'Search' -> Full-screen Satellite Map (ESRI World Imagery).
   - Draggable Marker: User pins their exact roof.
   - Action: 'Confirm My Roof' button captures Lat/Lng and County.
4. **The Discovery Wizard (Split-Screen Modal):**
   - **Layout:** Split-screen (Left: 'ONE MONTH FREE' Promo | Right: Questions).
   - **Step 1: Ownership:** "Do you own or rent the property?" (Options: Owner, Tenant).
   - **Step 2: Property Type:** "What type of property is it?" (Options: House/Villa/Apartment, Commercial, Warehouse, Industrial).
   - **Step 3: Electricity Type:** "How do you pay for electricity?" (Options: Prepaid, Monthly Bill).
   - **Step 4: Borehole Check:** "Do you have a borehole?" (Options: Yes, No).
   - **Step 5: Monthly Spend:** "Average Monthly Spend (KES)?" (Slider/Input).
5. **3x3 Results Matrix:**
   - **Tabs:** [Rent Only] | [Rent-to-Own] | [Pay Cash].
   - **Cards:** [Essential] | [Standard] | [Premium].

## Technical Logic
- **Package Tiers:** - Essential: 1.5kW (Bills < 7.5k).
  - Standard: 3.0kW (Bills 7.5k - 18k).
  - Premium: 5.0kW+ (Bills > 18k OR Borehole = True).
- **Industrial/Warehouse Exception:** If selected, show a "Request Custom Industrial Quote" CTA instead of standard cards.
- **KPLC Rate:** 28.45 KES/kWh.
- **Yield Multipliers:** North (1.15), Nairobi (0.98), Nyeri (0.95).

## Backend Implementation
- **PHP:** Use PDO for all database logic in `/api`.
- **Database:** `leads` table must include `ownership_status` and `property_category`.
- **Transitions:** Use Framer Motion for the Wizard "Slide" effect between steps.