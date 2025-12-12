-- Добавление английских полей для названия и описания объекта
ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS title_en VARCHAR(255),
    ADD COLUMN IF NOT EXISTS description_en TEXT;

