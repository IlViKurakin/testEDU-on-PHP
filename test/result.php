<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Получаем результаты теста
$testResult = getUserTestResult($_SESSION['user_id']);

if (!$testResult || $testResult['status'] !== 'completed') {
    header('Location: ../profile.php');
    exit;
}

$score = $testResult['score'];
$totalQuestions = QUESTIONS_PER_TEST;
$percentage = round(($score / $totalQuestions) * 100);
$passed = $score >= PASSING_SCORE;

include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Результаты тестирования</h3>
        </div>
        <div class="card-body text-center">
            <div class="alert alert-success mb-4">
                Поздравляем с окончанием прохождения теста! Ознакомьтесь с результатом.
            </div>
            
            <div class="result-circle mx-auto mb-4">
                <svg width="200" height="200" viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#eee" stroke-width="20"/>
                    <circle cx="100" cy="100" r="90" fill="none" 
                            stroke="<?= $passed ? '#28a745' : '#dc3545' ?>" 
                            stroke-width="20" stroke-dasharray="565.48" 
                            stroke-dashoffset="<?= 565.48 * (1 - $percentage / 100) ?>"/>
                    <text x="100" y="110" text-anchor="middle" font-size="40" fill="#333">
                        <?= $percentage ?>%
                    </text>
                </svg>
            </div>
            
            <h4 class="<?= $passed ? 'text-success' : 'text-danger' ?>">
                <?= $passed ? 'Пройдено успешно' : 'Тест не сдан' ?>
            </h4>
            
            <p class="lead">
                Правильных ответов: <?= $score ?> из <?= $totalQuestions ?>
            </p>
            
            <p>
                <?= $passed ? 
                    'Вы успешно прошли тестирование. Поздравляем!' : 
                    'К сожалению, вы не набрали достаточное количество баллов. Попробуйте подготовиться лучше.' 
                ?>
            </p>
            
            <a href="../profile.php" class="btn btn-primary mt-3">Вернуться в личный кабинет</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>