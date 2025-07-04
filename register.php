<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$positions = ['Региональный директор','Региональный МУП','Директор','Сити-менеджер','МОП','РОП','МУП','РУЧ'];
$departments = ['ЦУП','Академия','Шамбала','Колледж','Школа','ВУЗ','Сад'];
$branches = ['Абакан','Абинск','Адлер','Альметьевск','Анапа','Ангарск','Апшеронск','Армавир','Архангельск','Астрахань','Балаково','Балашиха','Барнаул','Батайск','Белгород','Березники','Бийск','Благовещенск','Братск','Брянск','Бузулук','Великие Луки','Великий Новгород','Видное','Владивосток','Владикавказ','Владимир','Волгоград','Волгоград Окружной','Волгодонск','Волжский','Вологда','Воронеж','Воронеж Окружной','Воскресенск','Всеволожск','Выборг','Гатчина','Геленджик','Горячий Ключ','Грозный','Дербент','Дзержинск','Димитровград','Дмитров','Долгопрудный','Домодедово','Дубна','Евпатория','Егорьевск','Ейск','Екатеринбург','Екатеринбург Окружной','Железногорск','Железнодорожный','Жуковский','Зеленоград','Златоуст','Иваново','Ижевск','Иркутск','Истра','Йошкар-Ола','Казань','Калининград','Калуга','Каменск-Уральский','Камышин','Каспийск','Кемерово','Керчь','Кингисепп','Кириши','Киров','Кисловодск','Ковров','Коломна','Колпино','Комсомольск-на-Амуре','Копейск','Королёв','Кострома','Красногорск','Краснодар','Краснодар Окружной','Красноярск','Курган','Курск','Лабинск','Липецк','Лыткарино','Люберцы','Магнитогорск','Майкоп','Махачкала','Междуреченск','Миасс','Мичуринск','Москва м. Академическая','Москва м. Алексеевская','Москва м. Беляево','Москва м. Бибирево','Москва м. Домодедовская','Москва м. Жулебино','Москва м. Кутузовская','Москва м. Марьино','Москва м. Митино','Москва м. Новаторская','Москва м. Первомайская','Москва м. Перово','Москва м. Преображенская площадь','Москва м. Севастопольская','Москва м. Сокол','Москва м. Тимирязевская','Москва м. Тушинская','Москва м. Ховрино','Москва м. Шаболовская','Москва м. Юго-Западная','Мурманск','Муром','Мытищи','Набережные Челны','Назрань','Нальчик','Наро-Фоминск','Находка','Невинномысск','Нефтекамск','Нижневартовск','Нижнекамск','Нижний Новгород','Нижний Новгород Окружной','Нижний Тагил','Новокузнецк','Новокуйбышевск','Новомосковск','Новороссийск','Новосибирск','Новосибирск Окружной','Новочеркасск','Ногинск','Ноябрьск','Обнинск','Одинцово','Омск','Омск Окружной','Орел','Оренбург','Орехово-Зуево','Орск','Павловский Посад','Пенза','Первоуральск','Пермь','Пермь Окружной','Петрозаводск','Петропавловск-Камчатский','Подольск','Прокопьевск','Псков','Пушкин','Пушкино','Пятигорск','Реутов','Ростов-на-Дону','Рубцовск','Рыбинск','Рязань','Салават','Самара','Санкт-Петербург м. Академическая','Санкт-Петербург м. Беговая','Санкт-Петербург м. Дыбенко','Санкт-Петербург м. Итальянская','Санкт-Петербург м. Кировский завод','Санкт-Петербург м. Московская','Санкт-Петербург м. Пролетарская','Санкт-Петербург м. Удельная','Санкт-Петербург м. Шушары','Саранск','Саратов','Саратов Окружной','Саров','Светлоград','Севастополь','Северодвинск','Сергиев Посад','Серов','Серпухов','Симферополь','Смоленск','Сосновый Бор','Сочи','Ставрополь','Старый Оскол','Стерлитамак','Ступино','Сургут','Сызрань','Сыктывкар','Таганрог','Тамбов','Тверь','Тихвин','Тобольск','Тольятти','Томск','Туапсе','Тула','Тюмень','Улан-Удэ','Ульяновск','Ульяновск Окружной','Уссурийск','Уфа','Уфа Окружной','Хабаровск','Хасавюрт','Химки','Чебоксары','Челябинск','Челябинск Окружной','Череповец','Черкесск','Чехов','Чита','Шамбала','Шатура','Шахты','Щелково','Электросталь','Элиста','Энгельс','Южно-Сахалинск','Якутск','Ярославль'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $position = $_POST['position'];
    $department = $_POST['department'];
    $branch = $_POST['branch'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Валидация данных
    if (empty($fullName)) {
        $errors['full_name'] = 'Введите имя и фамилию';
    }
    
    if (!in_array($position, $positions)) {
        $errors['position'] = 'Выберите должность из списка';
    }
    
    if (!in_array($department, $departments)) {
        $errors['department'] = 'Выберите подразделение из списка';
    }
    
    if (!in_array($branch, $branches)) {
        $errors['branch'] = 'Выберите филиал из списка';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Введите адрес электронной почты';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный адрес электронной почты';
    } elseif (substr($email, -15) !== '@top-academy.ru') {
        $errors['email'] = 'Адрес электронной почты должен оканчиваться на @top-academy.ru';
    } else {
        // Проверка на уникальность email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Этот адрес электронной почты уже зарегистрирован';
        }
    }
    
    if (empty($password)) {
        $errors['password'] = 'Введите пароль';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Пароль должен содержать хотя бы одну заглавную букву';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Пароль должен содержать хотя бы одну цифру';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        // Хеширование пароля
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Регистрация пользователя
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, position, department, branch, email, password_hash, registration_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$fullName, $position, $department, $branch, $email, $passwordHash]);
            $userId = $pdo->lastInsertId();

            // После успешной регистрации
            $_SESSION['is_admin'] = 0; // По умолчанию новый пользователь не админ
            
            // Создаем запись о тесте
            $stmt = $pdo->prepare("
                INSERT INTO test_results (user_id, status)
                VALUES (?, 'not_started')
            ");
            $stmt->execute([$userId]);
            
            // Завершаем транзакцию
            $pdo->commit();
            
            // Автоматический вход
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['message'] = 'Регистрация прошла успешно!';
            $_SESSION['message_type'] = 'success';
            header('Location: profile.php');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors['database'] = 'Ошибка при регистрации: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="my-4">Регистрация</h1>
    
    <?php if (isset($errors['database'])): ?>
        <div class="alert alert-danger"><?= $errors['database'] ?></div>
    <?php endif; ?>
    
    <form method="POST" id="registration-form">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="full_name" class="form-label">Имя и фамилия</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="text-danger small"><?= $errors['full_name'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="position" class="form-label">Должность</label>
                    <select id="position" name="position" class="form-select" required>
                        <option value="">Выберите должность</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?= $pos ?>" <?= ($_POST['position'] ?? '') === $pos ? 'selected' : '' ?>>
                                <?= $pos ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['position'])): ?>
                        <div class="text-danger small"><?= $errors['position'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="department" class="form-label">Подразделение</label>
                    <select id="department" name="department" class="form-select" required>
                        <option value="">Выберите подразделение</option>
                        <?php foreach ($departments as $dep): ?>
                            <option value="<?= $dep ?>" <?= ($_POST['department'] ?? '') === $dep ? 'selected' : '' ?>>
                                <?= $dep ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['department'])): ?>
                        <div class="text-danger small"><?= $errors['department'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="branch" class="form-label">Филиал</label>
                    <select id="branch" name="branch" class="form-select" required>
                        <option value="">Выберите филиал</option>
                        <?php foreach ($branches as $br): ?>
                            <option value="<?= $br ?>" <?= ($_POST['branch'] ?? '') === $br ? 'selected' : '' ?>>
                                <?= $br ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['branch'])): ?>
                        <div class="text-danger small"><?= $errors['branch'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Адрес электронной почты</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="user@top-academy.ru" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div id="password-strength" class="small"></div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-danger small"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="text-danger small"><?= $errors['confirm_password'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            <a href="login.php" class="btn btn-link">Уже есть аккаунт? Войти</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthText = document.getElementById('password-strength');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length === 0) {
            strengthText.textContent = '';
            strengthText.className = '';
            return;
        }
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^A-Za-z0-9]/)) strength++;
        
        const messages = ['Очень слабый', 'Слабый', 'Средний', 'Сильный', 'Очень сильный'];
        const colors = ['text-danger', 'text-danger', 'text-warning', 'text-success', 'text-success'];
        
        strengthText.textContent = `Надежность пароля: ${messages[strength]}`;
        strengthText.className = `small ${colors[strength]}`;
    });
});
</script>

<?php include 'includes/footer.php'; ?>