<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager', 'employee'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['role'] === 'admin') {
    header("Location: manage_users.php");
    exit();
}
try {
    $total_motorcycles = $pdo->query("SELECT COUNT(*) FROM motorcycles")->fetchColumn();
    if ($_SESSION['role'] === 'employee') {
        $emp_id = (int)$_SESSION['user_id'];
        $total_rentals = $pdo->query("SELECT COUNT(*) FROM rentals WHERE employee_id IS NULL OR employee_id = $emp_id")->fetchColumn();
    } else {
        $total_rentals = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
    }
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}
render_header();
?>
<div class="row mb-4 mt-3">
    <div class="col-12">
        <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i>Bảng Điều Khiển (Dashboard)</h2>
        <p class="text-muted">Xin chào <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Chào mừng bạn quay trở lại trang quản trị.</p>
    </div>
</div>
<div class="row g-4 mb-5">
    <?php if ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'employee'): ?>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow-sm border-0 h-100 rounded-3 position-relative overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="z-index: 2;">
                        <h6 class="text-uppercase fw-bold opacity-75 mb-1">Tổng số xe máy</h6>
                        <h2 class="display-4 fw-bold mb-0"><?= $total_motorcycles ?></h2>
                    </div>
                    <i class="bi bi-motorcycle position-absolute end-0 bottom-0 opacity-25" style="font-size: 7rem; transform: translate(10px, 20px);"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                <a href="manage_motorcycles.php" class="btn btn-outline-light btn-sm fw-bold">
                    Quản lý Xe <i class="bi bi-arrow-right-circle ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-dark bg-warning shadow-sm border-0 h-100 rounded-3 position-relative overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="z-index: 2;">
                        <h6 class="text-uppercase fw-bold opacity-75 mb-1">Tổng số đơn thuê</h6>
                        <h2 class="display-4 fw-bold mb-0"><?= $total_rentals ?></h2>
                    </div>
                    <i class="bi bi-card-checklist position-absolute end-0 bottom-0 opacity-25" style="font-size: 7rem; transform: translate(10px, 20px);"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                <a href="manage_rentals.php" class="btn btn-dark btn-sm fw-bold">
                    Quản lý Đơn thuê <i class="bi bi-arrow-right-circle ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="col-md-4">
        <div class="card text-white bg-primary shadow-sm border-0 h-100 rounded-3 position-relative overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="z-index: 2;">
                        <h6 class="text-uppercase fw-bold opacity-75 mb-1">Tổng số người dùng</h6>
                        <h2 class="display-4 fw-bold mb-0"><?= $total_users ?></h2>
                    </div>
                    <i class="bi bi-people position-absolute end-0 bottom-0 opacity-25" style="font-size: 7rem; transform: translate(10px, 20px);"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                <a href="manage_users.php" class="btn btn-light text-primary btn-sm fw-bold">
                    Quản lý Người dùng <i class="bi bi-person-gear ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
