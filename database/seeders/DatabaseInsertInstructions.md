# GuÃ­a Completa para Insertar Datos en ComercioPlus

Esta guÃ­a proporciona comandos SQL completos para insertar datos de prueba en todas las tablas principales de la base de datos ComercioPlus.

## âš ï¸ Importante: Orden de InserciÃ³n

Para respetar las claves forÃ¡neas, insertar los datos en este orden:
1. users
2. categories
3. stores
4. products
5. carts
6. cart_products
7. orders
8. order_products

---

## 1. Tabla: users

```sql
-- Usuario administrador
INSERT INTO users (name, email, password, phone, status, address, role, created_at, updated_at)
VALUES ('Admin ComercioPlus', 'admin@comercioplus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 1, 'Calle Principal 123', 'admin', NOW(), NOW());

-- Usuario comerciante
INSERT INTO users (name, email, password, phone, status, address, role, created_at, updated_at)
VALUES ('MarÃ­a GonzÃ¡lez', 'maria@tienda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 1, 'Av. Comercio 456', 'comerciante', NOW(), NOW());

-- Usuario cliente
INSERT INTO users (name, email, password, phone, status, address, role, created_at, updated_at)
VALUES ('Carlos RodrÃ­guez', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1122334455', 1, 'Plaza Central 789', 'cliente', NOW(), NOW());
```

---

## 2. Tabla: categories

```sql
INSERT INTO categories (name, description, slug, created_at, updated_at)
VALUES ('ElectrÃ³nica', 'Productos electrÃ³nicos y gadgets', 'electronica', NOW(), NOW());

INSERT INTO categories (name, description, slug, created_at, updated_at)
VALUES ('Ropa y Accesorios', 'Ropa, zapatos y accesorios de moda', 'ropa-accesorios', NOW(), NOW());

INSERT INTO categories (name, description, slug, created_at, updated_at)
VALUES ('Hogar y JardÃ­n', 'ArtÃ­culos para el hogar y jardÃ­n', 'hogar-jardin', NOW(), NOW());

INSERT INTO categories (name, description, slug, created_at, updated_at)
VALUES ('Deportes', 'Equipamiento deportivo y fitness', 'deportes', NOW(), NOW());

INSERT INTO categories (name, description, slug, created_at, updated_at)
VALUES ('Libros', 'Libros, revistas y material educativo', 'libros', NOW(), NOW());
```

---

## 3. Tabla: stores

```sql
-- Tienda de MarÃ­a GonzÃ¡lez (user_id = 2)
INSERT INTO stores (user_id, name, slug, logo, cover, primary_color, description, direccion, telefono, estado, horario_atencion, categoria_principal, calificacion_promedio, created_at, updated_at)
VALUES (2, 'TechStore', 'techstore', '/images/stores/techstore-logo.png', '/images/stores/techstore-cover.jpg', '#FF6B35', 'Tu tienda de tecnologÃ­a de confianza', 'Av. TecnologÃ­a 123, Ciudad', '555-0123', 'activa', 'Lunes a Viernes 9:00-18:00', 'ElectrÃ³nica', 4.8, NOW(), NOW());

-- Tienda adicional
INSERT INTO stores (user_id, name, slug, logo, cover, primary_color, description, direccion, telefono, estado, horario_atencion, categoria_principal, calificacion_promedio, created_at, updated_at)
VALUES (2, 'Fashion Corner', 'fashion-corner', '/images/stores/fashion-logo.png', '/images/stores/fashion-cover.jpg', '#8B5CF6', 'Moda y estilo para todos', 'Centro Comercial Plaza, Local 45', '555-0456', 'activa', 'Lunes a SÃ¡bado 10:00-20:00', 'Ropa y Accesorios', 4.6, NOW(), NOW());
```

---

## 4. Tabla: products

