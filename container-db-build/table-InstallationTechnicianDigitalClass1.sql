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
DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_user CASCADE;
CREATE TABLE InstallationTechnicianDigitalClass1_user (
    id uuid primary key default gen_random_uuid(),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    role INT not null default 0,
    CONSTRAINT chk_role_range
        CHECK (role >= 0 AND role <= 63),
    CONSTRAINT fk_userid
        FOREIGN KEY (id)
        REFERENCES app_user(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

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
    id serial primary key,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id uuid not null,
    session_id uuid not null,
    mondai_id uuid not null,
    user_selected varchar(4),
    correct_mondai BOOLEAN DEFAULT false
);

-- 過去に解いた問題のリスト:
DROP TABLE IF EXISTS InstallationTechnicianDigitalClass1_log CASCADE;
CREATE TABLE IF NOT EXISTS InstallationTechnicianDigitalClass1_log (
    id uuid primary key default gen_random_uuid(),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id uuid not null,
    mondai_id uuid not null,
    user_selected varchar(4),
    correct_mondai BOOLEAN DEFAULT false,
    CONSTRAINT fk_userid
        FOREIGN KEY (user_id)
        REFERENCES app_user(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_mondai_id
        FOREIGN KEY (mondai_id)
        REFERENCES InstallationTechnicianDigitalClass1_mondaiset(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO InstallationTechnicianDigitalClass1_category (id, name) VALUES
   (1, '電気通信技術の基礎'),
   (2, '端末設備の接続のための技術及び理論'),
   (3, '端末設備の接続に関する法規');

-- TRIGGER
CREATE OR REPLACE TRIGGER InstallationTechnicianDigitalClass1_mondaiset_updated_at
    BEFORE UPDATE ON InstallationTechnicianDigitalClass1_mondaiset
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE OR REPLACE TRIGGER InstallationTechnicianDigitalClass1_assigns_updated_at
    BEFORE UPDATE ON InstallationTechnicianDigitalClass1_assigns
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE OR REPLACE TRIGGER InstallationTechnicianDigitalClass1_log_updated_at
    BEFORE UPDATE ON InstallationTechnicianDigitalClass1_log
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
