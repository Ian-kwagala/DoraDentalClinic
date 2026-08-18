-- =========================================================================
-- dcms_schema.sql
-- Full database schema for Dora's Dental Gem DCMS (Demo Phase entities).
-- Import this into phpMyAdmin (XAMPP) or via: mysql -u root doradental < dcms_schema.sql
-- =========================================================================

CREATE DATABASE IF NOT EXISTS doradental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE doradental;

-- -------------------------------------------------------------------------
-- USERS / ROLES  (Phase 0)
-- Role is a single ENUM column rather than a separate roles table —
-- simpler for 3 fixed roles, matches the blueprint's "Users/Roles" entity.
-- -------------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','dentist','receptionist') NOT NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME NULL   -- soft-delete: staff accounts are disabled, not erased
);

-- -------------------------------------------------------------------------
-- PATIENTS  (Phase 1, scaffolded now)
-- -------------------------------------------------------------------------
CREATE TABLE patients (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_code       VARCHAR(20) NOT NULL UNIQUE,   -- e.g. DDG-1042
    first_name         VARCHAR(80) NOT NULL,
    last_name          VARCHAR(80) NOT NULL,
    date_of_birth      DATE NULL,
    gender             ENUM('male','female','other') NULL,
    phone              VARCHAR(30) NULL,
    email              VARCHAR(150) NULL,
    address            VARCHAR(255) NULL,
    next_of_kin_name   VARCHAR(120) NULL,
    next_of_kin_phone  VARCHAR(30) NULL,
    blood_group        VARCHAR(5) NULL,
    allergies          TEXT NULL,
    medical_conditions TEXT NULL,
    current_medications TEXT NULL,
    created_by         INT UNSIGNED NULL,             -- FK to users.id (who registered them)
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         DATETIME NULL,                 -- soft-delete, never hard-delete patient records
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_patient_name (last_name, first_name),
    INDEX idx_patient_phone (phone)
);

-- -------------------------------------------------------------------------
-- APPOINTMENTS  (Phase 2, scaffolded now)
-- -------------------------------------------------------------------------
CREATE TABLE appointments (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT UNSIGNED NOT NULL,
    dentist_id       INT UNSIGNED NULL,        -- FK to users.id (role=dentist)
    chair_label      VARCHAR(30) NULL,         -- e.g. "Chair 1"
    appointment_date DATE NOT NULL,
    start_time       TIME NOT NULL,
    end_time         TIME NOT NULL,
    status           ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    reason           VARCHAR(255) NULL,        -- e.g. "Review + polish"
    notes            TEXT NULL,
    created_by       INT UNSIGNED NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (dentist_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_appt_date (appointment_date)
);

-- -------------------------------------------------------------------------
-- TREATMENTS / CLINICAL RECORDS  (Phase 3, scaffolded now)
-- One row per finding/procedure logged against a tooth or general visit.
-- -------------------------------------------------------------------------
CREATE TABLE treatments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT UNSIGNED NOT NULL,
    appointment_id  INT UNSIGNED NULL,
    dentist_id      INT UNSIGNED NULL,
    tooth_number    VARCHAR(5) NULL,   -- FDI notation, e.g. "26"
    finding         ENUM('healthy','filled','caries','crown','root_canal','missing') NULL,
    diagnosis       TEXT NULL,
    treatment_notes TEXT NULL,
    procedure_code  VARCHAR(20) NULL,  -- e.g. "D2391" (matches invoice line items)
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (dentist_id) REFERENCES users(id)
);

CREATE TABLE prescriptions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT UNSIGNED NOT NULL,
    treatment_id   INT UNSIGNED NULL,
    medication_name VARCHAR(150) NOT NULL,
    dosage         VARCHAR(80) NULL,     -- e.g. "500mg"
    frequency      VARCHAR(80) NULL,     -- e.g. "TDS (3x/day)"
    duration       VARCHAR(50) NULL,     -- e.g. "5 days"
    instructions   VARCHAR(255) NULL,    -- e.g. "After meals"
    prescribed_by  INT UNSIGNED NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (treatment_id) REFERENCES treatments(id),
    FOREIGN KEY (prescribed_by) REFERENCES users(id)
);

-- -------------------------------------------------------------------------
-- BILLING & PAYMENTS  (Phase 4, scaffolded now)
-- -------------------------------------------------------------------------
CREATE TABLE invoices (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number  VARCHAR(30) NOT NULL UNIQUE,  -- e.g. INV-2026-0189
    patient_id      INT UNSIGNED NOT NULL,
    appointment_id  INT UNSIGNED NULL,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
    vat_amount      DECIMAL(12,2) NOT NULL DEFAULT 0,
    insurance_cover DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_due       DECIMAL(12,2) NOT NULL DEFAULT 0,
    status          ENUM('draft','issued','partial','paid','overdue') NOT NULL DEFAULT 'draft',
    issue_date      DATE NULL,
    due_date        DATE NULL,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE invoice_items (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id     INT UNSIGNED NOT NULL,
    procedure_code VARCHAR(20) NULL,
    description    VARCHAR(255) NOT NULL,
    quantity       INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price     DECIMAL(12,2) NOT NULL,
    amount         DECIMAL(12,2) NOT NULL,  -- quantity * unit_price, stored for easy display
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id   INT UNSIGNED NOT NULL,
    patient_id   INT UNSIGNED NOT NULL,
    amount       DECIMAL(12,2) NOT NULL,
    method       ENUM('cash','mobile_money','card','bank','insurance') NOT NULL,
    payment_date DATE NOT NULL,
    recorded_by  INT UNSIGNED NULL,
    notes        VARCHAR(255) NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- -------------------------------------------------------------------------
-- NOTIFICATIONS  (Phase 5, scaffolded now)
-- -------------------------------------------------------------------------
CREATE TABLE notification_templates (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,   -- e.g. "Appointment reminder"
    channel      ENUM('sms','email','both') NOT NULL,
    subject      VARCHAR(150) NULL,       -- used for email only
    message_body TEXT NOT NULL,           -- supports {{patient}} {{date}} {{time}} {{dentist}} merge tags
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE notification_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id  INT UNSIGNED NULL,
    template_id INT UNSIGNED NULL,
    channel     ENUM('sms','email') NOT NULL,
    recipient   VARCHAR(150) NOT NULL,   -- phone or email actually sent to
    message     TEXT NOT NULL,           -- final rendered message (after merge tags filled in)
    status      ENUM('pending','sent','delivered','failed') NOT NULL DEFAULT 'pending',
    sent_at     DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (template_id) REFERENCES notification_templates(id)
);

-- -------------------------------------------------------------------------
-- AUDIT LOG  (Non-functional requirement: track who changed what)
-- -------------------------------------------------------------------------
CREATE TABLE audit_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(50) NOT NULL,   -- e.g. "create", "update", "soft_delete"
    table_name VARCHAR(50) NOT NULL,
    record_id  INT UNSIGNED NULL,
    details    TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);