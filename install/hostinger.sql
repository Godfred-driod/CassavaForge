-- Import this into the empty MySQL database Hostinger already created.
-- Do not run CREATE DATABASE. Select the Hostinger database first in phpMyAdmin.

CREATE TABLE IF NOT EXISTS products (
  id CHAR(36) PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(200) NOT NULL,
  series_code VARCHAR(50),
  category ENUM('packaging','films','cutlery') NOT NULL,
  application_grade VARCHAR(100),
  description TEXT,
  image_url VARCHAR(2048),
  icon VARCHAR(50),
  spec1_label VARCHAR(100),
  spec1_value VARCHAR(200),
  spec2_label VARCHAR(100),
  spec2_value VARCHAR(200),
  display_order INT NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_posts (
  id CHAR(36) PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  excerpt TEXT,
  content LONGTEXT,
  image_url VARCHAR(2048),
  author VARCHAR(200),
  category VARCHAR(80),
  read_minutes SMALLINT,
  published TINYINT(1) NOT NULL DEFAULT 1,
  published_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
  id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(200) NOT NULL,
  email_address VARCHAR(254) NOT NULL,
  message_body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_visitors (
  visitor_id CHAR(36) PRIMARY KEY,
  first_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS page_views (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visitor_id CHAR(36) NULL,
  path VARCHAR(255) NOT NULL,
  referrer VARCHAR(2048) NULL,
  user_agent VARCHAR(512) NULL,
  viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_page_views_path (path),
  INDEX idx_page_views_viewed_at (viewed_at),
  INDEX idx_page_views_visitor (visitor_id)
);

CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) PRIMARY KEY,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(200) NOT NULL,
  role ENUM('owner','staff','partner') NOT NULL,
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  company_name VARCHAR(200) NULL,
  phone VARCHAR(40) NULL,
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_status (role, status)
);

CREATE TABLE IF NOT EXISTS orders (
  id CHAR(36) PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  user_id CHAR(36) NOT NULL,
  assignee_id CHAR(36) NULL,
  type ENUM('sample','trial','quote','production') NOT NULL,
  status ENUM('submitted','reviewing','in_progress','shipped','quoted','declined','closed') NOT NULL DEFAULT 'submitted',
  payment_status ENUM('none','pending','paid','waived') NOT NULL DEFAULT 'none',
  product_id CHAR(36) NULL,
  quantity VARCHAR(80) NULL,
  unit VARCHAR(40) NULL,
  message TEXT NOT NULL,
  staff_note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_status (status),
  INDEX idx_orders_type (type),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_orders_assignee FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_events (
  id CHAR(36) PRIMARY KEY,
  order_id CHAR(36) NOT NULL,
  actor_user_id CHAR(36) NULL,
  event ENUM('created','status','note','message') NOT NULL,
  body TEXT NULL,
  internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order_events_order (order_id, created_at),
  CONSTRAINT fk_order_events_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_order_events_actor FOREIGN KEY (actor_user_id) REFERENCES users (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
  id CHAR(36) PRIMARY KEY,
  order_id CHAR(36) NOT NULL,
  product_id CHAR(36) NULL,
  qty VARCHAR(80) NULL,
  unit VARCHAR(40) NULL,
  unit_price DECIMAL(12,2) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
);

INSERT INTO products (id, slug, name, series_code, category, application_grade, description, image_url, display_order, published) VALUES
(UUID(), 'cassava-flex-pack', 'Cassava Flex Pack', 'CF-01', 'packaging', 'Flexible packaging', 'A renewable film platform for lightweight applications where clarity and end-of-life matter.', 'assets/images/packaging.jpg', 1, 1),
(UUID(), 'cassava-clear-film', 'Cassava Clear Film', 'CF-02', 'films', 'Transparent barrier film', 'A clear, adaptable film material for thoughtful product and food-contact concepts.', 'assets/images/films.jpg', 2, 1),
(UUID(), 'cassava-molded-form', 'Cassava Molded Form', 'CF-03', 'cutlery', 'Molded products', 'A practical molded-material base for single-use products that should not outlive their purpose.', 'assets/images/cutlery.jpg', 3, 1);

INSERT INTO blog_posts (id, slug, title, category, excerpt, content, image_url, author, read_minutes, published) VALUES
(UUID(), 'why-cassava', 'Why cassava is an important material story', 'Materials', 'Cassava connects agricultural resilience with new possibilities in product design.', 'Good material choices begin with good systems thinking. Cassava grows in climates where other crops can be difficult, and its starch offers a compelling starting point for renewable material development.\n\nThe opportunity is not to replace every material overnight. It is to build credible alternatives, test them honestly, and make the better choice easier to adopt.', 'assets/images/blog-smiling-african-smallholder-cassava-farmers-standing-4.jpg', 'CassavaForge', 4, 1),
(UUID(), 'designing-for-the-next-cycle', 'Designing for the next cycle', 'Design', 'Performance and responsibility become more useful when they are considered together.', 'A material is never only a material. It is a supply chain, a manufacturing decision, a user experience, and an end-of-life question. Our work is to bring those questions into the same room early enough to make a difference.', 'assets/images/blog-minimalist-product-design-showcase-featuring-eco-5.jpg', 'CassavaForge', 3, 1),
(UUID(), 'from-farm-to-formulation', 'From farm to formulation', 'Research', 'A closer look at the material journey from cassava harvest to usable biopolymer.', 'The strongest material systems begin with a clear understanding of their feedstock. We work with agricultural partners and formulation teams to connect source quality to finished-product performance.', 'assets/images/blog-modern-biotechnology-laboratory-glassware-clear-glass-2.jpg', 'CassavaForge', 5, 1),
(UUID(), 'designing-for-compostability', 'Designing for compostability', 'Sustainability', 'End-of-life performance is a design input, not an afterthought.', 'Compostability only matters when it is designed into the full product system. That means thinking about thickness, additives, use conditions, and the environments where the material will return.', 'assets/images/blog-wide-panoramic-vista-of-a-sustainable-6.jpg', 'CassavaForge', 4, 1),
(UUID(), 'building-the-value-chain', 'Building the value chain', 'Community', 'Regenerative materials depend on aligned growers, makers, and brands.', 'The transition to lower-impact materials is a collective project. Connecting growers, converters, researchers, and brands creates the practical feedback loops needed to scale responsibly.', 'assets/images/blog-a-vivid-vibrant-green-young-plant-3.jpg', 'CassavaForge', 3, 1),
(UUID(), 'a-materials-update', 'A materials update from CassavaForge', 'Product Update', 'New formulations and pilot conversations are moving from the lab toward real applications.', 'Our current focus is translating promising cassava resin blends into dependable solutions for packaging, films, and molded products. We are testing the variables that matter in real manufacturing environments.', 'assets/images/blog-close-up-macro-studio-photography-of-1.jpg', 'CassavaForge', 3, 1);
