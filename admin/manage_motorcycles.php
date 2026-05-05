<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'employee')) {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $license_plate = trim($_POST['license_plate']);
    $category_id = $_POST['category_id'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];
    $condition_state = $_POST['condition_state'];
    $description = trim($_POST['description']);
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO motorcycles (name, license_plate, category_id, price_per_day, status, condition_state, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $license_plate, $category_id, $price_per_day, $status, $condition_state, $description]);
            $success = "Thêm xe máy mới thành công!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) $error = "Lỗi: Biển số xe này đã tồn tại.";
            else $error = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $delete_id = (int)$_POST['delete_id'];
    try {
        $check_stmt = $pdo->prepare("SELECT status FROM motorcycles WHERE id = ?");
        $check_stmt->execute([$delete_id]);
        $bike_status = $check_stmt->fetchColumn();
        if ($bike_status === 'rented') {
            $error = "Không thể xóa xe này vì đang có khách thuê!";
        } else {
            $del_stmt = $pdo->prepare("DELETE FROM motorcycles WHERE id = ?");
            $del_stmt->execute([$delete_id]);
            $success = "Đã xóa xe thành công khỏi hệ thống!";
        }
    } catch (PDOException $e) {
        $error = "Không thể xóa xe này vì nó đang liên kết với dữ liệu Đơn Thuê.";
    }
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$motorcycles_query = "
    SELECT m.*, c.name as category_name 
    FROM motorcycles m 
    LEFT JOIN categories c ON m.category_id = c.id 
    ORDER BY m.created_at DESC
";
$motorcycles = $pdo->query($motorcycles_query)->fetchAll();
render_header();
?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-motorcycle text-success me-2"></i>Quản lý Kho Xe</h2>
    <?php if ($_SESSION['role'] === 'manager'): ?>
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addFormCollapse">
        <i class="bi bi-plus-lg me-1"></i> Nhập xe mới
    </button>
    <?php endif; ?>
</div>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
<?php endif; ?>
<?php if ($_SESSION['role'] === 'manager'): ?>
<div class="collapse mb-4" id="addFormCollapse">
    <div class="card card-body shadow-sm bg-light">
        <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">Khai báo thông tin xe</h5>
        <form action="manage_motorcycles.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Tên xe</label>
                    <input type="text" name="name" class="form-control" required placeholder="Honda Vision 2023">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Biển số</label>
                    <input type="text" name="license_plate" class="form-control" required placeholder="29A1-12345">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Danh mục xe</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Giá thuê / Ngày (VNĐ)</label>
                    <input type="number" name="price_per_day" class="form-control" required min="0" step="1000">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Trạng thái xe</label>
                    <select name="status" class="form-select">
                        <option value="available" selected>Sẵn sàng cho thuê</option>
                        <option value="rented">Đang cho thuê</option>
                        <option value="maintenance">Đang bảo dưỡng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Tình trạng</label>
                    <select name="condition_state" class="form-select">
                        <option value="mới" selected>Mới</option>
                        <option value="cũ">Cũ</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold text-muted">Ghi chú / Mô tả</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Tình trạng xe..."></textarea>
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#addFormCollapse">Hủy</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Lưu thông tin</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Tên Xe</th>
                        <th>Biển Số</th>
                        <th>Loại</th>
                        <th>Tình Trạng</th>
                        <th>Giá Thuê</th>
                        <th>Trạng Thái</th>
                        <th class="text-center pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($motorcycles) > 0): ?>
                        <?php foreach ($motorcycles as $bike): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $bike['id'] ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($bike['name']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($bike['license_plate']) ?></span></td>
                                <td><?= htmlspecialchars($bike['category_name']) ?></td>
                                <td>
                                    <?php if($bike['condition_state'] == 'mới'): ?>
                                        <span class="badge bg-info text-dark"><i class="bi bi-star-fill me-1"></i>Mới</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-star-half me-1"></i>Cũ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-danger fw-bold"><?= number_format($bike['price_per_day'], 0, ',', '.') ?> đ</td>
                                <td>
                                    <?php 
                                        if($bike['status'] == 'available') echo '<span class="badge bg-success">Sẵn sàng</span>';
                                        elseif($bike['status'] == 'rented') echo '<span class="badge bg-warning text-dark">Đang thuê</span>';
                                        else echo '<span class="badge bg-danger">Bảo dưỡng</span>';
                                    ?>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($bike['status'] == 'available'): ?>
                                            <a href="add_rental.php?bike_id=<?= $bike['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-plus"></i> Tạo Đơn
                                            </a>
                                        <?php elseif ($bike['status'] == 'rented'): ?>
                                            <button class="btn btn-sm btn-warning text-dark border-0" disabled><i class="bi bi-person-fill-lock"></i> Đang cho thuê</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary border-0" disabled><i class="bi bi-tools"></i> Đang bảo dưỡng</button>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['role'] === 'manager'): ?>
                                            <?php if ($bike['status'] === 'rented'): ?>
                                                <button class="btn btn-sm btn-secondary" disabled title="Không thể xóa xe đang cho thuê">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <form action="manage_motorcycles.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa xe biển số <?= htmlspecialchars($bike['license_plate']) ?> vĩnh viễn?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="delete_id" value="<?= $bike['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa xe này">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5">Chưa có dữ liệu xe máy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
