-- Run this once in phpMyAdmin against an existing cassavaforge database
-- to enable landing-page tracking without re-importing schema.sql.

USE cassavaforge;

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
