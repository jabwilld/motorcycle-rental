<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'employee')) {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $rental_id = (int)$_POST['rental_id'];
    $motorcycle_id = (int)$_POST['motorcycle_id'];
    $new_status = $_POST['status'];
    $employee_id = $_SESSION['user_id']; 
    $can_modify = true;
    if ($_SESSION['role'] === 'employee') {
        $stmt_check = $pdo->prepare("SELECT employee_id FROM rentals WHERE id = ?");
        $stmt_check->execute([$rental_id]);
        $order = $stmt_check->fetch();
        if ($order && $order['employee_id'] !== null && $order['employee_id'] != $employee_id) {
            $can_modify = false;
        }
    }
    if (!$can_modify) {
        $error = "Lỗi: Bạn không có quyền thay đổi đơn hàng do nhân viên khác phụ trách!";
    } else {
        $allowed_statuses = ['pending', 'approved', 'active'];
        if (in_array($new_status, $allowed_statuses)) {
            try {
                $update_rental = $pdo->prepare("UPDATE rentals SET status = ?, employee_id = ? WHERE id = ?");
                $update_rental->execute([$new_status, $employee_id, $rental_id]);
                if ($new_status == 'pending' || $new_status == 'approved' || $new_status == 'active') {
                    $update_bike = $pdo->prepare("UPDATE motorcycles SET status = 'rented' WHERE id = ?");
                    $update_bike->execute([$motorcycle_id]);
                } 
                $success = "Cập nhật thành công trạng thái cho đơn thuê mã #$rental_id!";
            } catch (PDOException $e) {
                $error = "Đã xảy ra lỗi khi cập nhật: " . $e->getMessage();
            }
        } else {
            $error = "Lỗi: Trạng thái không hợp lệ!";
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $delete_id = (int)$_POST['delete_id'];
    $motorcycle_id = (int)$_POST['motorcycle_id'];
    $current_status = $_POST['current_status'];
    $current_user_id = $_SESSION['user_id'];
    $can_delete = true;
    if ($_SESSION['role'] === 'employee') {
        $stmt_check = $pdo->prepare("SELECT employee_id FROM rentals WHERE id = ?");
        $stmt_check->execute([$delete_id]);
        $order = $stmt_check->fetch();
        if ($order && $order['employee_id'] !== null && $order['employee_id'] != $current_user_id) {
            $can_delete = false;
        }
    }
    if (!$can_delete) {
        $error = "Lỗi: Bạn không có quyền xóa đơn hàng do nhân viên khác phụ trách!";
    } else {
        try {
            $del_stmt = $pdo->prepare("DELETE FROM rentals WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            $update_bike = $pdo->prepare("UPDATE motorcycles SET status = 'available' WHERE id = ?");
            $update_bike->execute([$motorcycle_id]);
            $success = "Đã xóa đơn hàng #$delete_id thành công!";
        } catch (PDOException $e) {
            $error = "Lỗi khi xóa đơn hàng: " . $e->getMessage();
        }
    }
}
$where_clause = "";
if ($_SESSION['role'] === 'employee') {
    $emp_id = (int)$_SESSION['user_id'];
    $where_clause = " WHERE r.employee_id IS NULL OR r.employee_id = $emp_id ";
}
$sql = "
    SELECT 
        r.id as rental_id, 
        r.start_date, 
        r.end_date, 
        r.total_price, 
        r.status, 
        r.created_at,
        r.motorcycle_id,
        c.full_name as customer_name,
        c.phone as customer_phone,
        m.name as motorcycle_name,
        m.license_plate,
        e.full_name as employee_name
    FROM rentals r
    INNER JOIN customers c ON r.customer_id = c.id             
    INNER JOIN motorcycles m ON r.motorcycle_id = m.id     
    LEFT JOIN users e ON r.employee_id = e.id              
    $where_clause
    ORDER BY r.created_at DESC
";
$rentals = $pdo->query($sql)->fetchAll();
render_header();
?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-card-checklist text-warning me-2"></i>Quản lý Đơn Thuê Xe</h2>
</div>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
<?php endif; ?>
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Xe thuê</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền</th>
                        <th class="text-center">Người xử lý</th>
                        <th class="text-center" style="width: 200px;">Trạng thái & Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rentals) > 0): ?>
                        <?php foreach ($rentals as $row): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $row['rental_id'] ?></td>
                                <td>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($row['customer_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($row['customer_phone']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['motorcycle_name']) ?></div>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['license_plate']) ?></span>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem;">Từ: <strong><?= date('d/m/Y', strtotime($row['start_date'])) ?></strong></div>
                                    <div style="font-size: 0.9rem;">Đến: <strong><?= date('d/m/Y', strtotime($row['end_date'])) ?></strong></div>
                                </td>
                                <td class="fw-bold text-danger">
                                    <?= number_format($row['total_price'], 0, ',', '.') ?> đ
                                </td>
                                <td class="text-center">
                                    <?php if(!empty($row['employee_name'])): ?>
                                        <span class="badge bg-secondary"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($row['employee_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small">Chờ xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-center">
                                    <form action="manage_rentals.php" method="POST" class="d-flex flex-column gap-2 mb-2">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="rental_id" value="<?= $row['rental_id'] ?>">
                                        <input type="hidden" name="motorcycle_id" value="<?= $row['motorcycle_id'] ?>">
                                        <?php 
                                            $is_disabled = ''; 
                                        ?>
                                        <select name="status" class="form-select form-select-sm" <?= $is_disabled ?>>
                                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>🟡 Chưa lấy xe (Chưa cọc)</option>
                                            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>🔵 Chưa lấy xe (Có cọc)</option>
                                            <option value="active" <?= $row['status'] == 'active' ? 'selected' : '' ?>>🟣 Đã lấy xe</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" <?= $is_disabled ?>>
                                            <i class="bi bi-save me-1"></i> Lưu
                                        </button>
                                    </form>
                                    <form action="manage_rentals.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng #<?= $row['rental_id'] ?> này không?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?= $row['rental_id'] ?>">
                                        <input type="hidden" name="motorcycle_id" value="<?= $row['motorcycle_id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $row['status'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Xóa đơn hàng này">
                                            <i class="bi bi-trash"></i> Xóa đơn
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5">Chưa có đơn thuê xe nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
