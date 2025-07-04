<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdmin();

// Получаем точную статистику по пользователям и тестам
$stats = $pdo->query("
    SELECT 
        COUNT(DISTINCT u.id) as total_users,
        SUM(CASE WHEN tr.status = 'not_started' OR tr.id IS NULL THEN 1 ELSE 0 END) as not_started,
        SUM(CASE WHEN tr.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN tr.status = 'completed' AND tr.score >= " . PASSING_SCORE . " THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN tr.status = 'completed' AND tr.score < " . PASSING_SCORE . " THEN 1 ELSE 0 END) as failed
    FROM users u
    LEFT JOIN (
        SELECT id, user_id, status, score
        FROM test_results
        WHERE id IN (SELECT MAX(id) FROM test_results GROUP BY user_id)
    ) tr ON u.id = tr.user_id
")->fetch();

// Получаем последние завершенные тесты
$recentTests = $pdo->query("
    SELECT u.full_name, tr.score, tr.end_time 
    FROM test_results tr
    JOIN users u ON tr.user_id = u.id
    WHERE tr.status = 'completed'
    ORDER BY tr.end_time DESC
    LIMIT 5
")->fetchAll();

include '../includes/header.php';
?>

<div class="container">
    <h1 class="my-4">Административная панель</h1>
    
    <div class="row">
        <!-- Карточка: Всего пользователей -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Всего пользователей</h5>
                    <p class="card-text display-4"><?= $stats['total_users'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Карточка: Тест не начат -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Тест не начат</h5>
                    <p class="card-text display-4"><?= $stats['not_started'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Карточка: В процессе -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">В процессе</h5>
                    <p class="card-text display-4"><?= $stats['in_progress'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Карточка: Успешно прошли -->
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Успешно прошли</h5>
                    <p class="card-text display-4"><?= $stats['passed'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Карточка: Не прошли -->
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Не прошли</h5>
                    <p class="card-text display-4"><?= $stats['failed'] ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Быстрые действия</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="users.php" class="list-group-item list-group-item-action">
                            Управление пользователями
                        </a>
                        <a href="results.php" class="list-group-item list-group-item-action">
                            Просмотр результатов тестирования
                        </a>
                        <a href="questions.php" class="list-group-item list-group-item-action">
                            Управление вопросами
                        </a>
                        <a href="export_results.php" class="list-group-item list-group-item-action">
                            Экспорт результатов
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Последние завершенные тесты</h5>
                </div>
                <div class="card-body">
                    <?php if ($recentTests): ?>
                        <div class="list-group">
                            <?php foreach ($recentTests as $test): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($test['full_name']) ?></strong>
                                        <span class="badge badge-<?= $test['score'] >= PASSING_SCORE ? 'success' : 'danger' ?>">
                                            <?= $test['score'] ?>/<?= QUESTIONS_PER_TEST ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($test['end_time'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Нет завершенных тестов</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>