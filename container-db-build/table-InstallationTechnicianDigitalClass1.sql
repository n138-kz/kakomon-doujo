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
    id uuid primary key default gen_random_uuid(),
    openid text unique not null default gen_random_uuid(),
    mondai jsonb not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted BOOLEAN default false
);

--
-- 払い出した問題のリスト: SID を外部キーにする
-- 答案提出後にリセットする
DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_assigns CASCADE;
CREATE TABLE IF NOT EXISTS InstallationTechnicianDigitalClass1_assigns (
    id uuid primary key default gen_random_uuid(),
    mondai_id uuid not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_answed varchar(4),
    correct_mondai BOOLEAN DEFAULT false,
    user_id uuid not null,
    session_id uuid not null
);

-- 過去に解いた問題のリスト:
DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_log CASCADE;
CREATE TABLE IF NOT EXISTS InstallationTechnicianDigitalClass1_log (
    id uuid primary key default gen_random_uuid(),
    mondai_id uuid not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_answed varchar(4),
    correct_mondai BOOLEAN DEFAULT false,
    user_id uuid not null
);

INSERT INTO InstallationTechnicianDigitalClass1_category (id, name) VALUES
   (1, '基礎'),
   (2, '技術'),
   (3, '端末設備の接続に関する法規');

-- TRIGGER
CREATE OR REPLACE TRIGGER InstallationTechnicianDigitalClass1_mondaiset_updated_at
    BEFORE UPDATE ON InstallationTechnicianDigitalClass1_mondaiset
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- VIEW
CREATE OR REPLACE VIEW vw_InstallationTechnicianDigitalClass1_mondaiset_ramdom_mondais AS
SELECT m.id internal_id, m.openid id, m.mondai,c.name FROM InstallationTechnicianDigitalClass1_mondaiset m
JOIN installationtechniciandigitalclass1_category c
ON m.mondai->>'category' = c.id::text
WHERE deleted = false ORDER BY random();

CREATE OR REPLACE VIEW vw_InstallationTechnicianDigitalClass1_mondaiset_count AS
SELECT (SELECT COUNT(OPENID) FROM INSTALLATIONTECHNICIANDIGITALCLASS1_MONDAISET) count_all,
(SELECT COUNT(OPENID) FROM INSTALLATIONTECHNICIANDIGITALCLASS1_MONDAISET WHERE MONDAI ->> 'category' = '1' ) count_category_1,
(SELECT COUNT(OPENID) FROM INSTALLATIONTECHNICIANDIGITALCLASS1_MONDAISET WHERE MONDAI ->> 'category' = '2' ) count_category_2,
(SELECT COUNT(OPENID) FROM INSTALLATIONTECHNICIANDIGITALCLASS1_MONDAISET WHERE MONDAI ->> 'category' = '3' ) count_category_3;

COMMIT;
