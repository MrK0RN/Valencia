-- Миграция: добавление поля repair_needed в таблицу properties
-- Дата: 2024

ALTER TABLE properties 
ADD COLUMN repair_needed BOOLEAN DEFAULT FALSE;


