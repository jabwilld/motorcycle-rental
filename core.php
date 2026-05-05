<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

$host = 'localhost';
$dbname = 'motorcycle_rental';
$username = 'root'; 
$password = '';     
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối Cơ sở dữ liệu: " . $e->getMessage());
}

function check_login() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    return false;
}
function check_admin() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true;
    }
    return false;
}
function check_employee() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'employee') {
        return true;
    }
    return false;
}

function render_header() {
    $base_url = '/motorcycle-rental/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thuê Xe Máy MotoRental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_url ?>index.php">
                <i class="bi bi-motorcycle me-2"></i>MotoRental
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'manage_users.php') !== false) ? 'active' : '' ?>" href="/motorcycle-rental/admin/manage_users.php"><i class="bi bi-people-fill"></i> Quản lý Nhân sự</a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'employee')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'manage_motorcycles.php') !== false) ? 'active' : '' ?>" href="/motorcycle-rental/admin/manage_motorcycles.php"><i class="bi bi-motorcycle"></i> Quản lý Xe</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'manage_rentals.php') !== false) ? 'active' : '' ?>" href="/motorcycle-rental/admin/manage_rentals.php"><i class="bi bi-card-checklist"></i> Quản lý Đơn Thuê</a>
                            </li>
                        <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                Xin chào, <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></strong>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <span class="dropdown-item-text text-muted small">
                                        Vai trò: <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?= $base_url ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light fw-bold px-3 mt-2 mt-lg-0" href="<?= $base_url ?>login.php"><i class="bi bi-shield-lock me-1"></i> Đăng Nhập Nội Bộ</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container min-vh-100">
<?php
}

function render_footer() {
?>
    </div> 
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <h5 class="mb-3 text-uppercase fw-bold text-warning">Hệ thống Thuê Xe Máy MotoRental</h5>
            <p class="small text-white-50 mb-0 mt-3">
                &copy; <?= date('Y') ?> Bản quyền thuộc về MotoRental. Mọi quyền được bảo lưu.
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/motorcycle-rental/assets/js/script.js"></script>
</body>
</html>
<?php
}
?>
