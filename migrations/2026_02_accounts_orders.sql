-- Run once against cassavaforge. Seeds the first owner from config on the next request.

USE cassavaforge;

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
