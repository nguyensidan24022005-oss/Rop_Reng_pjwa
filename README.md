# Rốp Rẻng — Cà phê & Giải khát

> **Source of Truth cho phiên bản V1**
>
> README này là tài liệu nền tảng của dự án.  
> Mọi thay đổi code, database, UI/UX và tính năng trong V1 phải bám theo tài liệu này.
>
> **Nguyên tắc quan trọng:** Không tự ý thêm tính năng V2 vào V1 chỉ vì thấy “có thể làm được”.

---

# 1. Tổng quan dự án

**Tên dự án:** Rốp Rẻng – Cà phê & Giải khát

**Loại dự án:** Website F&B đặt món + hệ thống quản lý nội bộ.

**Mục tiêu V1:**

- Xây dựng website giới thiệu và bán sản phẩm của Rốp Rẻng.
- Hiển thị Menu động từ Database.
- Quản lý danh mục và sản phẩm bằng Database.
- Hỗ trợ sản phẩm có nhiều size/variant.
- Thêm sản phẩm vào giỏ hàng.
- Tạo đơn hàng.
- Lưu đơn hàng vào Database.
- Quản lý đơn hàng cơ bản.
- Có Admin Dashboard phục vụ vận hành cửa hàng.
- Quản lý sản phẩm/danh mục.
- Có thống kê doanh thu cơ bản.
- Giao diện hiện đại, dễ sử dụng, ưu tiên desktop.

---

# 2. Phạm vi V1

## 2.1. Những gì V1 PHẢI có

### Customer-facing website

- Homepage
- Menu
- Category
- Product listing
- Product variants / size
- Cart
- Checkout / tạo đơn
- Order code sau khi tạo đơn
- Responsive cơ bản

### Admin / Staff

- Admin Dashboard
- Quản lý sản phẩm
- Quản lý danh mục
- Quản lý đơn hàng
- Xem chi tiết đơn
- Cập nhật trạng thái đơn
- Thống kê doanh thu cơ bản

### Backend

- PHP Native
- PDO
- MySQL / MariaDB
- REST-like JSON API nội bộ
- Server-side validation
- Transaction khi tạo đơn
- Database snapshot cho order items

---

# 3. Những gì KHÔNG làm trong V1

Các tính năng dưới đây thuộc **V2 hoặc tương lai**.

Không tự ý triển khai trong V1:

- Customer registration
- Customer login
- Customer profile
- Customer personal order history
- Loyalty Points
- Quy tắc điểm hết hạn cuối năm
- Coupon / Voucher
- Discount code
- Advanced customer management
- Multi-level Admin permissions
- Advanced revenue charts
- Multi-branch management
- Online payment
- Email notification
- SMS notification
- Advanced best-selling analytics
- Product reviews
- Product ratings
- Advanced search
- Smart search / AI recommendation
- Chatbot
- LLM
- Redis
- Celery
- Docker nếu không cần cho V1 hiện tại
- React / Node build system

## Quy tắc

Nếu một tính năng không nằm trong V1 nhưng có vẻ hữu ích:

> Ghi nhận cho V2, KHÔNG tự ý đưa vào V1.

---

# 4. Mô hình vận hành V1

Rốp Rẻng V1 tập trung vào **vận hành cửa hàng**, không xây dựng một nền tảng e-commerce đầy đủ.

Luồng chính:

```text
Homepage
   ↓
Menu
   ↓
Category
   ↓
Product
   ↓
Variant / Size
   ↓
Add to Cart
   ↓
Cart
   ↓
Checkout
   ↓
Create Order
   ↓
Order Code
   ↓
Admin / Staff quản lý đơn
```

Không có:

```text
Customer Account
Customer Login
Shipping
Online Payment
Loyalty
Coupon
```

---

# 5. Công nghệ

## Frontend

- HTML5
- CSS3
- JavaScript ES6+
- PHP-rendered pages

Không dùng:

- React
- Vue
- Angular
- Node.js build
- Vite

trong kiến trúc V1 hiện tại.

## Backend

- PHP Native
- PHP 8+
- PDO
- JSON API

## Database

- MySQL / MariaDB
- InnoDB
- utf8mb4

## Local development

- XAMPP
- Apache
- MySQL

Project nằm tại:

```text
C:\xampp\htdocs\Rop_Reng_pjwa
```

---

# 6. Kiến trúc thư mục

Kiến trúc hiện tại:

