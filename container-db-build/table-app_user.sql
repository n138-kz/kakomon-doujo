BEGIN;

-- FUNCTION
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TABLE
DROP TABLE IF EXISTS app_user_role CASCADE;
CREATE TABLE app_user_role (
    id INT PRIMARY KEY,
    description TEXT NOT NULL
);

DROP TABLE IF EXISTS app_user CASCADE;
CREATE TABLE app_user (
    id uuid primary key default gen_random_uuid(),
    email text,
    app_username text,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    rawjson JSONB NOT NULL DEFAULT '{}',
    deleted BOOLEAN DEFAULT false,
    role INT not null default 0,
    CONSTRAINT chk_role_range
        CHECK (role >= 0 AND role <= 63)
);

DROP TABLE IF EXISTS app_authn_session CASCADE;
CREATE TABLE app_authn_session (
    id serial primary key,
    userid uuid not null,
    ip_address inet not null default '::1'::inet,
    user_agent text not null,
    payload JSONB NOT NULL DEFAULT '{}',
    session_id uuid not null default gen_random_uuid(),
    expires_in INT not null DEFAULT (1::int4 << 31),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_userid
        FOREIGN KEY (userid)
        REFERENCES app_user(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- INITIAL DATA
INSERT INTO app_user_role VALUES
(0, 'NOBODY'),
(1, 'READ'),
(2, 'SEARCH'),
(4, 'UPDATE'),
(8, 'REGIST'),
(16, 'DELETE'),
(32, 'USERMANAGEMENT');

-- TRIGGER
CREATE OR REPLACE TRIGGER update_app_user_updated_at
    BEFORE UPDATE ON app_user
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE OR REPLACE TRIGGER update_app_authn_session_updated_at
    BEFORE UPDATE ON app_authn_session
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();
COMMIT;
