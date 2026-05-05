<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id === 0) {
    header("Location: manage_users.php");
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$target_user = $stmt->fetch();
if (!$target_user) {
    echo "<script>alert('Tài khoản không tồn tại!'); window.location.href='manage_users.php';</script>";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    try {
        $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $update_stmt->execute([$full_name, $email, $phone, $user_id]);
        $success = "Cập nhật thông tin tài khoản thành công! Tự động quay lại sau 2 giây...";
        $target_user['full_name'] = $full_name;
        $target_user['email'] = $email;
        $target_user['phone'] = $phone;
        echo "<meta http-equiv='refresh' content='2;url=manage_users.php'>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Lỗi: Email này đã được sử dụng bởi một tài khoản khác!";
        } else {
            $error = "Lỗi khi cập nhật: " . $e->getMessage();
        }
    }
}
render_header();
?>
<div class="container mt-4 mb-5" style="max-width: 600px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Thông Tin</h2>
        <a href="manage_users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Quay lại</a>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
    <?php endif; ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="edit_user.php?id=<?= $user_id ?>" method="POST">
                <input type="hidden" name="action" value="edit_user">
                <div class="mb-3">
                    <label class="form-label text-muted fw-bold">Tên đăng nhập (Không thể đổi)</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($target_user['username']) ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên đầy đủ</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($target_user['full_name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($target_user['email']) ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($target_user['phone']) ?>">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php render_footer(); ?>