```text
Rop_Reng_pjwa/
│
├── admin/
│   ├── orders.php
│   └── order-detail.php
│
├── api/
│   ├── create-order.php
│   ├── orders.php
│   ├── order-detail.php
│   └── update-order-status.php
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── homepage.css
│   │   ├── menu.css
│   │   ├── checkout.css
│   │   └── admin.css
│   │
│   ├── js/
│   │   └── main.js
│   │
│   └── images/
│
├── auth/
│   └── auth.js
│
├── config/
│   └── db.php
│
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
│
├── pages/
│   ├── home.php
│   ├── menu.php
│   └── checkout.php
│
├── checkout.php
├── index.php
└── README.md
```

Cấu trúc có thể mở rộng khi cần, nhưng không thay đổi kiến trúc chỉ để “cho hiện đại”.

---

# 7. Design System

## Brand

**Rốp Rẻng – Cà phê & Giải khát**

Định hướng:

> Bold Urban Branding

Logo:

- Giữ nguyên logo gốc.
- Không tự ý redesign logo.

## Màu chủ đạo

```text
Deep Olive       #293706
Warm Ivory       #F5EEC9
Electric Lime    #D9E75B
Golden Yellow    #F2C75C
Charcoal         #24271C
```

## Visual direction

- Bold
- Urban
- Modern
- Có cá tính
- Dễ đọc
- Không quá rối
- Có khoảng thở
- Ưu tiên desktop
- Responsive cơ bản

Background homepage sử dụng phong cách dark olive kết hợp pattern/icon sáng hơn.

---

# 8. Homepage

Homepage hiện tại gồm:

```text
1. Hero
2. Category Section
3. Product / Featured Section
4. Footer
```

## Hero

- Logo / branding
- Hero visual
- CTA dẫn tới Menu

Không tạo thêm CTA dư thừa nếu đã có Menu trên Navbar.

## Category

Category được lấy từ Database.

Không hard-code danh mục nếu Database đã có dữ liệu.

## Product section

Sản phẩm phải lấy từ Database.

Các flag:

```text
is_best_seller
is_new
is_seasonal
```

được thiết kế để hỗ trợ logic hiển thị.

Không tự ý đánh dấu sản phẩm là:

- Best Seller
- New
- Seasonal

nếu chưa có dữ liệu xác nhận.

---

# 9. Navbar

Navbar hiện tại gồm:

```text
TRANG CHỦ
MENU
VỀ RỐP RẺNG
GIỎ HÀNG
```

Giỏ hàng hiển thị số lượng.

Không thêm lại nút:

```text
XEM MENU
```

vào Header nếu nó chỉ trùng chức năng với Menu navigation.

---

# 10. Categories

Rốp Rẻng hiện có 7 category:

| ID | Category | Slug |
|---:|---|---|
| 1 | Giải khát cà phê | `giaikhatcaphe` |
| 2 | Latte | `latte` |
| 3 | Nước giải khát | `nuocgiaikhat` |
| 4 | Phê đi | `phedi` |
| 5 | Quốc dân cà phê | `quocdancaphe` |
| 6 | Thong dong cà phê | `thongdongcaphe` |
| 7 | Trà sữa | `trasua` |

Category image nằm trong:

```text
assets/images/products/drinks/
```

---

# 11. Products

Database hiện có **30 sản phẩm thực tế**.

## Category 1 — Giải khát cà phê

1. Americano — 40k
2. Cà phê ủ lạnh Nitro nguyên vị — 45k
3. Cà phê ủ lạnh quýt nhài — 45k
4. Cà phê ủ lạnh đào mơ màng — 49k
5. Cà phê ủ lạnh chanh bạc hà — 45k
6. Cà phê ủ lạnh Nitro bưởi hồng — 49k

## Category 2 — Latte

7. Phê dừa lát-tê — 49k
8. Matcha lát-tê — 49k

## Category 3 — Nước giải khát

9. Nước Dừa tắc — 49k
10. Nước Dừa thạch đào — 49k
11. Trà quýt nhài tắc — 49k
12. Hồng trà sâm bí đao sương sáo hạt chia — 49k
13. Hồng trà sâm lài — 49k

## Category 4 — Phê đi

14. Phê Phin kem sữa Marble — 49k
15. Phê Phin caramel muối — 49k
16. Phê Phin choco — 49k
17. Phê Phin hạnh nhân — 49k

## Category 5 — Quốc dân cà phê

