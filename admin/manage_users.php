<?php
require_once dirname(__DIR__) . '/core.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $redirect_url = (isset($_SESSION['role']) && $_SESSION['role'] === 'employee') ? 'index.php' : '../login.php';
    header("Location: " . $redirect_url);
    exit();
}

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $target_user_id = (int)$_POST['target_user_id'];
    if ($target_user_id === $_SESSION['user_id']) {
        $error = "Bạn không thể tự thay đổi quyền hoặc xóa chính tài khoản của mình!";
    } else {
        if ($_POST['action'] == 'delete_user') {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$target_user_id]);
                $success = "Đã xóa vĩnh viễn tài khoản ID #$target_user_id khỏi hệ thống!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Không thể xóa tài khoản này! Người này đang có đơn thuê xe hoặc đã từng xử lý đơn (ràng buộc dữ liệu).";
                } else {
                    $error = "Lỗi hệ thống: " . $e->getMessage();
                }
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $email, $phone, $role]);
        $success = "Tạo tài khoản $username thành công!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Lỗi: Tên đăng nhập hoặc Email đã tồn tại trong hệ thống!";
        } else {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
$sql = "SELECT id, username, full_name, email, phone, role, created_at FROM users ORDER BY role ASC, created_at DESC";
$users = $pdo->query($sql)->fetchAll();
render_header();
?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Quản lý Người Dùng</h2>
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addUserCollapse">
        <i class="bi bi-plus-lg me-1"></i> Thêm người dùng
    </button>
</div>
<div class="collapse mb-4" id="addUserCollapse">
    <div class="card card-body shadow-sm bg-light border-0">
        <h5 class="fw-bold border-bottom pb-2 mb-3 text-primary">Tạo tài khoản mới</h5>
        <form action="manage_users.php" method="POST">
            <input type="hidden" name="action" value="add_user">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mật khẩu</label>
                    <input type="text" name="password" class="form-control" required value="123">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Quyền hạn</label>
                    <select name="role" class="form-select" required>
                        <option value="customer">Khách hàng</option>
                        <option value="employee">Nhân viên</option>
                        <option value="manager">Quản lý</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tên đầy đủ</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-12 mt-3 text-end">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Tạo tài khoản</button>
                </div>
            </div>
        </form>
    </div>
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
                        <th class="ps-3">ID</th>
                        <th>Tên đầy đủ</th>
                        <th>Tên đăng nhập</th>
                        <th>Liên hệ</th>
                        <th>Ngày tạo</th>
                        <th class="text-center" style="width: 250px;">Vai trò (Quyền)</th>
                        <th class="text-center pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#<?= $u['id'] ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['username']) ?></span></td>
                            <td>
                                <div style="font-size: 0.9rem;"><i class="bi bi-envelope text-muted me-1"></i><?= htmlspecialchars($u['email']) ?></div>
                                <div style="font-size: 0.9rem;"><i class="bi bi-telephone text-muted me-1"></i><?= htmlspecialchars($u['phone']) ?></div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-center">
                                <?php 
                                    $role_badge = 'bg-secondary';
                                    $role_text = 'Khách hàng';
                                    if ($u['role'] == 'admin') { $role_badge = 'bg-danger'; $role_text = 'Admin'; }
                                    if ($u['role'] == 'manager') { $role_badge = 'bg-warning text-dark'; $role_text = 'Manager'; }
                                    if ($u['role'] == 'employee') { $role_badge = 'bg-info text-dark'; $role_text = 'Nhân viên'; }
                                ?>
                                <span class="badge <?= $role_badge ?> p-2 fs-6"><?= $role_text ?></span>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Sửa thông tin">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <form action="manage_users.php" method="POST" onsubmit="return confirm('Bạn có CHẮC CHẮN muốn xóa tài khoản <?= htmlspecialchars($u['username']) ?> không? Toàn bộ dữ liệu của người này có thể sẽ bị ảnh hưởng.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa tài khoản này">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light text-muted border" disabled><i class="bi bi-lock-fill"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
