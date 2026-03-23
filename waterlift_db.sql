-- ============================================================
--  Waterlift Solar Savings — Database Schema
--  Run in phpMyAdmin or MySQL CLI to initialise / upgrade.
-- ============================================================

CREATE DATABASE IF NOT EXISTS waterlift_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE waterlift_db;

-- ──────────────────────────────────────────────────────────────
--  LEADS table — captures the full wizard + contact form data
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS leads (
    id                  INT UNSIGNED      NOT NULL AUTO_INCREMENT,

    -- ── Contact details (from contact form) ──────────────────
    name                VARCHAR(100)      NOT NULL,
    phone               VARCHAR(20)       NOT NULL,
    email               VARCHAR(150)      DEFAULT NULL,
    message             TEXT              DEFAULT NULL,

    -- ── Location (from satellite map pin) ────────────────────
    lat                 DECIMAL(10, 7)    DEFAULT NULL,
    lng                 DECIMAL(10, 7)    DEFAULT NULL,
    county              VARCHAR(60)       DEFAULT NULL,
    address             TEXT              DEFAULT NULL,

    -- ── Wizard Step 1: Ownership ─────────────────────────────
    ownership_status    ENUM('owner', 'tenant')
                            DEFAULT NULL
                            COMMENT 'Step 1 — Owner or Tenant',

    -- ── Wizard Step 2: Property Category ────────────────────
    property_category   ENUM('residential', 'commercial', 'warehouse', 'industrial')
                            DEFAULT NULL
                            COMMENT 'Step 2 — Property type selected in wizard',

    -- ── Wizard Step 3: Electricity Payment Type ──────────────
    bill_type           ENUM('prepaid', 'monthly')
                            DEFAULT NULL
                            COMMENT 'Step 3 — Prepaid tokens or monthly invoice',

    -- ── Wizard Step 4: Borehole ───────────────────────────────
    has_borehole        TINYINT(1)        DEFAULT NULL
                            COMMENT 'Step 4 — 1 = Yes, 0 = No',

    -- ── Wizard Step 5: Monthly Electricity Spend ─────────────
    monthly_bill        DECIMAL(10, 2)    DEFAULT NULL
                            COMMENT 'Step 5 — Average monthly KES spend',

    -- ── Recommended package (calculated from wizard answers) ─
    package_tier        ENUM('Essential', 'Standard', 'Premium')
                            DEFAULT NULL,
    payment_model       ENUM('Rent Only', 'Rent-to-Own', 'Pay Cash')
                            DEFAULT NULL,

    -- ── Lead management ───────────────────────────────────────
    source              VARCHAR(30)       NOT NULL DEFAULT 'contact_form'
                            COMMENT 'contact_form | wizard_then_form | whatsapp',
    status              ENUM('new', 'contacted', 'qualified', 'converted', 'lost')
                            NOT NULL DEFAULT 'new',
    notes               TEXT              DEFAULT NULL
                            COMMENT 'Internal CRM notes',

    -- ── Timestamps ────────────────────────────────────────────
    created_at          TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_county          (county),
    INDEX idx_package_tier    (package_tier),
    INDEX idx_status          (status),
    INDEX idx_created_at      (created_at),
    INDEX idx_property_cat    (property_category),
    INDEX idx_ownership       (ownership_status)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='All inbound leads — from contact form and discovery wizard';


-- ──────────────────────────────────────────────────────────────
--  LEAD_TAGS — future CRM tagging (many-to-many)
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lead_tags (
    lead_id             INT UNSIGNED      NOT NULL,
    tag                 VARCHAR(50)       NOT NULL,
    created_at          TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (lead_id, tag),
    CONSTRAINT fk_lead_tags_lead
        FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ──────────────────────────────────────────────────────────────
--  PACKAGES — admin-managed solar package catalogue
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS packages (
    id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,

    -- Display
    name              VARCHAR(100)  NOT NULL,
    capacity          VARCHAR(50)   NOT NULL            COMMENT 'e.g. "1.5 kW"',
    capacity_num      DECIMAL(8,2)  NOT NULL DEFAULT 0  COMMENT 'Numeric kW for sorting & savings calc',
    icon              VARCHAR(10)   NOT NULL DEFAULT '☀️',
    tagline           VARCHAR(255)  DEFAULT NULL,
    badge             VARCHAR(60)   DEFAULT NULL        COMMENT 'e.g. "Most Popular"',
    gradient          VARCHAR(255)  NOT NULL DEFAULT 'linear-gradient(135deg,#0f2d52 0%,#1e4d8c 100%)',
    accent_color      VARCHAR(20)   NOT NULL DEFAULT '#06b6d4',
    coverage_fraction DECIMAL(4,2)  NOT NULL DEFAULT 0.50,

    -- Features (JSON array of strings)
    features          JSON          DEFAULT NULL,

    -- Pricing
    rent_monthly      INT UNSIGNED  DEFAULT NULL,
    rto_monthly       INT UNSIGNED  DEFAULT NULL,
    rto_months        TINYINT UNSIGNED NOT NULL DEFAULT 72,
    cash_price        INT UNSIGNED  DEFAULT NULL,
    cash_roi          VARCHAR(20)   DEFAULT NULL        COMMENT 'e.g. "3.5 yrs"',
    savings_label     VARCHAR(100)  DEFAULT NULL        COMMENT 'e.g. "Save up to KES 2800/mo"',

    -- Visibility & ordering
    is_active         TINYINT(1)    NOT NULL DEFAULT 1,
    sort_order        TINYINT UNSIGNED NOT NULL DEFAULT 0,

    created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_active_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Solar packages managed via admin dashboard';

-- ── Seed default packages (skip if already present) ──────────────────────────
INSERT IGNORE INTO packages
    (id, name, capacity, capacity_num, icon, tagline, badge, gradient, accent_color, coverage_fraction, features, rent_monthly, rto_monthly, rto_months, cash_price, cash_roi, savings_label, sort_order)
VALUES
(1, 'Essential', '1.5 kW', 1.5, '💡',
 'Ideal for small homes & apartments', NULL,
 'linear-gradient(135deg,#0f2d52 0%,#1e4d8c 100%)', '#06b6d4', 0.45,
 '["Up to 8 LED lights","TV + refrigerator","Phone & device charging","Basic system monitoring","1-year free maintenance"]',
 3500, 5500, 72, 180000, '3.5 yrs', 'Save up to KES 2,800/mo', 1),

(2, 'Standard', '3.0 kW', 3.0, '⚡',
 'Perfect for medium homes & offices', 'Most Popular',
 'linear-gradient(135deg,#0891b2 0%,#06b6d4 100%)', '#f59e0b', 0.65,
 '["Full home lighting","TV + fridge + washing machine","Small water pump support","24/7 remote monitoring","Lifetime maintenance"]',
 6500, 9500, 72, 320000, '3.2 yrs', 'Save up to KES 5,800/mo', 2),

(3, 'Premium', '5.0 kW+', 5.0, '🚀',
 'For large homes, boreholes & commercial', 'Borehole Ready',
 'linear-gradient(135deg,#c2410c 0%,#f97316 100%)', '#f59e0b', 0.75,
 '["Full property coverage","Borehole pump support","Air conditioning ready","Priority 24/7 support","Lifetime maintenance + monitoring"]',
 11000, 16000, 72, 550000, '2.8 yrs', 'Save up to KES 9,500/mo', 3);


-- ──────────────────────────────────────────────────────────────
--  USE_CASES — admin-managed "What We Power" cards
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS use_cases (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title       VARCHAR(150)  NOT NULL,
    tag         VARCHAR(60)   NOT NULL,
    description TEXT          NOT NULL,
    stat_label  VARCHAR(100)  DEFAULT NULL   COMMENT 'e.g. "From KES 3,500/mo"',
    image_url   VARCHAR(500)  DEFAULT NULL,
    is_active   TINYINT(1)    NOT NULL DEFAULT 1,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_active_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Use-case cards shown in the "What We Power" section';

-- Seed default use cases
INSERT IGNORE INTO use_cases (id, title, tag, description, stat_label, image_url, sort_order) VALUES
(1, 'Home Solar Installation', 'Residential',
 'Power your entire household — lights, fridge, TV, washing machine and more — with a clean, reliable solar system.',
 'From KES 3,500/mo', '/images/installation.jpg', 1),
(2, 'Business & Commercial', 'Commercial',
 'Reduce operating costs for your business, supermarket, school, or office block with a high-capacity system.',
 'Custom sizing available', '/images/solar-commercial.jpg', 2),
(3, 'Solar Irrigation & Borehole', 'Agriculture',
 'Run your borehole pump, irrigation system, and farm equipment reliably — even in remote off-grid locations.',
 'Borehole-ready Premium package', '/images/solar-irrigation.jpg', 3);


-- ──────────────────────────────────────────────────────────────
--  TESTIMONIALS — customer reviews (pending admin approval)
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonials (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100)  NOT NULL,
    location      VARCHAR(100)  DEFAULT NULL,
    package_label VARCHAR(100)  DEFAULT NULL  COMMENT 'e.g. "Standard · Rent-to-Own"',
    stars         TINYINT       NOT NULL DEFAULT 5,
    message       TEXT          NOT NULL,
    avatar_url    VARCHAR(500)  DEFAULT NULL,
    is_approved   TINYINT(1)    NOT NULL DEFAULT 0  COMMENT '0 = pending, 1 = approved',
    is_featured   TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_approved       (is_approved),
    INDEX idx_featured_order (is_approved, is_featured, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Customer testimonials — must be approved before appearing on site';

-- Seed sample approved testimonials
INSERT IGNORE INTO testimonials (id, name, location, package_label, stars, message, avatar_url, is_approved, is_featured, sort_order) VALUES
(1, 'James Mwangi', 'Kiambu County', 'Standard · Rent-to-Own', 5,
 'My monthly KPLC bill dropped from KES 12,000 to under KES 3,000 after Waterlift installed my system. The team was professional and installation took just one day.',
 NULL, 1, 1, 1),
(2, 'Fatuma Hassan', 'Mombasa County', 'Premium · Pay Cash', 5,
 'Running a small hotel on the Coast is expensive. Waterlift sized a commercial system for us and the ROI has been incredible. Highly recommend for businesses.',
 NULL, 1, 1, 2),
(3, 'Peter Kariuki', 'Nakuru County', 'Premium · Rent', 5,
 'I was sceptical about solar powering my borehole but they proved me wrong. The Premium package handles the pump surge no problem. Water 24/7 now.',
 NULL, 1, 1, 3);


-- ──────────────────────────────────────────────────────────────
--  Sample admin view (run manually if needed)
-- ──────────────────────────────────────────────────────────────
-- SELECT
--     id, name, phone, county, property_category,
--     ownership_status, has_borehole, monthly_bill,
--     package_tier, payment_model, status, created_at
-- FROM leads
-- ORDER BY created_at DESC
-- LIMIT 50;