```sql
-- Productos para TechStore (store_id = 1)
INSERT INTO products (name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at)
VALUES ('iPhone 15 Pro', 'El Ãºltimo smartphone de Apple con chip A17 Pro', 1299.99, 15, '/images/products/iphone15.jpg', 1, 0, 4.9, 2, 1, NOW(), NOW());

INSERT INTO products (name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at)
VALUES ('MacBook Air M3', 'Laptop ultradelgada con chip M3', 1499.99, 8, '/images/products/macbook.jpg', 1, 1, 4.7, 2, 1, NOW(), NOW());

INSERT INTO products (name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at)
VALUES ('AirPods Pro', 'AudÃ­fonos inalÃ¡mbricos con cancelaciÃ³n de ruido', 249.99, 25, '/images/products/airpods.jpg', 1, 0, 4.8, 2, 1, NOW(), NOW());

-- Productos para Fashion Corner (store_id = 2)
INSERT INTO products (name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at)
VALUES ('Vestido Elegante', 'Vestido de noche negro con detalles en encaje', 89.99, 12, '/images/products/vestido.jpg', 2, 1, 4.5, 2, 2, NOW(), NOW());

INSERT INTO products (name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at)
VALUES ('Zapatillas Deportivas', 'Zapatillas cÃ³modas para running', 79.99, 20, '/images/products/zapatillas.jpg', 2, 0, 4.3, 2, 2, NOW(), NOW());
```

---

## 5. Tabla: carts

```sql
-- Carrito activo para Carlos (user_id = 3)
INSERT INTO carts (user_id, status, created_at, updated_at)
VALUES (3, 'active', NOW(), NOW());

-- Carrito completado para Carlos
INSERT INTO carts (user_id, status, created_at, updated_at)
VALUES (3, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));
```

---

## 6. Tabla: cart_products

```sql
-- Productos en carrito activo (cart_id = 1)
INSERT INTO cart_products (cart_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (1, 1, 1, 1299.99, NOW(), NOW());

INSERT INTO cart_products (cart_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (1, 3, 2, 249.99, NOW(), NOW());

-- Productos en carrito completado (cart_id = 2)
INSERT INTO cart_products (cart_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (2, 4, 1, 89.99, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));
```

---

## 7. Tabla: orders

```sql
-- Orden pendiente
INSERT INTO orders (user_id, store_id, total, status, created_at, updated_at)
VALUES (3, 1, 1799.97, 'pending', NOW(), NOW());

-- Orden completada
INSERT INTO orders (user_id, store_id, total, status, created_at, updated_at)
VALUES (3, 2, 89.99, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));
```

---

## 8. Tabla: order_products

```sql
-- Productos de orden pendiente (order_id = 1)
INSERT INTO order_products (order_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (1, 1, 1, 1299.99, NOW(), NOW());

INSERT INTO order_products (order_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (1, 3, 2, 249.99, NOW(), NOW());

-- Productos de orden completada (order_id = 2)
INSERT INTO order_products (order_id, product_id, quantity, unit_price, created_at, updated_at)
VALUES (2, 4, 1, 89.99, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));
```

---

## ðŸ“‹ Script Completo de InserciÃ³n

Para ejecutar todos los inserts de una vez, copia y pega este script completo en tu cliente MySQL:

