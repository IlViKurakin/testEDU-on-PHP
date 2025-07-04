<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Если пользователь уже авторизован - редирект
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Валидация
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } elseif (empty($password)) {
        $error = 'Введите пароль';
    } else {
        // Поиск пользователя в БД
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Проверка пароля
        if ($user && password_verify($password, $user['password_hash'])) {
            // Успешная авторизация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = (bool)$user['is_admin']; // Добавляем статус админа в сессию

            // Запись времени входа
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Редирект с сообщением
            $_SESSION['message'] = 'Вы успешно вошли в систему';
            $_SESSION['message_type'] = 'success';
            
            // Редирект в админку для администраторов
            if ($_SESSION['is_admin']) {
                header('Location: /admin/');
            } else {
                header('Location: profile.php');
            }
            exit;
        } else {
            $error = 'Неверный email или пароль';
            
            // Запись неудачной попытки входа (для безопасности)
            $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip, attempt_time) VALUES (?, ?, NOW())");
            $stmt->execute([$email, $_SERVER['REMOTE_ADDR']]);
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card login-card mt-5">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center mb-0">Вход в систему</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="login-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                       placeholder="user@top-academy.ru" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">Войти</button>
                        </div>
                        
                        <div class="text-center">
                            <a href="forgot-password.php" class="text-muted">Забыли пароль?</a>
                            <span class="mx-2">|</span>
                            <a href="register.php">Регистрация</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Показать/скрыть пароль
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Валидация формы
document.getElementById('login-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    if (!email.endsWith('@top-academy.ru')) {
        alert('Используйте корпоративную почту @top-academy.ru');
        e.preventDefault();
    }
});
</script>

<?php include 'includes/footer.php'; ?>