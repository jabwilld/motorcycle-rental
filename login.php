<?php
require_once __DIR__ . '/core.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: admin/manage_users.php");
        } else {
            header("Location: admin/index.php");
        }
        exit();
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không chính xác!";
    }
}
render_header();
?>
<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-5">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-dark text-white text-center py-4 rounded-top-4">
                <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>ĐĂNG NHẬP NỘI BỘ</h4>
            </div>
            <div class="card-body p-5">
                <?php if ($error != ''): ?>
                    <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold">Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control form-control-lg bg-light" placeholder="Nhập username..." required>
                    </div>
                    <div class="mb-5">
                        <label class="form-label text-muted fw-bold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control form-control-lg bg-light" placeholder="Nhập mật khẩu..." required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow"><i class="bi bi-box-arrow-in-right me-2"></i>Truy cập Hệ thống</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
