CREATE DATABASE IF NOT EXISTS cassavaforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cassavaforge;

CREATE TABLE products (
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

CREATE TABLE blog_posts (
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

CREATE TABLE contact_messages (
  id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(200) NOT NULL,
  email_address VARCHAR(254) NOT NULL,
  message_body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE site_visitors (
  visitor_id CHAR(36) PRIMARY KEY,
  first_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE page_views (
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