18. Cà phê đá — 30k
19. Cà phê sữa — 35k
20. Sữa tươi sương sáo cà phê — 38k
21. Cà phê muối — 38k
22. Cà phê phô mai — 35k
23. Cà phê bơ đậu phộng — 32k
24. Cà phê kem dẻo chuối — 32k

## Category 6 — Thong dong cà phê

25. Cà phê sữa sương mây — M 32k / L 45k
26. Cà phê mây — M 35k / L 49k

## Category 7 — Trà sữa

27. Trà sữa gạo rang Genmaicha — M 27k / L 30k
28. Trà sữa trân châu gạo rang Genmaicha — M 35k / L 39k
29. Trà sữa oolong lài — M 35k / L 39k
30. Trà sữa oolong lài cốm xanh — M 45k / L 49k

---

# 12. Product variants

V1 hỗ trợ product variants.

Hiện tại:

```text
36 variants
```

gồm:

```text
24 Default
12 M/L
```

6 sản phẩm có M/L:

```text
Cà phê sữa sương mây
Cà phê mây
Trà sữa gạo rang Genmaicha
Trà sữa trân châu gạo rang Genmaicha
Trà sữa oolong lài
Trà sữa oolong lài cốm xanh
```

Các sản phẩm còn lại sử dụng:

```text
Default
```

## Quy tắc

`products.price`:

- là giá base/default.
- với sản phẩm M/L hiện tại, lưu giá M.

Giá thực tế khi đặt hàng phải lấy từ variant hợp lệ nếu sản phẩm có variant.

---

# 13. Topping

Mô hình đã thống nhất cho Rốp Rẻng:

> **Topping là add-on, không phải category sản phẩm độc lập.**

Tức là không tạo category:

```text
Topping
```

trong 7 category đồ uống.

Mô hình dữ liệu dự kiến:

```text
toppings
product_toppings
```

Topping được chọn bổ sung vào sản phẩm và có thể có surcharge.

Nếu topping chưa được triển khai trong code hiện tại:

> Không giả định rằng tính năng đã hoàn thành.

Chỉ triển khai khi scope của phiên làm việc yêu cầu.

---

# 14. Database

Database:

```text
rop_reng
```

Engine:

```text
InnoDB
```

Charset:

```text
utf8mb4
```

---

# 15. Bảng products

Schema nền tảng:

```sql
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_seasonal TINYINT(1) NOT NULL DEFAULT 0,
    season_start DATE DEFAULT NULL,
    season_end DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);
```

## Quy tắc phân loại sản phẩm V1

### DRINKS

- `is_seasonal = 0`
- `category_id` bắt buộc có giá trị.
- `season_start = NULL`.
- `season_end = NULL`.
- Sản phẩm thuộc một trong 7 category cố định.

### SEASON PRODUCTS

- `is_seasonal = 1`.
- `category_id = NULL`.
- `season_start` bắt buộc có giá trị.
- `season_end` bắt buộc có giá trị.
- `season_start <= season_end`.
- Chỉ được hiển thị và kinh doanh trong khoảng ngày đã thiết lập.

### TOPPINGS

- Topping là add-on, không phải category sản phẩm.
- Không đưa Topping vào bảng `categories` như một danh mục đồ uống.
- Quản lý và lựa chọn topping thuộc phạm vi V2.

### Nguyên tắc dữ liệu

- Không cho phép DRINKS thiếu category.
- Không cho phép SEASON PRODUCTS có category.
- Không cho phép sản phẩm có ngày mùa vụ không hợp lệ.
- API phải kiểm tra dữ liệu ở server-side.

Không tự tạo description giả cho sản phẩm nếu chưa có dữ liệu.

---

# 16. Bảng product_variants

```sql
CREATE TABLE product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_variants_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    INDEX idx_product_variants_product (product_id),
    INDEX idx_product_variants_active (product_id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
```

## Quy tắc Product Variants V1

Mỗi sản phẩm phải có một trong hai cấu trúc:

### Default

- Có đúng một variant tên `Default`.
- Giá variant bằng `products.price`.

### M/L

- Có đúng hai variant tên `M` và `L`.
- `products.price` bằng giá variant `M`.
- Giá variant `L` được lưu riêng.

Không cho phép một sản phẩm đồng thời có `Default` và `M/L`.
Không cho phép trùng tên variant trong cùng một sản phẩm.

Giá sử dụng khi đặt hàng phải được lấy từ variant hợp lệ.

---

# 17. Orders

V1 sử dụng:

```text
orders