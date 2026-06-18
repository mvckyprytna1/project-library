-- Vicky Project Library
-- PHP Native 8+ / MySQL-MariaDB
-- Engine: InnoDB
-- Charset: utf8mb4_unicode_ci

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('owner') NOT NULL DEFAULT 'owner',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  color VARCHAR(20) NOT NULL DEFAULT '#60a5fa',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  description TEXT NULL,
  status ENUM('live','draft','development','bug','archived') NOT NULL DEFAULT 'draft',
  tech_stack VARCHAR(255) NULL,
  hosting_platform VARCHAR(120) NULL,
  github_url VARCHAR(255) NULL,
  live_url VARCHAR(255) NULL,
  thumbnail VARCHAR(255) NULL,
  notes TEXT NULL,
  is_favorite TINYINT(1) NOT NULL DEFAULT 0,
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_projects_category (category_id),
  INDEX idx_projects_status (status),
  INDEX idx_projects_favorite (is_favorite),
  INDEX idx_projects_public (is_public),
  INDEX idx_projects_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  note TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_project_notes_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_project_notes_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, slug, color) VALUES
('Web App', 'web-app', '#38bdf8'),
('Landing Page', 'landing-page', '#a78bfa'),
('Dashboard', 'dashboard', '#34d399'),
('Bot / Automation', 'bot-automation', '#f59e0b')
ON DUPLICATE KEY UPDATE name = VALUES(name);
