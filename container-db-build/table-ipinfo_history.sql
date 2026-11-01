BEGIN;

-- FUNCTION
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_default_ip_in_rawjson()
RETURNS TRIGGER AS $$
BEGIN
    -- rawjson に ip キーが存在しない場合のみ ip カラムの値をセットする
    IF NOT (NEW.rawjson ? 'ip') THEN
        NEW.rawjson = jsonb_set(NEW.rawjson, '{ip}', to_jsonb(NEW.ip::text));
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TABLE
DROP TABLE IF EXISTS ipinfo_history CASCADE;
CREATE TABLE ipinfo_history (
    ip inet NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    rawjson JSONB NOT NULL DEFAULT '{}',
    deleted BOOLEAN DEFAULT false
);

-- INSERT (127.0.0.0/8 の一括挿入)
INSERT INTO ipinfo_history (ip, rawjson)
    SELECT ip::inet, jsonb_build_object('ip', ip) AS rawjson
        FROM (
            SELECT '127.' || (i >> 16 & 255) || '.' || (i >> 8 & 255) || '.' || (i & 255) AS ip
                FROM generate_series(0, 16777215) AS i
        ) sub;

ALTER TABLE ipinfo_history ADD CONSTRAINT ipinfo_history_pkey PRIMARY KEY (ip);

-- TRIGGER
CREATE OR REPLACE TRIGGER update_ipinfo_history_updated_at
    BEFORE UPDATE ON ipinfo_history
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE OR REPLACE TRIGGER trg_set_default_ip_in_rawjson
    BEFORE INSERT OR UPDATE ON ipinfo_history
    FOR EACH ROW
    EXECUTE FUNCTION set_default_ip_in_rawjson();

COMMIT;
