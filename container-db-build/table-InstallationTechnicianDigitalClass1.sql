BEGIN;

-- FUNCTION
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 工事担任者デジタル1級
-- Installation Technician Digital Class 1

-- TABLE
DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_category CASCADE;
CREATE TABLE InstallationTechnicianDigitalClass1_category (
    id integer primary key,
    name text not null
);

DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_mondaiset CASCADE;
CREATE TABLE IF NOT EXISTS InstallationTechnicianDigitalClass1_mondaiset (
    id serial primary key,
    mondai jsonb not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted BOOLEAN default false
);

INSERT INTO InstallationTechnicianDigitalClass1_category (id, name) VALUES
   (1, '基礎'),
   (2, '技術'),
   (3, '法規');

-- TRIGGER
CREATE OR REPLACE TRIGGER InstallationTechnicianDigitalClass1_mondaiset_updated_at
    BEFORE UPDATE ON InstallationTechnicianDigitalClass1_mondaiset
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();
COMMIT;
