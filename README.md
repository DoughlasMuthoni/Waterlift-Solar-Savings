# Waterlift Solar Savings

A solar energy lead-generation website for Kenya. Guides users through a discovery wizard, recommends a solar package based on their electricity bill and property details, then captures their contact information.

**Stack:** React 19 + Vite + Tailwind CSS (frontend) · PHP PDO (backend) · MySQL (database) · XAMPP (local server)

---

## Project Structure

```
waterlift_solat_savings/
├── frontend/          # React app (Vite dev server on :5173)
│   └── src/
│       ├── App.jsx
│       └── components/
│           ├── Hero.jsx
│           ├── DiscoveryWizard.jsx
│           ├── PricingMatrix.jsx
│           ├── Packages.jsx
│           └── ContactForm.jsx
├── api/               # Public PHP endpoints (proxied from Vite)
│   ├── get_packages.php
│   └── save_lead.php
├── admin/             # PHP admin panel (/waterlift_solat_savings/admin/)
│   ├── dashboard.php
│   ├── leads.php
│   ├── packages.php
│   └── package_form.php
└── waterlift_db.sql   # Database schema + seed data
```

---

## Local Setup

1. Copy the project folder into `C:\xampp\htdocs\`
2. Start Apache and MySQL in XAMPP Control Panel
3. Import `waterlift_db.sql` in phpMyAdmin
4. `cd frontend && npm install && npm run dev`
5. Visit `http://localhost:5173` (frontend) or `http://localhost/waterlift_solat_savings/admin` (admin)

**Admin credentials:** `admin` / `waterlift@2024`

---

## Wizard & Package Recommendation Logic

The Discovery Wizard collects five inputs from the user and uses them to recommend the most appropriate solar package.

### Wizard Steps

| Step | Question | Options |
|------|----------|---------|
| 1 | Do you own or rent? | Owner / Tenant |
| 2 | Property type | House/Villa/Apartment / Commercial / Warehouse / Industrial |
| 3 | How do you pay for electricity? | Prepaid / Monthly Bill |
| 4 | Do you have a borehole? | Yes / No |
| 5 | Average monthly spend (KES) | Slider / numeric input |

### Package Tier Selection

Packages are stored in the database and sorted by `capacity_num` (kW) ascending. The wizard maps the user's inputs to a tier using this decision tree:

```
If property_type is "warehouse" OR "industrial":
  → Skip all tiers. Show "Request Custom Industrial Quote" CTA.

Else if borehole = Yes OR monthly_bill > 18,000 KES:
  → Recommend the LARGEST capacity package (Premium tier)

Else if monthly_bill >= 7,500 KES:
  → Recommend the MIDDLE capacity package (Standard tier)

Else (monthly_bill < 7,500 KES):
  → Recommend the SMALLEST capacity package (Essential tier)
```

Default tier thresholds (based on the seeded packages):

| Tier | System Size | Monthly Bill Range |
|------|------------|-------------------|
| Essential | 1.5 kW | < KES 7,500 |
| Standard | 3.0 kW | KES 7,500 – 18,000 |
| Premium | 5.0 kW+ | > KES 18,000 OR borehole |

> These thresholds are enforced in code, not hardcoded to specific package IDs. If you add or rename packages in the admin panel, the logic still works — it sorts by `capacity_num` and picks smallest / middle / largest accordingly.

### Borehole Override

When the user declares a borehole (`hasBorehole = true`), the Standard and Essential cards are hidden entirely. Only the largest-capacity (Premium) package is shown, with a notice explaining that the premium system is required to handle the borehole pump's surge current.

### Industrial / Warehouse Exception

Properties of type `warehouse` or `industrial` bypass the entire package matrix. Instead, a custom CTA card is displayed prompting the user to request a bespoke quote. No tier recommendation is made.

---

## Savings Estimate Calculation

Each package card displays an estimated monthly saving based on the user's bill and location.

### Formula

```
savings = round( (monthlyBill / KPLC_RATE) × SOLAR_YIELD × regionMultiplier × coverageFraction × KPLC_RATE )
```

Which simplifies to:

```
savings = round( monthlyBill × 0.75 × regionMultiplier × coverageFraction )
```

### Constants

| Constant | Value | Source |
|----------|-------|--------|
| `KPLC_RATE` | KES 28.45 / kWh | Kenya Power tariff |
| `SOLAR_YIELD` | 0.75 (75%) | Industry standard for Kenya |

### Region Multipliers

Solar irradiance varies across Kenya. The multiplier adjusts savings upward for sunnier regions and slightly downward for cloudier, high-altitude areas.

| Region | Counties | Multiplier |
|--------|----------|-----------|
| Northern Kenya | Turkana, Marsabit, Wajir, Mandera, Garissa, Isiolo, Samburu, Tana River | **1.15** |
| Nairobi | Nairobi | **0.98** |
| Central Highlands | Nyeri | **0.95** |
| All other counties | — | **1.00** |

### Coverage Fraction

Each package has a `coverage_fraction` field (0–1) stored in the database. It represents what proportion of the household's electricity needs that package can cover.

Example defaults:

| Package | Coverage Fraction |
|---------|-----------------|
| Essential 1.5 kW | 0.40 (40%) |
| Standard 3.0 kW | 0.70 (70%) |
| Premium 5.0 kW | 0.95 (95%) |

### Worked Example

**User:** Monthly bill KES 12,000 · Nairobi County · Standard 3.0 kW package (coverage 0.70)

```
savings = round( 12,000 × 0.75 × 0.98 × 0.70 )
        = round( 6,174 )
        = KES 6,174 / month
```

---

## Payment Models

Every package is available in three payment structures, shown via tabs on the pricing matrix:

| Tab | Description |
|-----|-------------|
| Rent Only | Fixed monthly fee. No ownership. Lifetime maintenance included. |
| Rent-to-Own | Monthly payments for 72 months, then full ownership transfers to customer. |
| Pay Cash | One-time upfront payment. Best long-term ROI. |

The `cash.roi` field on each package stores the estimated payback period (e.g. `"3.2 yrs"`), calculated during package setup in the admin panel.

---

## Data Flow: Wizard → Database

```
User completes wizard
       ↓
App.jsx stores answers in state (wizardAnswers)
       ↓
PricingMatrix renders recommended package
       ↓
User optionally clicks "Get Started" → sets selectedPkg (tier + payment model)
       ↓
ContactForm pre-filled via prefill prop:
  - county, lat, lng (from location selection)
  - propertyType, ownership, billType, hasBorehole, monthlyBill (from wizard)
  - packageTier: selectedPkg.tier OR auto-calculated fallback
  - paymentModel: selectedPkg.model OR mapped from interest dropdown
       ↓
POST /api/save_lead.php
       ↓
MySQL: leads table (includes all wizard fields)
```

The fallback calculation in `App.jsx` ensures `packageTier` is always saved even if the user skips the pricing section:

```js
function calcRecommendedTier(answers) {
  if (!answers) return null
  if (answers.hasBorehole || answers.monthlyBill > 18000) return 'Premium'
  if (answers.monthlyBill >= 7500) return 'Standard'
  return 'Essential'
}
```

---

## Admin Panel

Access at `/waterlift_solat_savings/admin/`

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/dashboard.php` | Stats overview + recent leads |
| All Leads | `/leads.php` | Filterable lead table, status management |
| Lead Detail | `/lead.php?id=N` | Full lead info, notes, status update |
| Packages | `/packages.php` | Add / edit / toggle / delete packages |
| Export CSV | `/api/export.php` | Download all leads as CSV |

Packages edited in the admin panel are immediately reflected on the live frontend — no rebuild required.
