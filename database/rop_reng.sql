CREATE DATABASE IF NOT EXISTS rop_reng
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rop_reng;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_seasonal TINYINT(1) NOT NULL DEFAULT 0,

    season_start DATE DEFAULT NULL,
    season_end DATE DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_products_category (category_id),
    INDEX idx_products_active (is_active),
    INDEX idx_products_best_seller (is_best_seller),
    INDEX idx_products_new (is_new),
    INDEX idx_products_seasonal (is_seasonal)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_variants_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    INDEX idx_product_variants_product (product_id),
    INDEX idx_product_variants_active (product_id, is_active)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    customer_name VARCHAR(150) DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_orders_created_at (created_at),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(150) NOT NULL,
    variant_name VARCHAR(50) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    line_total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_order_items_variant
        FOREIGN KEY (variant_id)
        REFERENCES product_variants(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_product (product_id),
    INDEX idx_order_items_variant (variant_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (name, slug) VALUES
('Giải khát cà phê', 'giaikhatcaphe'),
('Latte', 'latte'),
('Nước giải khát', 'nuocgiaikhat'),
('Phê đi', 'phedi'),
('Quốc dân cà phê', 'quocdancaphe'),
('Thong dong cà phê', 'thongdongcaphe'),
('Trà sữa', 'trasua');