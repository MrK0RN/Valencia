-- Добавление полей близости к морю и метро
ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS sea_distance_meters INTEGER,
    ADD COLUMN IF NOT EXISTS sea_distance_minutes INTEGER,
    ADD COLUMN IF NOT EXISTS metro_distance_meters INTEGER,
    ADD COLUMN IF NOT EXISTS metro_distance_minutes INTEGER;