```sql
-- Insertar usuarios
INSERT INTO users (id, name, email, role_id, password, phone, avatar, status, address, created_at, updated_at) VALUES
(1, 'Carlos Motos', 'carlos@motos.com', 2, '12345678', '3214567890', NULL, 1, 'Calle 1 #1-1', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(2, 'Andrea Repuestos', 'andrea@repuestos.com', 2, '12345678', '3001112233', NULL, 1, 'Carrera 5 #4-3', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(3, 'Juan Cliente', 'juan@cliente.com', 2, '12345678', '3102223344', NULL, 1, 'Av. Siempre Viva 742', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(4, 'Ana Tienda', 'ana@tienda.com', 2, '12345678', '3113334455', NULL, 1, 'Calle 8 #8-8', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(5, 'Pedro Comprador', 'pedro@comprador.com', 2, '12345678', '3124445566', NULL, 1, 'Calle 10 #10-10', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(6, 'Laura Comercial', 'laura@comercial.com', 2, '12345678', '3135556677', NULL, 1, 'Cra 15 #15-15', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(7, 'Daniel Cliente', 'daniel@cliente.com', 2, '12345678', '3146667788', NULL, 1, 'Cra 20 #20-20', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(8, 'Jorge Vendedor', 'jorge@vendedor.com', 2, '12345678', '3157778899', NULL, 1, 'Av. Norte 100', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(9, 'LucÃ­a Tienda', 'lucia@tienda.com', 2, '12345678', '3168889900', NULL, 1, 'Calle Sur 23', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(10, 'Felipe Repuestos', 'felipe@repuestos.com', 2, '12345678', '3179990011', NULL, 1, 'Cra 8 #12-4', '2025-08-01 02:07:41', '2025-08-01 02:07:41'),
(11, 'Andres Arenas', 'andriw034@gmail.com', 2, '$2y$10$6cTQxGbiYZwK1u9ZvT8xl.Jd6809.5JDw1KwOmelKXOpCXfxqF4iK', NULL, NULL, 1, NULL, '2025-08-06 17:03:08', '2025-08-06 17:03:08');

-- Insertar categorÃ­as
INSERT INTO categories (id, name, parent_id, created_at, updated_at) VALUES
(1, 'Repuestos', NULL, '2025-08-01 02:57:50', '2025-08-01 02:57:50'),
(2, 'Llantas', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(3, 'Luces', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(4, 'Frenos', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(5, 'Aceites', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(6, 'Espejos', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(7, 'Herramientas', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(8, 'BaterÃ­as', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(9, 'Accesorios', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37'),
(10, 'Protecciones', NULL, '2025-08-01 03:00:37', '2025-08-01 03:00:37');

-- Insertar tiendas
INSERT INTO stores (id, user_id, name, slug, logo, cover, background, primary_color, description, direccion, telefono, estado, horario_atencion, categoria_principal, calificacion_promedio, created_at, updated_at) VALUES
(1, 1, 'MotoRÃ¡pida', 'motorapida', 'logo1.png', 'cover1.jpg', NULL, '#FFA14F', 'Venta de accesorios de motos de alto rendimiento.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(2, 2, 'TurboMoto', 'turbomoto', 'logo2.png', 'cover2.jpg', NULL, '#FFA14F', 'Especialistas en llantas y aceites.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(3, 3, 'Repuestos Ãguila', 'repuestos-aguila', 'logo3.png', 'cover3.jpg', NULL, '#FFA14F', 'Repuestos originales para motos Yamaha y Honda.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(4, 4, 'FullMoto', 'fullmoto', 'logo4.png', 'cover4.jpg', NULL, '#FFA14F', 'Tienda completa de cascos, llantas y luces.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(5, 5, 'MotoSpeed', 'motospeed', 'logo5.png', 'cover5.jpg', NULL, '#FFA14F', 'Velocidad y rendimiento en un solo lugar.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(6, 6, 'RacingParts', 'racingparts', 'logo6.png', 'cover6.jpg', NULL, '#FFA14F', 'Todo para tu moto de carreras.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(7, 7, 'MecÃ¡nica Pro', 'mecanica-pro', 'logo7.png', 'cover7.jpg', NULL, '#FFA14F', 'Repuestos y servicio tÃ©cnico.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(8, 8, 'MotoStore', 'motostore', 'logo8.png', 'cover8.jpg', NULL, '#FFA14F', 'Tu tienda de confianza para motos.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(9, 9, 'Accesorios MÃ³viles', 'accesorios-moviles', 'logo9.png', 'cover9.jpg', NULL, '#FFA14F', 'Accesorios para todo tipo de moto.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18'),
(10, 10, 'Repuestos Colombia', 'repuestos-colombia', 'logo10.png', 'cover10.jpg', NULL, '#FFA14F', 'Distribuidor autorizado de repuestos.', '', NULL, 'activa', NULL, '', 0.00, '2025-08-01 02:09:18', '2025-08-01 02:09:18');

-- Insertar productos
INSERT INTO products (id, name, description, price, stock, image, category_id, offer, average_rating, user_id, store_id, created_at, updated_at) VALUES
(1, 'Casco Deportivo', 'Casco certificado para motociclismo deportivo', 250000.00, 30, 'casco1.jpg', 1, 0, 4.7, 1, 1, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(2, 'Aceite Motul 5100', 'Lubricante semisintÃ©tico 10W40', 58000.00, 50, 'aceite1.jpg', 2, 1, 4.5, 2, 2, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(3, 'Pastillas de freno', 'Juego de pastillas para freno de disco', 40000.00, 45, 'pastillas1.jpg', 3, 0, 4.3, 3, 3, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(4, 'Cadena reforzada', 'Cadena para motocicletas de alto cilindraje', 90000.00, 20, 'cadena1.jpg', 4, 1, 4.6, 4, 4, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(5, 'Guantes Racing', 'Guantes de cuero con protecciones', 72000.00, 25, 'guantes1.jpg', 5, 1, 4.8, 5, 5, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(6, 'Filtro de aire', 'Filtro de alto rendimiento para motos', 35000.00, 40, 'filtro1.jpg', 6, 0, 4.2, 6, 6, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(7, 'Espejos retrovisores', 'Par de espejos universales', 30000.00, 60, 'espejos1.jpg', 7, 0, 4.4, 7, 7, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(8, 'Luces LED', 'Kit de luces LED blancas para moto', 45000.00, 35, 'led1.jpg', 8, 1, 4.6, 8, 8, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(9, 'Llantas Michelin', 'Llanta trasera 140/70', 180000.00, 10, 'llanta1.jpg', 9, 0, 4.9, 9, 9, '2025-08-01 03:03:25', '2025-08-01 03:03:25'),
(10, 'Cubre tanque', 'Protector de tanque antirayones', 27000.00, 70, 'tanque1.jpg', 10, 1, 4.1, 10, 10, '2025-08-01 03:03:25', '2025-08-01 03:03:25');

-- Insertar carritos
INSERT INTO carts (id, user_id, status, created_at, updated_at) VALUES
(1, 1, 'active', '2025-08-01 18:01:00', '2025-08-01 18:01:00'),
(2, 2, 'completed', '2025-08-01 18:02:00', '2025-08-01 18:02:00'),
(3, 3, 'active', '2025-08-01 18:03:00', '2025-08-01 18:03:00'),
(4, 4, 'completed', '2025-08-01 18:04:00', '2025-08-01 18:04:00'),
(5, 5, 'active', '2025-08-01 18:05:00', '2025-08-01 18:05:00'),
(6, 6, 'completed', '2025-08-01 18:06:00', '2025-08-01 18:06:00'),
(7, 7, 'active', '2025-08-01 18:07:00', '2025-08-01 18:07:00'),
(8, 8, 'completed', '2025-08-01 18:08:00', '2025-08-01 18:08:00'),
(9, 9, 'active', '2025-08-01 18:09:00', '2025-08-01 18:09:00'),
(10, 10, 'completed', '2025-08-01 18:10:00', '2025-08-01 18:10:00');

-- Insertar productos en carritos
INSERT INTO cart_products (id, cart_id, product_id, quantity, created_at, updated_at) VALUES
(1, 1, 1, 2, '2025-08-01 18:15:00', '2025-08-01 18:15:00'),
(2, 2, 3, 1, '2025-08-01 18:16:00', '2025-08-01 18:16:00'),
(3, 3, 2, 4, '2025-08-01 18:17:00', '2025-08-01 18:17:00'),
(4, 4, 5, 3, '2025-08-01 18:18:00', '2025-08-01 18:18:00'),
(5, 5, 4, 1, '2025-08-01 18:19:00', '2025-08-01 18:19:00'),
(6, 6, 6, 2, '2025-08-01 18:20:00', '2025-08-01 18:20:00'),
(7, 7, 7, 3, '2025-08-01 18:21:00', '2025-08-01 18:21:00'),
(8, 8, 8, 1, '2025-08-01 18:22:00', '2025-08-01 18:22:00'),
(9, 9, 9, 2, '2025-08-01 18:23:00', '2025-08-01 18:23:00'),
(10, 10, 10, 5, '2025-08-01 18:24:00', '2025-08-01 18:24:00');

-- Insertar canales
INSERT INTO channels (id, type, link, created_at, updated_at) VALUES
(1, 'YouTube', 'https://youtube.com/comercioplus1', '2025-08-01 18:30:00', '2025-08-01 18:30:00'),
(2, 'Instagram', 'https://instagram.com/motopartsplus', '2025-08-01 18:31:00', '2025-08-01 18:31:00'),
(3, 'Facebook', 'https://facebook.com/tiendasmoto', '2025-08-01 18:32:00', '2025-08-01 18:32:00'),
(4, 'Twitter', 'https://twitter.com/motoplus_co', '2025-08-01 18:33:00', '2025-08-01 18:33:00'),
(5, 'TikTok', 'https://tiktok.com/@comercioplus', '2025-08-01 18:34:00', '2025-08-01 18:34:00'),
(6, 'WhatsApp', 'https://wa.me/573001112233', '2025-08-01 18:35:00', '2025-08-01 18:35:00'),
(7, 'Telegram', 'https://t.me/comercioplusbot', '2025-08-01 18:36:00', '2025-08-01 18:36:00'),
(8, 'LinkedIn', 'https://linkedin.com/company/motopartsplus', '2025-08-01 18:37:00', '2025-08-01 18:37:00'),
(9, 'Pinterest', 'https://pinterest.com/mototiendas', '2025-08-01 18:38:00', '2025-08-01 18:38:00'),
(10, 'Twitch', 'https://twitch.tv/comerciopluslive', '2025-08-01 18:39:00', '2025-08-01 18:39:00');

-- Insertar reclamos
INSERT INTO claims (id, user_id, message, date, contact_method, created_at, updated_at) VALUES
(1, 1, 'Producto no llegÃ³ en la fecha estimada.', '2025-07-01 10:00:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(2, 2, 'El producto llegÃ³ daÃ±ado.', '2025-07-02 11:30:00', 'phone', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(3, 3, 'No corresponde con la descripciÃ³n.', '2025-07-03 14:45:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(4, 4, 'Quiero hacer un cambio de producto.', '2025-07-04 09:15:00', 'phone', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(5, 5, 'No encuentro opciÃ³n de reembolso.', '2025-07-05 16:00:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(6, 6, 'Necesito soporte tÃ©cnico urgente.', '2025-07-06 13:20:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(7, 7, 'Mi pedido estÃ¡ incompleto.', '2025-07-07 17:10:00', 'phone', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(8, 8, 'No puedo contactar al vendedor.', '2025-07-08 08:50:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(9, 9, 'El sistema no me deja pagar.', '2025-07-09 12:00:00', 'phone', '2025-08-01 03:22:23', '2025-08-01 03:22:23'),
(10, 10, 'Quiero cancelar mi pedido.', '2025-07-10 15:30:00', 'email', '2025-08-01 03:22:23', '2025-08-01 03:22:23');

-- Insertar ubicaciones
INSERT INTO locations (id, address, city, state, postal_code, country, latitude, longitude, user_id, created_at, updated_at) VALUES
(1, 'Cra 10 # 5-30', 'BogotÃ¡', 'Cundinamarca', '110111', 'Colombia', 4.6097000, -74.0817000, 1, '2025-08-01 17:00:00', '2025-08-01 17:00:00'),
(2, 'Calle 45 # 6-15', 'MedellÃ­n', 'Antioquia', '050021', 'Colombia', 6.2518000, -75.5636000, 2, '2025-08-01 17:05:00', '2025-08-01 17:05:00'),
(3, 'Av. 3N # 18-24', 'Cali', 'Valle', '760001', 'Colombia', 3.4516000, -76.5320000, 3, '2025-08-01 17:10:00', '2025-08-01 17:10:00'),
(4, 'Cl. 13 # 4-90', 'Barranquilla', 'AtlÃ¡ntico', '080001', 'Colombia', 10.9685000, -74.7813000, 4, '2025-08-01 17:15:00', '2025-08-01 17:15:00'),
(5, 'Carrera 7 # 8-20', 'Cartagena', 'BolÃ­var', '130001', 'Colombia', 10.3910000, -75.4794000, 5, '2025-08-01 17:20:00', '2025-08-01 17:20:00'),
(6, 'Cl. 60 # 5-90', 'Pereira', 'Risaralda', '660001', 'Colombia', 4.8143000, -75.6946000, 6, '2025-08-01 17:25:00', '2025-08-01 17:25:00'),
(7, 'Av. 1E # 15-45', 'CÃºcuta', 'Norte de Santander', '540001', 'Colombia', 7.8939000, -72.5078000, 7, '2025-08-01 17:30:00', '2025-08-01 17:30:00'),
(8, 'Cra 8 # 19-32', 'Bucaramanga', 'Santander', '680001', 'Colombia', 7.1193000, -73.1227000, 8, '2025-08-01 17:35:00', '2025-08-01 17:35:00'),
(9, 'Cl. 23 # 4-18', 'Manizales', 'Caldas', '170001', 'Colombia', 5.0689000, -75.5174000, 9, '2025-08-01 17:40:00', '2025-08-01 17:40:00'),
(10, 'Av. 4A # 6-70', 'Neiva', 'Huila', '410001', 'Colombia', 2.9386000, -75.2678000, 10, '2025-08-01 17:45:00', '2025-08-01 17:45:00');

-- Insertar notificaciones
INSERT INTO notifications (id, title, message, is_read, user_id, created_at, updated_at) VALUES
(1, 'Bienvenido a ComercioPlus', 'Gracias por registrarte en nuestra plataforma.', 0, 1, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(2, 'Nuevo producto agregado', 'Se ha agregado un nuevo producto a tu tienda.', 0, 2, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(3, 'Tu pedido fue recibido', 'Hemos recibido tu pedido y lo estamos procesando.', 0, 3, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(4, 'Mensaje de cliente', 'Un cliente ha dejado un comentario en tu producto.', 0, 4, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(5, 'ActualizaciÃ³n de perfil', 'Tu perfil fue actualizado correctamente.', 1, 5, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(6, 'Tienda aprobada', 'Tu tienda ha sido aprobada por el equipo de revisiÃ³n.', 1, 6, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(7, 'Producto destacado', 'Uno de tus productos ha sido destacado en la portada.', 0, 2, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(8, 'Venta realizada', 'Â¡Felicidades! Has realizado una venta.', 1, 1, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(9, 'Producto agotado', 'Uno de tus productos se ha quedado sin stock.', 0, 3, '2025-08-01 02:08:07', '2025-08-01 02:08:07'),
(10, 'Soporte respondiÃ³', 'Tu solicitud de soporte ha sido respondida.', 1, 4, '2025-08-01 02:08:07', '2025-08-01 02:08:07');

-- Insertar perfiles
INSERT INTO profiles (id, username, image, birthdate, other_info, user_id, created_at, updated_at) VALUES
(1, 'andres_arenas', 'andres.jpg', '1986-05-15', 'Amante de las motos', 1, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),
(2, 'moto_store_123', 'moto123.png', '1992-08-09', 'Tienda especializada en repuestos', 2, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),
(3, 'cliente_motos1', 'cliente1.jpg', '1995-02-20', 'Fan de las motos deportivas', 3, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),
(4, 'admin_mecanico', 'admin.jpg', '1980-11-01', 'Administrador y mecÃ¡nico', 4, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),
(5, 'usuario_tienda', 'tiendauser.jpg', '1990-03-05', 'Vendemos motos de segunda', 5, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),
(6, 'cliente_aventurero', 'aventurero.png', '1988-12-10', 'Explorador de caminos en moto', 6, '2025-08-01 03:26:19', '2025-08-01 03:26:19'),

---

## ðŸ”§ Notas TÃ©cnicas

- **ContraseÃ±as**: Todas las contraseÃ±as estÃ¡n hasheadas con bcrypt. La contraseÃ±a real para todos los usuarios es `password`.
- **ImÃ¡genes**: Las rutas de imÃ¡genes son relativas y deben existir en el directorio `public/images/`.
- **Fechas**: Se usan `NOW()` para fechas actuales y `DATE_SUB(NOW(), INTERVAL X DAY)` para fechas pasadas.
- **IDs**: Los IDs de claves forÃ¡neas deben coincidir con los registros insertados previamente.
- **Campos opcionales**: Se pueden omitir campos marcados como `nullable` en las migraciones.

## ðŸš€ CÃ³mo Usar

1. Conecta a tu base de datos MySQL
2. Ejecuta los comandos en el orden especificado
3. Verifica que los datos se insertaron correctamente con consultas SELECT

Este conjunto de datos proporciona una base sÃ³lida para probar todas las funcionalidades de ComercioPlus.
