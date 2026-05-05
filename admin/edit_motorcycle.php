<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';
$bike_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bike_id === 0) {
    header("Location: manage_motorcycles.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $name = trim($_POST['name']);
    $license_plate = trim($_POST['license_plate']);
    $category_id = $_POST['category_id'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];
    $description = trim($_POST['description']);
    $image = $_POST['old_image']; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $filename;
                if (!empty($_POST['old_image'])) {
                    $old_file_path = $upload_dir . $_POST['old_image'];
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
            } else {
                $error = "Lỗi khi lưu file ảnh mới lên máy chủ.";
            }
        } else {
            $error = "Chỉ cho phép tải lên các định dạng ảnh hợp lệ (JPG, PNG, GIF, WEBP).";
        }
    }
    if (empty($error)) {
        try {
            $sql = "UPDATE motorcycles SET name = ?, license_plate = ?, category_id = ?, price_per_day = ?, status = ?, description = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $license_plate, $category_id, $price_per_day, $status, $description, $image, $bike_id]);
            $success = "Cập nhật thông tin xe máy thành công!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Lỗi: Biển số xe này đã được đăng ký cho một xe khác.";
            } else {
                $error = "Lỗi hệ thống CSDL: " . $e->getMessage();
            }
        }
    }
}
$stmt = $pdo->prepare("SELECT * FROM motorcycles WHERE id = ?");
$stmt->execute([$bike_id]);
$bike = $stmt->fetch();
if (!$bike) {
    header("Location: manage_motorcycles.php");
    exit();
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
render_header();
?>
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Thông Tin Xe</h2>
        <a href="manage_motorcycles.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
    <?php endif; ?>
    <div class="card shadow-sm border-0 bg-white">
        <div class="card-body p-4">
            <form action="edit_motorcycle.php?id=<?= $bike_id ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($bike['image']) ?>">
                <div class="row g-4">
                    <div class="col-md-4 text-center border-end">
                        <label class="form-label fw-bold text-muted w-100 text-start">Ảnh hiện tại</label>
                        <?php 
                            $img_path = '../assets/images/' . $bike['image'];
                            if (!empty($bike['image']) && file_exists($img_path)): 
                        ?>
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="Ảnh xe" class="img-fluid rounded border mb-3 bg-light" style="max-height: 220px; object-fit: contain; width: 100%; padding: 10px;">
                        <?php else: ?>
                            <div class="border rounded d-flex align-items-center justify-content-center mb-3 bg-light" style="height: 220px; width: 100%;">
                                <span class="text-muted"><i class="bi bi-image fs-1 d-block"></i>Chưa có ảnh hợp lệ</span>
                            </div>
                        <?php endif; ?>
                        <div class="text-start">
                            <label class="form-label fw-bold text-primary">Thay đổi ảnh mới (Tùy chọn)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Chỉ chọn file khi bạn muốn đổi ảnh khác.</small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Tên dòng xe</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($bike['name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Biển kiểm soát</label>
                                <input type="text" name="license_plate" class="form-control" required value="<?= htmlspecialchars($bike['license_plate']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Danh mục xe</label>
                                <select name="category_id" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $bike['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Giá thuê 1 ngày (VNĐ)</label>
                                <input type="number" name="price_per_day" class="form-control" required min="0" step="1000" value="<?= htmlspecialchars($bike['price_per_day']) ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-muted">Tình trạng phục vụ</label>
                                <select name="status" class="form-select">
                                    <option value="available" <?= ($bike['status'] == 'available') ? 'selected' : '' ?>>Sẵn sàng cho thuê (Trống)</option>
                                    <option value="rented" <?= ($bike['status'] == 'rented') ? 'selected' : '' ?>>Đang cho thuê (Bận)</option>
                                    <option value="maintenance" <?= ($bike['status'] == 'maintenance') ? 'selected' : '' ?>>Đang sửa chữa / Bảo dưỡng</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-muted">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Tình trạng trầy xước, số km đã đi..."><?= htmlspecialchars($bike['description']) ?></textarea>
                            </div>
                            <div class="col-12 text-end mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold"><i class="bi bi-save me-2"></i> Lưu Cập Nhật Xe</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php render_footer(); ?>
