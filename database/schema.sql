CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'owner', 'agent', 'admin') NOT NULL DEFAULT 'client',
    profile_image VARCHAR(255) DEFAULT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(100) NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    district_name VARCHAR(100) DEFAULT NULL,
    neighborhood_name VARCHAR(100) NOT NULL,
    specific_area VARCHAR(150) DEFAULT NULL
);

CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    property_type ENUM('chambre', 'studio', 'appartement', 'maison', 'mini_cite', 'terrain', 'autre') NOT NULL,
    listing_type ENUM('location', 'vente') NOT NULL,
    property_style ENUM('moderne', 'classique') NOT NULL DEFAULT 'classique',
    price DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'XAF',
    rooms INT NOT NULL DEFAULT 0,
    bedrooms INT NOT NULL DEFAULT 0,
    living_rooms INT NOT NULL DEFAULT 0,
    bathrooms INT NOT NULL DEFAULT 0,
    kitchens INT NOT NULL DEFAULT 0,
    kitchen_type ENUM('interne', 'externe') NOT NULL DEFAULT 'interne',
    surface_area DECIMAL(10,2) DEFAULT NULL,
    is_furnished TINYINT(1) NOT NULL DEFAULT 0,
    has_water TINYINT(1) NOT NULL DEFAULT 0,
    has_electricity TINYINT(1) NOT NULL DEFAULT 0,
    has_parking TINYINT(1) NOT NULL DEFAULT 0,
    has_fence TINYINT(1) NOT NULL DEFAULT 0,
    security_level ENUM('faible', 'moyen', 'bon', 'eleve') NOT NULL DEFAULT 'moyen',
    road_access ENUM('mauvais', 'moyen', 'bon') NOT NULL DEFAULT 'moyen',
    near_school TINYINT(1) NOT NULL DEFAULT 0,
    near_market TINYINT(1) NOT NULL DEFAULT 0,
    near_hospital TINYINT(1) NOT NULL DEFAULT 0,
    near_university TINYINT(1) NOT NULL DEFAULT 0,
    near_transport TINYINT(1) NOT NULL DEFAULT 0,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('disponible', 'reserve', 'loue', 'vendu', 'retire') NOT NULL DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_properties_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_properties_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE RESTRICT
);

CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_property_images_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    CONSTRAINT uq_favorites_user_property UNIQUE (user_id, property_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    property_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

CREATE TABLE visit_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    requester_id INT NOT NULL,
    owner_id INT NOT NULL,
    preferred_date DATETIME NOT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('en_attente', 'acceptee', 'refusee', 'annulee') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_visit_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_requester FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT DEFAULT NULL,
    reason VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('en_attente', 'traite', 'rejete') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

CREATE TABLE search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    keywords VARCHAR(255) DEFAULT NULL,
    region_name VARCHAR(100) DEFAULT NULL,
    city_name VARCHAR(100) DEFAULT NULL,
    min_price DECIMAL(12,2) DEFAULT NULL,
    max_price DECIMAL(12,2) DEFAULT NULL,
    property_type VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_search_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
