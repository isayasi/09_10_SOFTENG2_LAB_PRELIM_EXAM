-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'writer') NOT NULL DEFAULT 'writer',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ARTICLES TABLE
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    image_path VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- NOTIFICATIONS TABLE
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'danger', 'deletion', 'edit_request') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- EDIT REQUESTS TABLE
CREATE TABLE edit_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    requester_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SHARED ARTICLES TABLE
CREATE TABLE shared_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_share (article_id, user_id)
);

-- INSERT DEFAULT ADMIN USER
INSERT INTO users (id, username, email, first_name, last_name, password, role, registration_date, created_at) 
VALUES (
    1,
    'admin', 
    'admin@schoolpaper.com', 
    'System', 
    'Administrator', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hashed password: "password"
    'admin',
    NOW(),
    NOW()
);

-- CATEGORIES TABLE
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ARTICLE_CATEGORIES (Many-to-Many relationship)
CREATE TABLE article_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    category_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_article_category (article_id, category_id)
);

-- Add category_id column to articles table for primary category
ALTER TABLE articles ADD COLUMN category_id INT NULL AFTER author_id;
ALTER TABLE articles ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Insert some default categories
INSERT INTO categories (name, description, created_by) VALUES
('News Reports', 'Factual reporting of current events and news', 1),
('Editorials', 'Opinion pieces representing the newspaper''s stance', 1),
('Opinion', 'Personal opinions and perspectives from writers', 1),
('Features', 'In-depth stories and human interest articles', 1),
('Sports', 'Sports news and athletic achievements', 1),
('Entertainment', 'Arts, culture, and entertainment news', 1),
('Technology', 'Tech news and innovations', 1);



