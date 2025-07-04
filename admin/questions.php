<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверка прав администратора
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /index.php');
    exit;
}

// Обработка добавления вопроса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
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
        $stmt = $pdo->prepare("
            INSERT INTO questions (category_id, question_text, option1, option2, option3, option4, correct_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $categoryId,
            $questionText,
            $option1,
            $option2,
            $option3,
            $option4,
            $correctOption
        ]);
        
        $_SESSION['message'] = 'Вопрос успешно добавлен';
        $_SESSION['message_type'] = 'success';
        header('Location: questions.php');
        exit;
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

// Обработка удаления вопроса
if (isset($_GET['delete'])) {
    $questionId = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    
    $_SESSION['message'] = 'Вопрос успешно удален';
    $_SESSION['message_type'] = 'success';
    header('Location: questions.php');
    exit;
}

// Получаем список всех вопросов
$questions = $pdo->query("
    SELECT q.*, 
           CASE q.category_id
               WHEN 1 THEN 'МКА+ПШ'
               WHEN 2 THEN 'ШС'
               WHEN 3 THEN 'Travel'
               WHEN 4 THEN 'ПКО'
               WHEN 5 THEN 'Каникулярные программы'
               WHEN 6 THEN 'Школа'
               WHEN 7 THEN 'ВУЗ'
               WHEN 8 THEN 'Сад'
               WHEN 9 THEN 'Спецкурсы'
               WHEN 10 THEN 'Колледж'
           END as category_name
    FROM questions q
    ORDER BY q.category_id, q.id
")->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <h1 class="my-4">Управление вопросами</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Добавить новый вопрос</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="1">МКА+ПШ</option>
                                <option value="2">ШС</option>
                                <option value="3">Travel</option>
                                <option value="4">ПКО</option>
                                <option value="5">Каникулярные программы</option>
                                <option value="6">Школа</option>
                                <option value="7">ВУЗ</option>
                                <option value="8">Сад</option>
                                <option value="9">Спецкурсы</option>
                                <option value="10">Колледж</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Текст вопроса</label>
                            <textarea id="question_text" name="question_text" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Варианты ответов</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text">1</span>
                                <input type="text" name="option1" class="form-control" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="correct_option" value="1" required>
                                </div>
                            </div>
                            
                            <div class="input-group mb-2">
                                <span class="input-group-text">2</span>
                                <input type="text" name="option2" class="form-control" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="correct_option" value="2">
                                </div>
                            </div>
                            
                            <div class="input-group mb-2">
                                <span class="input-group-text">3</span>
                                <input type="text" name="option3" class="form-control" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="correct_option" value="3">
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <span class="input-group-text">4</span>
                                <input type="text" name="option4" class="form-control" required>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="radio" name="correct_option" value="4">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_question" class="btn btn-primary">Добавить вопрос</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Статистика вопросов</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Категория</th>
                                <th>Количество</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $categoryCounts = $pdo->query("
                                SELECT category_id, COUNT(*) as count 
                                FROM questions 
                                GROUP BY category_id
                                ORDER BY category_id
                            ")->fetchAll();
                            
                            foreach ($categoryCounts as $category): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $categoryNames = [
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
                                        echo $categoryNames[$category['category_id']];
                                        ?>
                                    </td>
                                    <td><?= $category['count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td><strong>Всего:</strong></td>
                                <td><strong><?= count($questions) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Список всех вопросов</h5>
            <a href="export.php?type=questions" class="btn btn-success">Экспорт в Excel</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Категория</th>
                            <th>Вопрос</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?= $question['id'] ?></td>
                                <td><?= $question['category_name'] ?></td>
                                <td><?= htmlspecialchars(mb_substr($question['question_text'], 0, 100)) ?>...</td>
                                <td>
                                    <a href="edit_question.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Редактировать
                                    </a>
                                    <a href="questions.php?delete=<?= $question['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Вы уверены, что хотите удалить этот вопрос?')">
                                        <i class="fas fa-trash"></i> Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>