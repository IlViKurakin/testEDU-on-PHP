<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$testResult = getUserTestResult($_SESSION['user_id']);

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Личный кабинет</h3>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Добро пожаловать, <?= htmlspecialchars($user['full_name']) ?>!</h5>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong>Должность:</strong> <?= htmlspecialchars($user['position']) ?></p>
                            <p><strong>Подразделение:</strong> <?= htmlspecialchars($user['department']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Филиал:</strong> <?= htmlspecialchars($user['branch']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Тестирование</h3>
                </div>
                <div class="card-body">
                    <?php if (!$testResult || $testResult['status'] === 'not_started'): ?>
                        <p>Вы еще не начинали прохождение теста.</p>
                        <a href="test/start.php" class="btn btn-primary">Начать тестирование</a>
                    <?php elseif ($testResult['status'] === 'in_progress'): ?>
    <p>Вы начали прохождение теста <?= date('d.m.Y H:i', strtotime($testResult['start_time'])) ?>, но еще не завершили его.</p>
                        <a href="test/question.php?n=<?= $_SESSION['current_question'] ?? 1 ?>" class="btn btn-primary">
                            Продолжить тестирование
                        </a>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <h5>Результаты тестирования</h5>
                            <p>Дата прохождения: <?= date('d.m.Y H:i', strtotime($testResult['end_time'])) ?></p>
                            <p>Результат: <?= $testResult['score'] ?> из <?= QUESTIONS_PER_TEST ?> правильных ответов</p>
                            <p>Статус: <?= $testResult['score'] >= PASSING_SCORE ? 'Пройдено успешно' : 'Тест не сдан' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Информация</h3>
                </div>
                <div class="card-body">
                    <p>Тест состоит из 50 вопросов по различным направлениям деятельности компании.</p>
                    <p>Для успешного прохождения теста необходимо ответить правильно как минимум на 40 вопросов (80%).</p>
                    <p>У вас есть только одна попытка для прохождения теста.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>