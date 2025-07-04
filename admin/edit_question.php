<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdmin();

$questionId = $_GET['id'] ?? 0;
$question = null;

if ($questionId) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $question = $stmt->fetch();
    
    if (!$question) {
        $_SESSION['message'] = 'Вопрос не найден';
        $_SESSION['message_type'] = 'danger';
        header('Location: questions.php');
        exit;
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)$_POST['category_id'];
    $questionText = trim($_POST['question_text']);
    $option1 = trim($_POST['option1']);
    $option2 = trim($_POST['option2']);
    $option3 = trim($_POST['option3']);
    $option4 = trim($_POST['option4']);
    $correctOption = (int)$_POST['correct_option'];
    
    // Валидация
    $errors = [];
    
    if (empty($questionText)) {
        $errors[] = 'Введите текст вопроса';
    }
    
    if (empty($option1) || empty($option2) || empty($option3) || empty($option4)) {
        $errors[] = 'Все варианты ответов должны быть заполнены';
    }
    
    if ($correctOption < 1 || $correctOption > 4) {
        $errors[] = 'Выберите правильный вариант ответа';
    }
    
    if (empty($errors)) {
        if ($questionId) {
            // Обновление вопроса
            $stmt = $pdo->prepare("
                UPDATE questions 
                SET category_id = ?, question_text = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_option = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $categoryId, $questionText, $option1, $option2, $option3, $option4, $correctOption, $questionId
            ]);
            
            $_SESSION['message'] = 'Вопрос успешно обновлен';
        } else {
            // Создание нового вопроса
            $stmt = $pdo->prepare("
                INSERT INTO questions (category_id, question_text, option1, option2, option3, option4, correct_option)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $categoryId, $questionText, $option1, $option2, $option3, $option4, $correctOption
            ]);
            
            $_SESSION['message'] = 'Вопрос успешно создан';
        }
        
        $_SESSION['message_type'] = 'success';
        header('Location: questions.php');
        exit;
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

$categories = [
    1 => 'МКА+ПШ',
    2 => 'ШС',
    3 => 'Travel',
    4 => 'ПКО',
    5 => 'Каникулярные программы',
    6 => 'Школа',
    7 => 'ВУЗ',
    8 => 'Сад',
    9 => 'Спецкурсы',
    10 => 'Колледж'
];

include '../includes/header.php';
?>

<div class="container">
    <h1 class="my-4"><?= $questionId ? 'Редактирование' : 'Создание' ?> вопроса</h1>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="category_id" class="form-label">Категория</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= $id ?>" 
                                <?= ($question['category_id'] ?? $_POST['category_id'] ?? '') == $id ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="question_text" class="form-label">Текст вопроса</label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="3" required><?= 
                        htmlspecialchars($question['question_text'] ?? $_POST['question_text'] ?? '') 
                    ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Варианты ответов</label>
                    
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><?= $i ?></span>
                            <input type="text" name="option<?= $i ?>" class="form-control" 
                                   value="<?= htmlspecialchars($question['option'.$i] ?? $_POST['option'.$i] ?? '') ?>" required>
                            <div class="input-group-text">
                                <input class="form-check-input" type="radio" name="correct_option" 
                                       value="<?= $i ?>" <?= 
                                       ($question['correct_option'] ?? $_POST['correct_option'] ?? 0) == $i ? 'checked' : '' ?> required>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="questions.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>