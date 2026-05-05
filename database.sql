CREATE DATABASE IF NOT EXISTS motorcycle_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE motorcycle_rental;

-- Bảng Người dùng Nội Bộ (Nhân sự: Admin, Manager, Employee)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    role ENUM('manager', 'employee', 'admin') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Khách hàng (Chỉ dùng để lưu trữ thông tin giao dịch, không có tài khoản)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    cccd VARCHAR(20) DEFAULT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng Loại xe (Phân loại xe)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Bảng Xe máy
CREATE TABLE motorcycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    category_id INT,
    price_per_day DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
    condition_state ENUM('mới', 'cũ') DEFAULT 'mới', -- Thay thế cho cột ảnh, chỉ ra tình trạng xe
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng Đơn thuê xe (Hợp đồng thuê)
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    motorcycle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    employee_id INT, -- Nhân viên phụ trách xử lý/quản lý đơn
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (motorcycle_id) REFERENCES motorcycles(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Thêm dữ liệu mẫu người dùng (Mật khẩu đều là '123' dạng plain-text cho dễ test đồ án)
INSERT INTO users (username, password, full_name, email, phone, role) VALUES 
('admin', '123', 'Quản trị viên', 'admin@example.com', '0123456789', 'admin'),
('manager', '123', 'Quản lý Kho', 'manager@example.com', '0987654321', 'manager'),
('nhanvien1', '123', 'Nhân viên 01', 'nv1@example.com', '0912345678', 'employee'),
('nhanvien2', '123', 'Nhân viên 02', 'nv2@example.com', '0912345679', 'employee');

-- Thêm dữ liệu mẫu khách hàng
INSERT INTO customers (full_name, phone, cccd, address) VALUES 
('Trần Văn A', '0901234567', '001099012345', '123 Lò Đúc, Hà Nội'),
('Nguyễn Thị B', '0901234568', '001099012346', '456 Hai Bà Trưng, TP.HCM'),
('Lê Văn C', '0912233445', '001099012347', '789 Nguyễn Văn Cừ, Đà Nẵng'),
('Phạm Thị D', '0988776655', '001099012348', '321 Lê Lợi, Cần Thơ');

-- Thêm dữ liệu mẫu danh mục
INSERT INTO categories (name) VALUES ('Xe số'), ('Xe tay ga'), ('Xe côn tay');

-- Thêm dữ liệu mẫu xe máy (Bỏ ảnh, phân biệt bằng biển số và tình trạng cũ/mới)
INSERT INTO motorcycles (name, license_plate, category_id, price_per_day, status, condition_state, description) VALUES
('Honda Wave Alpha', '29A1-111.11', 1, 100000, 'available', 'mới', 'Xe số siêu tiết kiệm xăng, mới 100%.'),
('Honda Wave Alpha', '29A1-222.22', 1, 90000, 'available', 'cũ', 'Xe số tiết kiệm xăng, đã qua sử dụng nhưng còn rất tốt.'),
('Honda Wave Alpha', '29A1-333.33', 1, 100000, 'rented', 'mới', 'Xe số siêu tiết kiệm xăng.'),
('Honda Air Blade', '29B1-444.44', 2, 150000, 'available', 'mới', 'Xe tay ga cốp rộng, máy khỏe, kiểu dáng thể thao.'),
('Honda Air Blade', '29B1-555.55', 2, 120000, 'rented', 'cũ', 'Xe tay ga chạy cực bốc, bảo dưỡng thường xuyên.'),
('Honda Air Blade', '29B1-666.66', 2, 150000, 'available', 'mới', 'Màu đen nhám nam tính.'),
('Yamaha Exciter 150', '29C1-777.77', 3, 200000, 'available', 'mới', 'Xe côn tay mạnh mẽ, phù hợp đi phượt xa.'),
('Yamaha Exciter 150', '29C1-888.88', 3, 170000, 'maintenance', 'cũ', 'Xe đang bảo dưỡng thay nhớt định kỳ.'),
('Yamaha Grande', '29D1-999.99', 2, 140000, 'available', 'mới', 'Xe tay ga nữ tính, tiết kiệm xăng, cốp siêu rộng.');

-- Thêm dữ liệu mẫu đơn thuê xe (Rentals)
INSERT INTO rentals (customer_id, motorcycle_id, start_date, end_date, total_price, status, employee_id) VALUES
(1, 3, '2026-04-01', '2026-04-05', 500000, 'active', 3), -- Khách A thuê Wave (ID 3) do Nhân viên 1 phụ trách
(2, 5, '2026-04-10', '2026-04-15', 600000, 'active', 4), -- Khách B thuê Air Blade (ID 5) do Nhân viên 2 phụ trách
(3, 1, '2026-04-05', '2026-04-08', 300000, 'active', 3), -- Khách C thuê Wave (đã lấy xe)
(4, 7, '2026-04-20', '2026-04-23', 600000, 'pending', 3); -- Khách D đặt thuê Exciter (chưa lấy xe - chưa cọc)
