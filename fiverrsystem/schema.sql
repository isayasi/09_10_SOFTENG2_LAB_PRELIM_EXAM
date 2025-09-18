CREATE TABLE fiverr_clone_users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    password TEXT,
    is_client BOOLEAN,
    bio_description TEXT,
    display_picture TEXT,
    contact_number VARCHAR(255),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE proposals (
    proposal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    description TEXT,
    image TEXT,
    min_price INT,
    max_price INT,
    view_count INT DEFAULT 0,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES fiverr_clone_users(user_id)
);

CREATE TABLE offers (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    description TEXT,
    proposal_id INT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES fiverr_clone_users(user_id),
    FOREIGN KEY (proposal_id) REFERENCES proposals(proposal_id)
);

-- Create categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create subcategories table
CREATE TABLE subcategories (
    subcategory_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Add category and subcategory references to proposals table
ALTER TABLE proposals 
ADD COLUMN category_id INT NULL AFTER user_id,
ADD COLUMN subcategory_id INT NULL AFTER category_id,
ADD FOREIGN KEY (category_id) REFERENCES categories(category_id),
ADD FOREIGN KEY (subcategory_id) REFERENCES subcategories(subcategory_id);

-- Add is_admin column to users table
ALTER TABLE fiverr_clone_users 
ADD COLUMN is_admin BOOLEAN DEFAULT FALSE AFTER is_client;

-- Insert an administrator user
INSERT INTO fiverr_clone_users (
    username, 
    email, 
    password, 
    is_client, 
    is_admin, 
    bio_description, 
    display_picture, 
    contact_number
) VALUES (
    'fiverr_admin', 
    'admin@fiverrclone.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: password
    TRUE,  -- Can act as a client
    TRUE,  -- Is an administrator
    'Fiverr System Administrator with full access to manage categories and user accounts.',
    'admin_avatar.jpg', 
    '+1-555-ADMIN-001'
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Technology', 'Services related to technology and software development'),
('Design & Creative', 'Creative services including graphic design, video editing, and more'),
('Writing & Translation', 'Content writing, translation, and proofreading services'),
('Marketing', 'Digital marketing, SEO, and social media services'),
('Business', 'Business consulting, virtual assistance, and other business services');