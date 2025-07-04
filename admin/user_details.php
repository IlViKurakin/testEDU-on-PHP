<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/functions.php';

// Проверка прав администратора
checkAdmin();

// Получаем ID пользователя
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Проверяем существование пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php?error=user_not_found');
    exit;
}

// Получаем результаты тестирования
$stmt = $pdo->prepare("
    SELECT id, start_time, end_time, score, status 
    FROM test_results 
    WHERE user_id = ?
    ORDER BY end_time DESC
");
$stmt->execute([$userId]);
$testResults = $stmt->fetchAll();

// Получаем детальные ответы
$answers = [];
if (!empty($testResults)) {
    $testId = $testResults[0]['id'];
    $stmt = $pdo->prepare("
        SELECT 
            q.id, q.category_id, q.question_text,
            q.option1, q.option2, q.option3, q.option4,
            q.correct_option,
            ua.answer AS user_answer
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        WHERE ua.result_id = ?
        ORDER BY q.category_id, q.id
    ");
    $stmt->execute([$testId]);
    $answers = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали пользователя | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card { margin-bottom: 20px; }
        .table-responsive { overflow-x: auto; }
        .badge { font-size: 0.9em; }
        .navbar { margin-bottom: 20px; }
    </style>
</head>
<body>
    <!-- Простая навигация без отдельного файла -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Админ-панель</a>
            <div class="d-flex">
                <a href="users.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2>Детальные ответы: <?= htmlspecialchars($user['full_name']) ?></h2>
                
                <!-- Информация о пользователе -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3"><strong>Должность:</strong> <?= htmlspecialchars($user['position']) ?></div>
                            <div class="col-md-3"><strong>Подразделение:</strong> <?= htmlspecialchars($user['department']) ?></div>
                            <div class="col-md-3"><strong>Филиал:</strong> <?= htmlspecialchars($user['branch']) ?></div>
                            <div class="col-md-3"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Результаты тестирования -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Результаты тестирования</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($testResults)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Тест</th>
                                            <th>Дата начала</th>
                                            <th>Дата завершения</th>
                                            <th>Баллы</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($testResults as $index => $result): ?>
                                            <tr>
                                                <td>Тест #<?= $index + 1 ?></td>
                                                <td><?= $result['start_time'] ? date('d.m.Y H:i', strtotime($result['start_time'])) : 'Н/Д' ?></td>
                                                <td><?= $result['end_time'] ? date('d.m.Y H:i', strtotime($result['end_time'])) : 'Н/Д' ?></td>
                                                <td><?= $result['score'] ?? '0' ?> из <?= QUESTIONS_PER_TEST ?></td>
                                                <td>
                                                    <?php switch($result['status']) {
                                                        case 'completed': echo '<span class="badge bg-success">Завершен</span>'; break;
                                                        case 'in_progress': echo '<span class="badge bg-warning">В процессе</span>'; break;
                                                        default: echo '<span class="badge bg-secondary">Не начат</span>';
                                                    } ?>
                                                </td>
                                                <td>
                                                    <a href="?user_id=<?= $userId ?>&test_id=<?= $result['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-search"></i> Показать
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Нет данных о тестировании</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Детальные ответы -->
                <?php if (!empty($answers)): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Детальные ответы</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Категория</th>
                                            <th>Вопрос</th>
                                            <th>Ответ пользователя</th>
                                            <th>Правильный ответ</th>
                                            <th>Результат</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($answers as $i => $answer): 
                                            $isCorrect = $answer['user_answer'] == $answer['correct_option'];
                                            $userAnswerText = $answer['option'.$answer['user_answer']] ?? 'Нет ответа';
                                            $correctAnswerText = $answer['option'.$answer['correct_option']] ?? 'Нет данных';
                                        ?>
                                            <tr class="<?= $isCorrect ? 'table-success' : 'table-danger' ?>">
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars(getCategoryName($answer['category_id'])) ?></td>
                                                <td><?= htmlspecialchars($answer['question_text']) ?></td>
                                                <td><?= htmlspecialchars($userAnswerText) ?></td>
                                                <td><?= htmlspecialchars($correctAnswerText) ?></td>
                                                <td>
                                                    <?php if ($isCorrect): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Правильно</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Неправильно</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif (!empty($testResults)): ?>
                    <div class="alert alert-warning">Нет данных об ответах для выбранного теста</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>