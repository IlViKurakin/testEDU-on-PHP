<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdmin();

$positions = ['Менеджер', 'Преподаватель', 'Администратор', 'Директор'];
$departments = ['Продажи', 'Обучение', 'Администрация', 'Маркетинг'];
$branches = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань'];

$userId = $_GET['id'] ?? 0;
$user = null;

if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['message'] = 'Пользователь не найден';
        $_SESSION['message_type'] = 'danger';
        header('Location: users.php');
        exit;
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $position = $_POST['position'];
    $department = $_POST['department'];
    $branch = $_POST['branch'];
    $email = trim($_POST['email']);
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Валидация
    $errors = [];
    
    if (empty($fullName)) {
        $errors[] = 'Введите имя и фамилию';
    }
    
    if (!in_array($position, $positions)) {
        $errors[] = 'Выберите должность из списка';
    }
    
    if (!in_array($department, $departments)) {
        $errors[] = 'Выберите подразделение из списка';
    }
    
    if (!in_array($branch, $branches)) {
        $errors[] = 'Выберите филиал из списка';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    } elseif (substr($email, -15) !== '@top-academy.ru') {
        $errors[] = 'Email должен оканчиваться на @top-academy.ru';
    }
    
    if (empty($errors)) {
        if ($userId) {
            // Обновление пользователя
            $stmt = $pdo->prepare("
                UPDATE users 
                SET full_name = ?, position = ?, department = ?, branch = ?, email = ?, is_admin = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $fullName, $position, $department, $branch, $email, $isAdmin, $userId
            ]);
            
            $_SESSION['message'] = 'Данные пользователя успешно обновлены';
        } else {
            // Создание нового пользователя
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, position, department, branch, email, is_admin, registration_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $fullName, $position, $department, $branch, $email, $isAdmin
            ]);
            
            $_SESSION['message'] = 'Пользователь успешно создан';
        }
        
        $_SESSION['message_type'] = 'success';
        header('Location: users.php');
        exit;
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h1 class="my-4"><?= $userId ? 'Редактирование' : 'Создание' ?> пользователя</h1>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ФИО</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($user['full_name'] ?? $_POST['full_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Должность</label>
                            <select id="position" name="position" class="form-select" required>
                                <option value="">Выберите должность</option>
                                <?php foreach ($positions as $pos): ?>
                                    <option value="<?= $pos ?>" 
                                        <?= ($user['position'] ?? $_POST['position'] ?? '') === $pos ? 'selected' : '' ?>>
                                        <?= $pos ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Подразделение</label>
                            <select id="department" name="department" class="form-select" required>
                                <option value="">Выберите подразделение</option>
                                <?php foreach ($departments as $dep): ?>
                                    <option value="<?= $dep ?>" 
                                        <?= ($user['department'] ?? $_POST['department'] ?? '') === $dep ? 'selected' : '' ?>>
                                        <?= $dep ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="branch" class="form-label">Филиал</label>
                            <select id="branch" name="branch" class="form-select" required>
                                <option value="">Выберите филиал</option>
                                <?php foreach ($branches as $br): ?>
                                    <option value="<?= $br ?>" 
                                        <?= ($user['branch'] ?? $_POST['branch'] ?? '') === $br ? 'selected' : '' ?>>
                                        <?= $br ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '') ?>" 
                                   placeholder="user@top-academy.ru" required>
                        </div>
                        
                        <?php if ($userId != $_SESSION['user_id']): ?>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" 
                                       <?= ($user['is_admin'] ?? $_POST['is_admin'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_admin">Администратор</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="users.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>