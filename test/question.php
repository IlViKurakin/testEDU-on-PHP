<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Проверяем, есть ли активный тест
if (!isset($_SESSION['test_questions'])) {
    header('Location: start.php');
    exit;
}

$questions = $_SESSION['test_questions'];
$totalQuestions = count($questions);
$currentQuestionNum = isset($_GET['n']) ? (int)$_GET['n'] : 1;

// Валидация номера вопроса
if ($currentQuestionNum < 1 || $currentQuestionNum > $totalQuestions) {
    header('Location: question.php?n=1');
    exit;
}

$currentQuestion = $questions[$currentQuestionNum - 1];

// Обработка ответа на предыдущий вопрос
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $_SESSION['user_answers'][$currentQuestionNum] = (int)$_POST['answer'];
    
    // Если это последний вопрос, сохраняем результаты
    if ($currentQuestionNum === $totalQuestions) {
        $score = saveTestResult(
            $_SESSION['user_id'],
            $questions,
            $_SESSION['user_answers']
        );
        
        // Очищаем сессию от данных теста
        unset($_SESSION['test_questions']);
        unset($_SESSION['user_answers']);
        unset($_SESSION['current_question']);
        
        // Перенаправляем на страницу результатов
        header('Location: result.php');
        exit;
    } else {
        // Переходим к следующему вопросу
        $_SESSION['current_question'] = $currentQuestionNum + 1;
        header('Location: question.php?n=' . ($currentQuestionNum + 1));
        exit;
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="progress mb-4">
        <div class="progress-bar" role="progressbar" 
             style="width: <?= ($currentQuestionNum / $totalQuestions) * 100 ?>%" 
             aria-valuenow="<?= $currentQuestionNum ?>" 
             aria-valuemin="1" 
             aria-valuemax="<?= $totalQuestions ?>">
            Вопрос <?= $currentQuestionNum ?> из <?= $totalQuestions ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?= htmlspecialchars($currentQuestion['question_text']) ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option1" value="1" required>
                        <label class="form-check-label" for="option1">
                            <?= htmlspecialchars($currentQuestion['option1']) ?>
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option2" value="2">
                        <label class="form-check-label" for="option2">
                            <?= htmlspecialchars($currentQuestion['option2']) ?>
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option3" value="3">
                        <label class="form-check-label" for="option3">
                            <?= htmlspecialchars($currentQuestion['option3']) ?>
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option4" value="4">
                        <label class="form-check-label" for="option4">
                            <?= htmlspecialchars($currentQuestion['option4']) ?>
                        </label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <?php if ($currentQuestionNum > 1): ?>
                        <a href="question.php?n=<?= $currentQuestionNum - 1 ?>" class="btn btn-secondary">Назад</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= $currentQuestionNum === $totalQuestions ? 'Завершить тест' : 'Далее' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>