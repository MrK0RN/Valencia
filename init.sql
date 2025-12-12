-- Создание таблицы пользователей
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    login_code VARCHAR(6) NULL,
    login_code_expires_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    notification_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица объектов недвижимости
CREATE TABLE properties (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    price DECIMAL(12,2) NOT NULL,
    address_full TEXT,
    address_district VARCHAR(255),
    show_exact_address BOOLEAN DEFAULT FALSE,
    sea_distance_meters INTEGER,
    sea_distance_minutes INTEGER,
    metro_distance_meters INTEGER,
    metro_distance_minutes INTEGER,
    area_total DECIMAL(8,2),
    area_living DECIMAL(8,2),
    area_kitchen DECIMAL(8,2),
    floor INTEGER,
    total_floors INTEGER,
    rooms INTEGER,
    description TEXT,
    description_en TEXT,
    video_url VARCHAR(500),
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'sold', 'hidden')),
    featured BOOLEAN DEFAULT FALSE,
    repair_needed BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    price_history JSONB DEFAULT '[]'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица фотографий
CREATE TABLE property_photos (
    id SERIAL PRIMARY KEY,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    image_path VARCHAR(500) NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица характеристик
CREATE TABLE property_features (
    id SERIAL PRIMARY KEY,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    feature_type VARCHAR(100) NOT NULL,
    feature_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица избранного
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, property_id)
);

-- Таблица заявок
CREATE TABLE requests (
    id SERIAL PRIMARY KEY,
    type VARCHAR(50) NOT NULL CHECK (type IN ('callback', 'agent_message', 'viewing')),
    property_id INTEGER REFERENCES properties(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    message TEXT,
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'processed', 'completed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица сессий
CREATE TABLE sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(64) UNIQUE NOT NULL,
    ip_address INET,
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица истории просмотров объектов
CREATE TABLE property_view_history (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address INET,
    user_agent TEXT
);

-- Уникальный индекс для предотвращения дубликатов просмотров в один день
CREATE UNIQUE INDEX idx_view_history_unique_day 
ON property_view_history(user_id, property_id, DATE(viewed_at));

-- Таблица подписок на уведомления о снижении цены
CREATE TABLE price_alerts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    target_price DECIMAL(12,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, property_id)
);

-- Таблица уведомлений
CREATE TABLE property_notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    property_id INTEGER REFERENCES properties(id) ON DELETE SET NULL,
    type VARCHAR(50) NOT NULL CHECK (type IN ('price_drop', 'sold', 'new_featured')),
    title VARCHAR(255) NOT NULL,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Индексы для оптимизации
CREATE INDEX idx_properties_price ON properties(price);
CREATE INDEX idx_properties_area ON properties(area_total);
CREATE INDEX idx_properties_rooms ON properties(rooms);
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_featured ON properties(featured);
CREATE INDEX idx_properties_sort ON properties(sort_order);
CREATE INDEX idx_features_property ON property_features(property_id);
CREATE INDEX idx_favorites_user ON favorites(user_id);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_sessions_token ON sessions(token);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_view_history_user ON property_view_history(user_id);
CREATE INDEX idx_view_history_property ON property_view_history(property_id);
CREATE INDEX idx_view_history_viewed ON property_view_history(viewed_at);
CREATE INDEX idx_price_alerts_user ON price_alerts(user_id);
CREATE INDEX idx_price_alerts_property ON price_alerts(property_id);
CREATE INDEX idx_price_alerts_active ON price_alerts(is_active);
CREATE INDEX idx_notifications_user ON property_notifications(user_id);
CREATE INDEX idx_notifications_read ON property_notifications(is_read);
CREATE INDEX idx_notifications_created ON property_notifications(created_at);

-- Создание демо-администратора
-- Email: admin@demo.com
-- Password: demo123
-- 
-- ВАЖНО: Для создания демо-администратора используйте PHP-скрипт setup_demo_admin.php
-- так как password_hash() генерирует уникальные хеши при каждом вызове.
-- 
-- Или создайте вручную через PHP:
-- $password = password_hash('demo123', PASSWORD_DEFAULT);
-- INSERT INTO users (name, email, password, role, notification_enabled)
-- VALUES ('Демо Администратор', 'admin@demo.com', $password, 'admin', true);

