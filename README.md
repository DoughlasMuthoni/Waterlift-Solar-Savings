# Waterlift Solar Savings

A solar lead-generation web application for **Waterlift Solar** — serving all 47 counties in Kenya.

**Stack:** React 19 + Vite + Tailwind CSS (frontend) · PHP 8 + PDO (backend) · MySQL (database) · XAMPP (local) / Hostinger or Truehost (production)

> Full service website: [waterliftsolar.africa](https://waterliftsolar.africa) — borehole drilling, hydrogeological surveys, test pumping, water storage towers, and an online shop.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Directory Structure](#2-directory-structure)
3. [Local Setup](#3-local-setup)
4. [User Journey](#4-user-journey)
5. [The Discovery Wizard](#5-the-discovery-wizard)
6. [Package Recommendation Engine](#6-package-recommendation-engine)
   - [How Packages Are Stored](#61-how-packages-are-stored)
   - [The Recommendation Algorithm](#62-the-recommendation-algorithm)
   - [Savings Estimate Calculation](#63-savings-estimate-calculation)
   - [Special Cases](#64-special-cases)
7. [What Happens When the Admin Adds a New Package](#7-what-happens-when-the-admin-adds-a-new-package)
8. [Data Flow: Wizard → Contact Form → Database](#8-data-flow-wizard--contact-form--database)
9. [Payment Models](#9-payment-models)
10. [Admin Dashboard](#10-admin-dashboard)
11. [Database Schema](#11-database-schema)
12. [API Endpoints](#12-api-endpoints)
13. [Frontend Components](#13-frontend-components)
14. [Deployment](#14-deployment)

---

## 1. Project Overview

Users visit the site, pin their exact roof on a satellite map, answer 5 quick questions, and are immediately shown a **personalised solar package recommendation** with estimated monthly savings. They then submit a contact form — automatically pre-filled with their wizard answers — and the sales team follows up within 24 hours.

Admins manage everything through a responsive PHP dashboard: leads (CRM), solar packages, use-case cards, and customer testimonials.

---

## 2. Directory Structure

```
waterlift_solat_savings/
├── frontend/                    # React + Vite SPA
│   ├── src/
│   │   ├── App.jsx              # Root component — state, data flow
│   │   └── components/
│   │       ├── Header.jsx           # Sticky nav + link to waterliftsolar.africa
│   │       ├── Hero.jsx             # Search bar + satellite map trigger
│   │       ├── Stats.jsx            # KPI bar
│   │       ├── HowItWorks.jsx
│   │       ├── UseCases.jsx         # "What We Power" cards (fetched from DB)
│   │       ├── WhyUs.jsx
│   │       ├── DiscoveryWizard.jsx  # 5-step split-screen modal
│   │       ├── PricingMatrix.jsx    # 3×3 results grid (shown post-wizard)
│   │       ├── Packages.jsx         # Always-visible packages section (from DB)
│   │       ├── MoreServices.jsx     # Cross-promo banner → waterliftsolar.africa
│   │       ├── Testimonials.jsx     # Customer reviews (fetched from DB)
│   │       ├── FAQ.jsx
│   │       ├── ContactForm.jsx      # Lead capture, pre-filled from wizard
│   │       ├── AddressSearch.jsx    # ESRI World Imagery satellite map
│   │       ├── SolarSavingsSimulator.jsx
│   │       └── WhatsApp.jsx         # Floating WhatsApp button
│   └── vite.config.js           # Proxy: /api/* → http://localhost (XAMPP)
│
├── api/                         # Public PHP endpoints (called by React)
│   ├── db_connect.php
│   ├── save_lead.php            # POST — saves wizard + contact form data
│   ├── get_packages.php         # GET  — active packages as JSON
│   ├── get_use_cases.php        # GET  — active use cases as JSON
│   └── get_testimonials.php     # GET  — approved testimonials as JSON
│
├── admin/                       # Protected PHP admin dashboard
│   ├── login.php
│   ├── dashboard.php            # Stats overview + recent leads
│   ├── leads.php                # Filterable lead table
│   ├── lead.php                 # Individual lead detail + CRM notes
│   ├── packages.php             # Package catalogue (card grid)
│   ├── package_form.php         # Add / edit package (live preview)
│   ├── use_cases.php            # Use-case card management
│   ├── use_case_form.php        # Add / edit use case
│   ├── testimonials.php         # Approve, feature, delete reviews
│   ├── includes/
│   │   ├── auth.php             # Session guard
│   │   ├── db.php               # PDO connection
│   │   └── layout.php           # Responsive sidebar + mobile bottom nav
│   └── api/
│       ├── update_lead.php
│       ├── toggle_package.php
│       ├── delete_package.php
│       ├── toggle_use_case.php
│       ├── delete_use_case.php
│       ├── toggle_testimonial.php   # approve / unapprove
│       ├── delete_testimonial.php
│       └── export.php               # CSV download
│
├── waterlift_db.sql             # Full schema + seed data
└── README.md                    # This file
```

---

## 3. Local Setup

**Prerequisites:** XAMPP (Apache + MySQL running) · Node.js 18+

```bash
# 1. Place project in XAMPP htdocs
#    e.g. C:\xampp\htdocs\waterlift_solat_savings\

# 2. Import the database
#    phpMyAdmin → Import → choose waterlift_db.sql → Go

# 3. Install frontend dependencies
cd frontend
npm install

# 4. Start the dev server
npm run dev
#    Frontend: http://localhost:5173
#    API calls to /api/* are proxied to http://localhost (XAMPP on port 80)

# 5. Access admin dashboard
#    http://localhost/waterlift_solat_savings/admin/
#    Password: waterlift@2024
```

---

## 4. User Journey

```
Homepage (Hero)
    │
    ├─ User types location, clicks Search
    │
    ▼
Full-screen ESRI Satellite Map
    │
    ├─ User drags marker to their exact roof
    ├─ Clicks "Confirm My Roof"
    │   → captures: lat, lng, county
    │
    ▼
Discovery Wizard (5-step modal, Framer Motion slide transitions)
    │
    ├─ Step 1: Ownership (Owner / Tenant)
    ├─ Step 2: Property Type
    ├─ Step 3: Electricity payment type
    ├─ Step 4: Borehole (Yes / No)
    └─ Step 5: Monthly bill (KES slider)
    │
    ▼
3×3 Pricing Matrix (auto-scrolls into view)
    │   ← packages fetched live from DB via /api/get_packages.php
    │   ← recommended package highlighted based on wizard answers
    │   ← three tabs: Rent Only | Rent-to-Own | Pay Cash
    │
    ├─ Optional: user clicks "Get Started" on a specific card
    │   → sets selectedPackage { tier, paymentModel }
    │
    ▼
Contact Form (pre-filled with wizard answers)
    │
    ├─ User adds name + phone (everything else already filled)
    ├─ Submits → POST /api/save_lead.php
    │
    ▼
Success screen → option to leave a testimonial
    │
    ▼
Lead appears in Admin Dashboard for follow-up
```

---

## 5. The Discovery Wizard

The wizard is a split-screen modal: the left panel shows the "ONE MONTH FREE" promotion; the right panel advances through 5 questions with a slide animation between each step.

| Step | Question | Answer options | Saved to DB as |
|------|----------|----------------|----------------|
| 1 | Do you own or rent? | Owner · Tenant | `ownership_status` |
| 2 | What type of property? | House/Villa/Apartment · Commercial · Warehouse · Industrial | `property_category` |
| 3 | How do you pay for electricity? | Prepaid · Monthly Bill | `bill_type` |
| 4 | Do you have a borehole? | Yes · No | `has_borehole` (1/0) |
| 5 | Average monthly spend (KES) | Slider + numeric input | `monthly_bill` |

When the user clicks **Finish**, the `onComplete(answers)` callback fires. `App.jsx` stores the answers in React state, closes the wizard, and scrolls to the Pricing Matrix.

---

## 6. Package Recommendation Engine

### 6.1 How Packages Are Stored

All solar packages live in the `packages` MySQL table, managed entirely from the admin dashboard. The fields that drive recommendation are:

| DB Field | Type | Purpose |
|----------|------|---------|
| `capacity_num` | `DECIMAL(8,2)` | **The only field used for tier sorting.** Numeric kW value. |
| `coverage_fraction` | `DECIMAL(4,2)` | 0–1 fraction of the bill this package offsets (used in savings formula) |
| `is_active` | `TINYINT(1)` | Only `1` (active) packages are served to the frontend |
| `sort_order` | `TINYINT UNSIGNED` | Controls visual display order — does **not** affect recommendation |

The API endpoint `GET /api/get_packages.php` returns all active packages sorted by `sort_order ASC, capacity_num ASC`, with a 60-second cache.

### 6.2 The Recommendation Algorithm

The algorithm is **fully dynamic** — it works with any number of packages the admin creates, because it sorts by `capacity_num` and picks by position (smallest / middle / largest), never by a hardcoded name or ID.

#### Decision tree

```
Step 1 — Fetch all active packages → sort ascending by capacity_num
         Result: [smallest .............. largest]

Step 2 — Apply override checks first:

  IF property_type IS 'industrial' OR 'warehouse'
      → Skip all packages entirely
      → Show "Request Custom Industrial Quote" CTA
      → STOP

  IF hasBorehole = true  OR  monthlyBill > 18,000 KES
      → Recommend LARGEST package  (index: sorted.length - 1)

  ELSE IF monthlyBill >= 7,500 KES
      → Recommend MIDDLE package   (index: Math.floor(sorted.length / 2))

  ELSE  (monthlyBill < 7,500 KES)
      → Recommend SMALLEST package (index: 0)
```

#### In code (`PricingMatrix.jsx`)

```js
function getRecommendedId(packages, bill, hasBorehole) {
  if (!packages.length) return null

  // Sort ascending by numeric capacity
  const sorted = [...packages].sort((a, b) => a.capacityNum - b.capacityNum)

  // Borehole or high bill → largest package
  if (hasBorehole || bill > 18000)
    return sorted[sorted.length - 1].id

  // Mid-range bill → middle package
  if (bill >= 7500 && sorted.length >= 2)
    return sorted[Math.floor(sorted.length / 2)].id

  // Low bill → smallest package
  return sorted[0].id
}
```

The same logic runs as a fallback in `App.jsx` to populate `packageTier` when the user submits the contact form without clicking a CTA:

```js
function calcRecommendedTier(answers) {
  if (!answers) return null
  if (answers.hasBorehole || answers.monthlyBill > 18000) return 'Premium'
  if (answers.monthlyBill >= 7500) return 'Standard'
  return 'Essential'
}
```

#### Default thresholds (based on the three seeded packages)

| Tier label | System size | Bill range | Borehole |
|------------|------------|------------|---------|
| Essential | 1.5 kW | < KES 7,500 | No |
| Standard | 3.0 kW | KES 7,500 – 18,000 | No |
| Premium | 5.0 kW+ | > KES 18,000 | Yes (any bill) |

### 6.3 Savings Estimate Calculation

Each package card shows an estimated monthly saving calculated from the user's bill and location.

#### Formula

```
savings = monthlyBill × SOLAR_YIELD × regionMultiplier × coverageFraction
```

Where `SOLAR_YIELD = 0.75` (75% — industry standard efficiency factor for Kenya).

#### Region multipliers

Solar irradiance varies across Kenya. The multiplier is derived from the county captured on the satellite map.

| Region | Multiplier | Counties |
|--------|-----------|---------|
| Northern Kenya | **1.15** | Turkana, Marsabit, Mandera, Wajir, Garissa, Isiolo, Samburu, Tana River |
| Nairobi | **0.98** | Nairobi |
| Central Highlands | **0.95** | Nyeri, Nyandarua, Kirinyaga |
| All other counties | **1.00** | Default |

#### Coverage fraction

Set per package by the admin. Represents the share of the electricity bill that system can offset.

| Default package | Coverage fraction |
|----------------|------------------|
| Essential 1.5 kW | 0.45 (45%) |
| Standard 3.0 kW | 0.65 (65%) |
| Premium 5.0 kW+ | 0.75 (75%) |

#### Worked example

**User:** KES 12,000/month bill · Nairobi county · Standard package (coverage 0.65)

```
savings = 12,000 × 0.75 × 0.98 × 0.65
        = 12,000 × 0.47775
        ≈ KES 5,733 / month
```

### 6.4 Special Cases

| Condition | What happens |
|-----------|-------------|
| `property_category` = `industrial` or `warehouse` | Entire package matrix is skipped. A "Request Custom Industrial Quote" CTA is shown. No savings estimate is calculated. |
| `has_borehole = true` | Algorithm jumps directly to the largest package. Standard and Essential are de-emphasised. |
| Admin deactivates all packages | Frontend shows loading skeleton, then gracefully empty — no crash. |
| Only 1 active package | All three conditions resolve to `sorted[0]`. The single package is always shown as recommended. |
| Only 2 active packages | `Math.floor(2/2) = 1` → the larger package becomes the mid-tier recommendation. |

---

## 7. What Happens When the Admin Adds a New Package

This is the full end-to-end flow from admin input to what the user sees.

### Step 1 — Admin fills in the package form

Go to `/admin/packages.php` → click **+ Add New Package** → the form at `/admin/package_form.php` opens.

The admin fills in:

| Field | Example | Notes |
|-------|---------|-------|
| Package name | "Ultra Premium" | Display name |
| Capacity (label) | "10 kW" | Human-readable string |
| **Capacity (kW) — numeric** | `10.0` | **This is the only value the recommendation algorithm uses** |
| Coverage fraction | `0.80` | 0–1, used in savings calculation |
| Rent monthly (KES) | `18,000` | |
| Rent-to-Own monthly | `25,000` | |
| RTO duration | `72` months | |
| Cash price | `900,000` | |
| Cash ROI | `"2.6 yrs"` | |
| Features | Up to 8 bullet points | Stored as JSON array |
| Badge | "Enterprise Ready" | Optional highlight label |
| Gradient theme | Pick from 8 presets or custom | Background for the card header |
| Sort order | `4` | Controls display position |
| Active | ✓ | Only active packages appear on the site |

A **live preview panel** on the right side of the form updates in real time as the admin types.

### Step 2 — Package saved to the database

On submit, `package_form.php` runs:

```sql
INSERT INTO packages (name, capacity, capacity_num, coverage_fraction, ...)
VALUES ('Ultra Premium', '10 kW', 10.0, 0.80, ...)
```

### Step 3 — Frontend picks it up automatically (within 60 seconds)

`GET /api/get_packages.php` queries:

```sql
SELECT * FROM packages
WHERE is_active = 1
ORDER BY sort_order ASC, capacity_num ASC
```

This endpoint has a 60-second `Cache-Control` header. After expiry, the next page load returns the updated package list. Both `PricingMatrix.jsx` and `Packages.jsx` fetch this on mount — no rebuild, no redeployment needed.

### Step 4 — Recommendation re-ranks automatically

Because the algorithm ranks by `capacity_num` position, adding a higher-capacity package automatically makes it the new "largest" (premium/borehole) recommendation.

**Example — before and after adding a 10 kW package:**

```
Before (3 packages):
  Sorted:  [1.5kW,  3kW,   5kW]
  Index:   [  0,     1,     2 ]

  Bill < 7,500       → index 0 = 1.5 kW  ✓ Essential
  Bill 7,500–18,000  → index 1 = 3.0 kW  ✓ Standard
  Borehole / >18,000 → index 2 = 5.0 kW  ✓ Premium

After (4 packages, 10 kW added):
  Sorted:  [1.5kW,  3kW,   5kW,   10kW]
  Index:   [  0,     1,     2,      3  ]

  Bill < 7,500       → index 0 = 1.5 kW  ✓ (unchanged)
  Bill 7,500–18,000  → index 2 = 5.0 kW  ↑ moved up
  Borehole / >18,000 → index 3 = 10 kW   ✓ new largest
```

**No code changes required.** The recommendation logic adapts automatically.

### Step 5 — What the admin should keep in mind

| Goal | How to achieve it |
|------|------------------|
| New entry-level package | Set `capacity_num` lower than all existing packages |
| New mid-range package | Set `capacity_num` between the smallest and largest |
| New top-tier / borehole package | Set `capacity_num` higher than all existing packages |
| Hide a package temporarily | Toggle it inactive — it disappears from the frontend within 60 seconds |
| Control card display order | Adjust `sort_order` — this does **not** affect recommendations |

---

## 8. Data Flow: Wizard → Contact Form → Database

```
User completes Discovery Wizard
        │
        ▼
App.jsx stores answers in state:
  wizardAnswers = { ownership, propertyType, billType, hasBorehole, monthlyBill }
  locationData  = { lat, lng, county }
        │
        ▼
PricingMatrix renders with recommended package highlighted
  ← getRecommendedId(packages, monthlyBill, hasBorehole)
        │
        ├── Optional: user clicks "Get Started" on a card
        │     → setSelectedPkg({ tier, model })
        │
        ▼
contactPrefill object computed in App.jsx:
  {
    county, lat, lng,                      ← from locationData
    propertyType, ownership, billType,     ← from wizardAnswers
    hasBorehole, monthlyBill,
    packageTier:  selectedPkg.tier         ← from CTA click
               || calcRecommendedTier(wizardAnswers),  ← fallback
    paymentModel: selectedPkg.model        ← from CTA click (or null)
  }
        │
        ▼
ContactForm receives prefill prop
  → useEffect populates county, propertyType, interest dropdown
  → Green "✓ from assessment" badge shown on pre-filled fields
        │
        ▼
User adds name + phone → submits form
        │
        ▼
POST /api/save_lead.php  (JSON payload includes all wizard fields)
        │
        ▼
MySQL: leads table row created
  ownership_status, property_category, bill_type, has_borehole,
  monthly_bill, package_tier, payment_model, lat, lng, county,
  name, phone, email, message, source='wizard_then_form'
        │
        ▼
Success screen shown → customer prompted to leave a testimonial
        │
        ▼
Testimonial submitted → stored with is_approved=0 (pending review)
        │
        ▼
Admin approves in /admin/testimonials.php
        │
        ▼
Approved testimonial appears on the live Testimonials section
```

---

## 9. Payment Models

Every package is available in three structures, shown as tabs on the Pricing Matrix:

| Tab | Description | DB fields used |
|-----|-------------|---------------|
| **Rent Only** | Fixed monthly fee, no ownership transfer. Lifetime maintenance included. | `rent_monthly` |
| **Rent-to-Own** | Monthly payments over 72 months, then ownership transfers to the customer. | `rto_monthly`, `rto_months` |
| **Pay Cash** | One-time upfront payment. Best long-term ROI. | `cash_price`, `cash_roi` |

The `cash_roi` field (e.g. `"3.2 yrs"`) is entered by the admin when creating the package. It represents estimated payback period based on the customer's average bill.

---

## 10. Admin Dashboard

Access: `http://localhost/waterlift_solat_savings/admin/`
Password: `waterlift@2024`

The dashboard is fully responsive: fixed sidebar on desktop (≥1024px), slide-in drawer + bottom navigation bar on mobile.

### Pages

| Page | URL | What it does |
|------|-----|-------------|
| Dashboard | `dashboard.php` | Stats: total leads, new today, conversion rate, county map |
| Leads | `leads.php` | Filterable, searchable lead list with status chips; collapsible advanced filters |
| Lead Detail | `lead.php?id=N` | Full wizard answers, satellite pin, status update, CRM notes |
| Packages | `packages.php` | Card grid — activate/deactivate, edit, delete |
| Add/Edit Package | `package_form.php` | Full form with live card preview; 8 gradient presets |
| Use Cases | `use_cases.php` | "What We Power" cards — add, edit, activate/deactivate, delete |
| Add/Edit Use Case | `use_case_form.php` | Title, tag, description, stat label, image URL, sort order |
| Testimonials | `testimonials.php` | Review queue (pending / approved) — approve, feature, delete |
| Export CSV | `api/export.php` | Downloads all leads as a CSV file |

### Testimonial moderation flow

1. Customer submits a testimonial after their contact form is accepted
2. Testimonial is saved with `is_approved = 0` (not visible on site)
3. Admin sees it in the **Pending** tab at `testimonials.php`
4. Admin clicks **Approve** → `is_approved` set to `1`
5. Testimonial is now returned by `GET /api/get_testimonials.php` and appears on the live site
6. Admin can also toggle **Featured** to give it priority placement in the carousel

---

## 11. Database Schema

```sql
-- Lead capture (wizard + contact form)
leads (
  id, name, phone, email,
  county, lat, lng, address,
  ownership_status ENUM('owner','tenant'),
  property_category ENUM('residential','commercial','warehouse','industrial'),
  bill_type ENUM('prepaid','monthly'),
  has_borehole TINYINT(1),
  monthly_bill DECIMAL(10,2),
  package_tier ENUM('Essential','Standard','Premium'),
  payment_model ENUM('Rent Only','Rent-to-Own','Pay Cash'),
  source VARCHAR(30),   -- 'contact_form' | 'wizard_then_form' | 'whatsapp'
  status ENUM('new','contacted','qualified','converted','lost'),
  notes TEXT,
  created_at, updated_at
)

-- Admin-managed solar packages
packages (
  id, name, capacity, capacity_num,
  icon, tagline, badge, gradient, accent_color,
  coverage_fraction,
  features JSON,
  rent_monthly, rto_monthly, rto_months,
  cash_price, cash_roi, savings_label,
  is_active, sort_order,
  created_at, updated_at
)

-- "What We Power" use-case cards
use_cases (
  id, title, tag, description, stat_label, image_url,
  is_active, sort_order,
  created_at, updated_at
)

-- Customer testimonials (admin approval required)
testimonials (
  id, name, location, package_label,
  stars TINYINT,
  message TEXT,
  avatar_url,
  is_approved TINYINT(1),   -- 0 = pending, 1 = live on site
  is_featured TINYINT(1),
  sort_order,
  created_at, updated_at
)

-- Future CRM tagging
lead_tags (lead_id, tag, created_at)
```

---

## 12. API Endpoints

All under `/api/` — proxied through Vite in dev; served directly by XAMPP in production.

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/get_packages.php` | Active packages as JSON (cached 60s) |
| `GET` | `/api/get_use_cases.php` | Active use cases as JSON |
| `GET` | `/api/get_testimonials.php` | Approved testimonials as JSON |
| `POST` | `/api/save_lead.php` | Save a new lead |

### `POST /api/save_lead.php` — expected payload

```json
{
  "name": "John Kamau",
  "phone": "+254 712 345 678",
  "email": "john@example.com",
  "county": "Nairobi",
  "propertyType": "residential",
  "interest": "rto",
  "message": "...",
  "ownership": "owner",
  "billType": "prepaid",
  "hasBorehole": false,
  "monthlyBill": 9500,
  "lat": -1.286389,
  "lng": 36.817223,
  "packageTier": "Standard",
  "paymentModel": "Rent-to-Own",
  "source": "wizard_then_form"
}
```

---

## 13. Frontend Components

### Component responsibilities

| Component | Fetches DB data? | Key inputs | Key outputs |
|-----------|-----------------|------------|-------------|
| `Hero` / `AddressSearch` | No | — | `onLocationConfirmed(lat, lng, county)` |
| `DiscoveryWizard` | No | `location` | `onComplete(answers)` |
| `PricingMatrix` | Yes — packages | `location`, `answers` | `onSelectPackage(tier, model)` |
| `Packages` | Yes — packages | — | — |
| `UseCases` | Yes — use_cases | — | — |
| `Testimonials` | Yes — testimonials | — | — |
| `ContactForm` | No | `prefill` (from wizard) | POST to save_lead |
| `MoreServices` | No | — | Links to waterliftsolar.africa |

### State managed in `App.jsx`

```
locationData   { lat, lng, county }           ← from satellite map
wizardAnswers  { ownership, propertyType,      ← from wizard onComplete
                 billType, hasBorehole,
                 monthlyBill }
selectedPkg    { tier, model }                 ← from PricingMatrix CTA click
contactPrefill (derived)                       ← passed to ContactForm
```

---

## 14. Deployment

---

### 14a. Build the Frontend (required for any host)

Run this once on your local machine before uploading anything:

```bash
cd frontend
npm run build
# Output goes to: frontend/dist/
```

This compiles the React app into plain HTML + JS + CSS files that any web server can serve. The `frontend/dist/` folder is what you upload — not the `frontend/src/` folder.

---

### 14b. Deploying on Truehost

Truehost uses **cPanel** shared hosting. The steps below assume you have an active Truehost account with a domain pointed to it (e.g. `savings.waterliftsolar.africa` or `waterliftsolar.co.ke`).

#### Step 1 — Create the MySQL database in cPanel

1. Log in to your Truehost cPanel: `https://cpanel.truehost.co.ke` (or the link in your welcome email)
2. Go to **MySQL Databases** (under the Databases section)
3. Create a new database — e.g. `truehostuser_waterlift`
4. Create a new database user — e.g. `truehostuser_wluser` — and set a strong password
5. Under **Add User to Database**, assign the user to the database and grant **ALL PRIVILEGES**
6. Note down the three values — you will need them in Step 4:
   - Database name (e.g. `truehostuser_waterlift`)
   - Username (e.g. `truehostuser_wluser`)
   - Password

> **Note:** Truehost prefixes both the database name and username with your cPanel account name. The actual values shown after creation are the ones to use.

#### Step 2 — Import the database schema

1. In cPanel, open **phpMyAdmin**
2. Click on the database you just created in the left panel
3. Click the **Import** tab
4. Click **Choose File** → select `waterlift_db.sql` from your project root
5. Click **Go**

All tables (`leads`, `packages`, `use_cases`, `testimonials`, `lead_tags`) and their seed data will be created.

#### Step 3 — Upload the project files

You have two options — File Manager (easier) or FTP (faster for large uploads).

**Option A — cPanel File Manager**

1. In cPanel, open **File Manager**
2. Navigate to `public_html/` (or a subdirectory if you want the app at a subfolder, e.g. `public_html/savings/`)
3. Upload and extract your files in this structure:

```
public_html/               ← or public_html/savings/
├── index.html             ← from frontend/dist/index.html
├── assets/                ← from frontend/dist/assets/
├── images/                ← your logo + use-case images
├── .htaccess              ← see Step 5 below
├── api/
│   ├── db_connect.php
│   ├── save_lead.php
│   ├── get_packages.php
│   ├── get_use_cases.php
│   └── get_testimonials.php
└── admin/
    ├── login.php
    ├── dashboard.php
    └── ...
```

> What to upload:
> - Contents of `frontend/dist/` → directly into `public_html/`
> - The `api/` folder → `public_html/api/`
> - The `admin/` folder → `public_html/admin/`

**Option B — FTP (FileZilla)**

1. In cPanel go to **FTP Accounts** and create an FTP user
2. Open FileZilla, connect with: Host = your domain or server IP, Port = 21
3. Drag the same folder structure above to `public_html/`

#### Step 4 — Update the database connection

Edit `api/db_connect.php` with your Truehost database credentials:

```php
$dsn  = 'mysql:host=localhost;dbname=truehostuser_waterlift;charset=utf8mb4';
$user = 'truehostuser_wluser';
$pass = 'your_strong_password_here';
```

> `host=localhost` is correct for Truehost shared hosting — MySQL runs on the same server as Apache.

Also edit `admin/includes/db.php` with the same three values (it has its own separate connection for the admin panel).

#### Step 5 — Create the `.htaccess` file

Create a file named `.htaccess` in `public_html/` (or your subdirectory root) with this content:

```apache
RewriteEngine On

# Serve /api and /admin directly — do not rewrite these to index.html
RewriteCond %{REQUEST_URI} ^/api/ [OR]
RewriteCond %{REQUEST_URI} ^/admin/
RewriteRule ^ - [L]

# All other URLs → React SPA (handles client-side routing)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ /index.html [L]
```

> If you deployed to a **subdirectory** (e.g. `public_html/savings/`), change the last rule to:
> ```apache
> RewriteRule ^ /savings/index.html [L]
> ```
> And update the `RewriteCond` paths to include the subdirectory prefix:
> ```apache
> RewriteCond %{REQUEST_URI} ^/savings/api/ [OR]
> RewriteCond %{REQUEST_URI} ^/savings/admin/
> ```

#### Step 6 — Set the admin password (optional hardening)

The default admin password is `waterlift@2024`. To change it, open `admin/login.php` and update the `password_verify` check, or update the hashed password constant at the top of that file.

#### Step 7 — Verify PHP version

Truehost shared hosting supports multiple PHP versions. The project requires **PHP 8.0 or higher** (for PDO and named arguments).

1. In cPanel, go to **Select PHP Version** (or **MultiPHP Manager**)
2. Set the PHP version for your domain to **8.1** or **8.2**
3. Ensure the `pdo_mysql` extension is enabled (it is by default on Truehost)

#### Step 8 — Test everything

Visit these URLs to confirm each part works:

| URL | Expected result |
|-----|----------------|
| `https://yourdomain.com` | React app loads, hero section visible |
| `https://yourdomain.com/api/get_packages.php` | JSON array of packages |
| `https://yourdomain.com/api/get_testimonials.php` | JSON array of testimonials |
| `https://yourdomain.com/admin/` | Redirects to login page |
| Admin login with `waterlift@2024` | Dashboard visible |

If the React app loads but shows blank pages on refresh, the `.htaccess` rewrite rules are not active — ensure **mod_rewrite** is enabled (it is on all Truehost plans by default).

#### Truehost deployment checklist

```
[ ] Database created in cPanel MySQL Databases
[ ] waterlift_db.sql imported via phpMyAdmin
[ ] frontend/dist/ contents uploaded to public_html/
[ ] api/ folder uploaded to public_html/api/
[ ] admin/ folder uploaded to public_html/admin/
[ ] api/db_connect.php updated with real DB credentials
[ ] admin/includes/db.php updated with real DB credentials
[ ] .htaccess created in public_html/
[ ] PHP version set to 8.1 or 8.2 in cPanel
[ ] Tested: homepage loads
[ ] Tested: /api/get_packages.php returns JSON
[ ] Tested: admin dashboard accessible
[ ] Tested: submit a test lead → appears in admin
```

---

### 14c. Subdomain setup (recommended)

The recommended production URL is `savings.waterliftsolar.africa`. To set this up on Truehost:

1. In cPanel go to **Subdomains**
2. Enter `savings` as the subdomain, select `waterliftsolar.africa` as the domain
3. Set the document root to `public_html/savings/` (or leave as default `public_html/savings`)
4. Upload all files to that document root
5. Update `.htaccess` rewrite rules to use `/savings/` prefix as noted in Step 5

---

### 14d. Database connection (`api/db_connect.php`)

```php
// Local (XAMPP)
$dsn  = 'mysql:host=localhost;dbname=waterlift_db;charset=utf8mb4';
$user = 'root';
$pass = '';

// Truehost production — replace with your actual values
$dsn  = 'mysql:host=localhost;dbname=truehostuser_waterlift;charset=utf8mb4';
$user = 'truehostuser_wluser';
$pass = 'your_strong_password_here';
```

No other configuration changes are needed. All API paths are relative, and the Vite proxy (`vite.config.js`) only applies in local development — on Truehost, Apache serves the PHP files directly.
