-- Gopak E-ticaret Veritabanı Tabloları

-- Müşteriler tablosu (sipariş veren kişiler)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Siparişler tablosu (yeni yapı)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    product_type VARCHAR(100) NOT NULL,
    product_color VARCHAR(100) NOT NULL,
    product_size VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    size_multiplier DECIMAL(3,1) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(3,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'production', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- İletişim mesajları tablosu (mevcut contact formu için)
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fiyat geçmişi tablosu (admin panel fiyat yönetimi için)
CREATE TABLE price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_type ENUM('increase', 'decrease') NOT NULL,
    percentage_change DECIMAL(5,2) NOT NULL,
    reason TEXT,
    affected_products INT DEFAULT 1,
    product_id INT NULL,
    product_name VARCHAR(255) NULL,
    old_price DECIMAL(10,2) NULL,
    new_price DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_change_type (change_type),
    INDEX idx_product_id (product_id)
);

-- Ürünler tablosu (admin panel ürün yönetimi için)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500),
    category VARCHAR(100) DEFAULT 'Standart',
    is_custom BOOLEAN DEFAULT FALSE,
    bag_type VARCHAR(100) NULL,
    bag_dimensions VARCHAR(100) NULL,
    min_order_quantity INT NULL,
    bag_description TEXT NULL,
    has_custom_print BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- Örnek veri ekleme (isteğe bağlı)
-- INSERT INTO customers (first_name, last_name, email, phone, address) VALUES
-- ('Ahmet', 'Yılmaz', 'ahmet@example.com', '0532 123 4567', 'İstanbul, Türkiye');

-- Örnek çanta ürünleri ekleme
INSERT INTO products (name, description, price, stock_quantity, category, bag_type, bag_dimensions, min_order_quantity, bag_description, has_custom_print) VALUES
('3D Çanta Küçük Boy', 'Pastane ve tatlıcılar için uygun 3D çanta', 2.50, 10000, 'Çanta', '3D Çanta (Yan Körüklü)', '30 × 25 × 10', 2500, 'Pastane ve tatlıcılar için uygun', 1),
('3D Çanta Orta Boy', 'Pastane ve tatlıcılar için uygun 3D çanta', 3.00, 8000, 'Çanta', '3D Çanta (Yan Körüklü)', '25 × 20 × 15', 2500, 'Pastane ve tatlıcılar için uygun', 1),
('3D Çanta Büyük Boy', 'Pastane ve tatlıcılar için uygun 3D çanta', 3.50, 6000, 'Çanta', '3D Çanta (Yan Körüklü)', '35 × 20 × 25', 1500, 'Pastane ve tatlıcılar için uygun', 1),
('3D Çanta Extra Büyük', 'Pastane ve tatlıcılar için uygun 3D çanta', 4.00, 5000, 'Çanta', '3D Çanta (Yan Körüklü)', '35 × 25 × 30', 1500, 'Pastane ve tatlıcılar için uygun', 1),
('Düz Çanta Küçük Boy', 'Giyim markaları ve kırtasiyeler için uygun', 2.00, 12000, 'Çanta', 'Düz Çanta (Yan Körüksüz)', '30 × 40 × 10', 3000, 'Giyim markaları ve kırtasiyeler için uygun', 1),
('Düz Çanta Orta Boy', 'Giyim markaları ve kırtasiyeler için uygun', 2.50, 10000, 'Çanta', 'Düz Çanta (Yan Körüksüz)', '40 × 40 × 10', 2500, 'Giyim markaları ve kırtasiyeler için uygun', 1),
('Düz Çanta Büyük Boy', 'Giyim markaları ve kırtasiyeler için uygun', 3.00, 8000, 'Çanta', 'Düz Çanta (Yan Körüksüz)', '50 × 50 × 10', 2000, 'Giyim markaları ve kırtasiyeler için uygun', 1),
('Düz Çanta Extra Büyük', 'Giyim markaları ve kırtasiyeler için uygun', 3.50, 6000, 'Çanta', 'Düz Çanta (Yan Körüksüz)', '40 × 45', 2500, 'Giyim markaları ve kırtasiyeler için uygun', 1);

