-- sample_products.sql
PRAGMA foreign_keys = OFF;
BEGIN TRANSACTION;

-- TABLA categories (si no existe)
CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- TABLA products (si no existe) - estructura simple compatible con MySQL/SQLite
CREATE TABLE IF NOT EXISTS products (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(1024),
  stock_quantity INTEGER DEFAULT 0,
  category_id INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insertar categories
INSERT INTO categories (name) VALUES ('Cascos');
INSERT INTO categories (name) VALUES ('Ropa y Protecciones');
INSERT INTO categories (name) VALUES ('Accesorios');
INSERT INTO categories (name) VALUES ('Repuestos');
INSERT INTO categories (name) VALUES ('ElectrÃ³nica');

-- Insertar 20 productos
INSERT INTO products (name, description, price, image, stock_quantity, category_id) VALUES
('Casco de moto integral', 'Casco integral con certificaciÃ³n ECE 22.05 y interior desmontable', 129.99, 'https://via.placeholder.com/640x480.png?text=Casco', 25, 1),
('Casco modular con visera solar', 'Casco modular con visera integrada y forro lavable', 179.50, 'https://via.placeholder.com/640x480.png?text=Casco+modular', 15, 1),
('Chaqueta de cuero para moto', 'Chaqueta de cuero bovino con protecciones en hombros y codos', 199.00, 'https://via.placeholder.com/640x480.png?text=Chaqueta', 12, 2),
('Guantes de moto de verano', 'Guantes transpirables con refuerzos en nudillos', 39.90, 'https://via.placeholder.com/640x480.png?text=Guantes', 40, 2),
('Botas de moto impermeables', 'Botas con membrana impermeable y suela antideslizante', 149.00, 'https://via.placeholder.com/640x480.png?text=Botas', 10, 2),
('Soporte para mÃ³vil en moto', 'Soporte universal con fijaciÃ³n al manillar', 24.50, 'https://via.placeholder.com/640x480.png?text=Soporte+mÃ³vil', 60, 3),
('Alarma para moto con GPS', 'Alarma con localizador GPS y alertas al mÃ³vil', 89.99, 'https://via.placeholder.com/640x480.png?text=Alarma+GPS', 8, 3),
('Candado de disco con alarma', 'Candado con alarma de 120dB y cable reforzado', 54.99, 'https://via.placeholder.com/640x480.png?text=Candado', 22, 3),
('Filtro de aceite para moto', 'Filtro de alto rendimiento compatible con 4T', 12.50, 'https://via.placeholder.com/640x480.png?text=Filtro+aceite', 80, 4),
('Pastillas de freno delanteras', 'Pastillas sinterizadas para frenada potente', 45.00, 'https://via.placeholder.com/640x480.png?text=Pastillas+freno', 35, 4),
('Kit de arrastre completo', 'Cadena, corona y piÃ±Ã³n de acero de alta resistencia', 129.00, 'https://via.placeholder.com/640x480.png?text=Kit+arrastre', 14, 4),
('NeumÃ¡tico trasero deportivo', 'NeumÃ¡tico con excelente agarre en seco y mojado', 99.95, 'https://via.placeholder.com/640x480.png?text=NeumÃ¡tico', 20, 4),
('Aceite de motor 4T 10W-40', 'Aceite sintÃ©tico para motores 4T', 18.75, 'https://via.placeholder.com/640x480.png?text=Aceite+4T', 120, 4),
('BujÃ­a de iridio', 'BujÃ­a de iridio para mejor combustiÃ³n y durabilidad', 19.99, 'https://via.placeholder.com/640x480.png?text=BujÃ­a', 50, 4),
('BaterÃ­a de gel para moto', 'BaterÃ­a de gel sin mantenimiento con excelente arranque', 89.00, 'https://via.placeholder.com/640x480.png?text=BaterÃ­a', 9, 5),
('Manillar deportivo de aluminio', 'Manillar deportivo mÃ¡s ligero y resistente', 74.90, 'https://via.placeholder.com/640x480.png?text=Manillar', 18, 5),
('Espejos retrovisores homologados', 'Espejos con diseÃ±o aerodinÃ¡mico y homologados', 29.50, 'https://via.placeholder.com/640x480.png?text=Espejos', 45, 3),
('Intermitentes LED', 'Juego de intermitentes LED de alta visibilidad', 34.99, 'https://via.placeholder.com/640x480.png?text=Intermitentes', 30, 3),
('Faro delantero halÃ³geno H4', 'Faro halÃ³geno H4 de alta potencia', 22.00, 'https://via.placeholder.com/640x480.png?text=Faro+H4', 26, 5),
('BaÃºl para moto 48L', 'BaÃºl con capacidad para dos cascos y respaldo', 159.00, 'https://via.placeholder.com/640x480.png?text=BaÃºl+48L', 7, 3);

COMMIT;
PRAGMA foreign_keys = ON;
