<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'employee')) {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';
$bike_id = isset($_GET['bike_id']) ? (int)$_GET['bike_id'] : 0;
if ($bike_id === 0) {
    header("Location: manage_motorcycles.php");
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM motorcycles WHERE id = ? AND status = 'available'");
$stmt->execute([$bike_id]);
$bike = $stmt->fetch();
if (!$bike) {
    echo "<script>alert('Xe này không tồn tại hoặc đang không sẵn sàng để thuê!'); window.location.href='manage_motorcycles.php';</script>";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_rental') {
    $customer_id = (int)$_POST['customer_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $employee_id = $_SESSION['user_id'];
    $ngay_bat_dau_giay = strtotime($start_date);
    $ngay_ket_thuc_giay = strtotime($end_date);
    if ($ngay_ket_thuc_giay < $ngay_bat_dau_giay) {
        $error = "Lỗi: Ngày trả xe không được nhỏ hơn ngày nhận xe!";
    } else {
        $so_ngay_thue = (($ngay_ket_thuc_giay - $ngay_bat_dau_giay) / 86400) + 1;
        $tong_tien = $so_ngay_thue * $bike['price_per_day'];
        try {
            $pdo->beginTransaction();
            $sql = "INSERT INTO rentals (customer_id, motorcycle_id, start_date, end_date, total_price, status, employee_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$customer_id, $bike_id, $start_date, $end_date, $tong_tien, $status, $employee_id]);
            $sql_update_bike = "UPDATE motorcycles SET status = 'rented' WHERE id = ?";
            $stmt2 = $pdo->prepare($sql_update_bike);
            $stmt2->execute([$bike_id]);
            $pdo->commit();
            $success = "Đã tạo đơn thuê xe thành công! Bạn có thể xem trong Quản lý Đơn Thuê.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Lỗi khi tạo đơn: " . $e->getMessage();
        }
    }
}
$customers = $pdo->query("SELECT id, full_name, phone FROM customers ORDER BY full_name ASC")->fetchAll();
render_header();
?>
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-file-earmark-plus text-success me-2"></i>Tạo Đơn Thuê Mới</h2>
        <a href="manage_motorcycles.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Quay lại</a>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white fw-bold">Thông tin Xe được chọn</div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="mb-4 text-secondary">
                        <i class="bi bi-motorcycle display-1"></i>
                    </div>
                    <h4 class="text-primary fw-bold"><?= htmlspecialchars($bike['name']) ?></h4>
                    <p class="mb-2">
                        <span class="badge bg-light text-dark border fs-6 me-2"><?= htmlspecialchars($bike['license_plate']) ?></span>
                        <?php if($bike['condition_state'] == 'mới'): ?>
                            <span class="badge bg-info text-dark fs-6"><i class="bi bi-star-fill me-1"></i>Mới</span>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6"><i class="bi bi-star-half me-1"></i>Cũ</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-danger fw-bold fs-4 mt-3 mb-0"><?= number_format($bike['price_per_day'], 0, ',', '.') ?> đ / Ngày</p>
                </div>
            </div>
        </div>
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <form action="add_rental.php?bike_id=<?= $bike_id ?>" method="POST">
                        <input type="hidden" name="action" value="add_rental">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted">1. Chọn Khách hàng</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Chọn khách hàng trong hệ thống --</option>
                                <?php foreach($customers as $cus): ?>
                                    <option value="<?= $cus['id'] ?>"><?= htmlspecialchars($cus['full_name']) ?> (SĐT: <?= htmlspecialchars($cus['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted fst-italic">Lưu ý: Phải thêm khách hàng mới vào CSDL thì mới xuất hiện ở đây.</small>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">2. Ngày nhận xe</label>
                                <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">3. Ngày trả xe</label>
                                <input type="date" name="end_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-muted">4. Trạng thái hiện tại</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Đã lấy xe (Mang xe đi ngay)</option>
                                    <option value="approved">Chưa lấy xe (Đã đặt cọc giữ xe)</option>
                                    <option value="pending">Chưa lấy xe (Chưa cọc - Chỉ giữ chỗ tạm)</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg fw-bold"><i class="bi bi-plus-circle me-2"></i> Xác nhận Tạo Đơn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
