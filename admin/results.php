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

$userId = $_GET['user_id'] ?? null;

if ($userId) {
    // Просмотр результатов конкретного пользователя
    $stmt = $pdo->prepare("
        SELECT u.*, tr.* 
        FROM users u
        JOIN test_results tr ON u.id = tr.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['message'] = 'Пользователь не найден';
        $_SESSION['message_type'] = 'danger';
        header('Location: users.php');
        exit;
    }
    
    $answers = getUserAnswers($user['id']);
    
    include '../includes/header.php';
    ?>
    
    <div class="container">
        <h1 class="my-4">Результаты тестирования</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Информация о пользователе</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ФИО:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                        <p><strong>Должность:</strong> <?= htmlspecialchars($user['position']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Подразделение:</strong> <?= htmlspecialchars($user['department']) ?></p>
                        <p><strong>Филиал:</strong> <?= htmlspecialchars($user['branch']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
    <div class="card-header">
        <h5>Общие результаты</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Дата завершения:</strong> <?= date('d.m.Y H:i', strtotime($user['end_time'])) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Результат:</strong> 
                    <span class="badge <?= $user['score'] >= PASSING_SCORE ? 'bg-success' : 'bg-danger' ?>">
                        <?= $user['score'] ?> из <?= QUESTIONS_PER_TEST ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>
        
        <div class="card">
            <div class="card-header">
                <h5>Детальные ответы</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Вопрос</th>
                                <th>Ответ</th>
                                <th>Правильный ответ</th>
                                <th>Результат</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($answers as $answer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($answer['question_text']) ?></td>
                                    <td>
                                        <?php if ($answer['answer'] == 1): ?>
                                            <?= htmlspecialchars($answer['option1']) ?>
                                        <?php elseif ($answer['answer'] == 2): ?>
                                            <?= htmlspecialchars($answer['option2']) ?>
                                        <?php elseif ($answer['answer'] == 3): ?>
                                            <?= htmlspecialchars($answer['option3']) ?>
                                        <?php elseif ($answer['answer'] == 4): ?>
                                            <?= htmlspecialchars($answer['option4']) ?>
                                        <?php else: ?>
                                            Нет ответа
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($answer['correct_option'] == 1): ?>
                                            <?= htmlspecialchars($answer['option1']) ?>
                                        <?php elseif ($answer['correct_option'] == 2): ?>
                                            <?= htmlspecialchars($answer['option2']) ?>
                                        <?php elseif ($answer['correct_option'] == 3): ?>
                                            <?= htmlspecialchars($answer['option3']) ?>
                                        <?php elseif ($answer['correct_option'] == 4): ?>
                                            <?= htmlspecialchars($answer['option4']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($answer['is_correct']): ?>
                                            <span class="badge bg-success">Правильно</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Неправильно</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="users.php" class="btn btn-secondary">Назад к списку</a>
            <a href="export.php?type=user_results&user_id=<?= $userId ?>" class="btn btn-success">Экспорт в Excel</a>
        </div>
    </div>
    
    <?php
} else {
    // Список всех результатов
    $query = "
        SELECT u.id, u.full_name, u.position, u.department, u.branch, 
               tr.score, tr.start_time, tr.end_time 
        FROM users u
        JOIN test_results tr ON u.id = tr.user_id
        WHERE tr.status = 'completed'
        ORDER BY tr.end_time DESC
    ";
    
    $results = $pdo->query($query)->fetchAll();
    
    include '../includes/header.php';
    ?>
    
    <div class="container">
        <h1 class="my-4">Результаты тестирования</h1>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Все завершенные тесты</h5>
                <a href="export.php?type=all_results" class="btn btn-success">Экспорт в Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Должность</th>
                                <th>Подразделение</th>
                                <th>Филиал</th>
                                <th>Дата завершения</th>
                                <th>Результат</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?= htmlspecialchars($result['full_name']) ?></td>
                                    <td><?= htmlspecialchars($result['position']) ?></td>
                                    <td><?= htmlspecialchars($result['department']) ?></td>
                                    <td><?= htmlspecialchars($result['branch']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($result['end_time'])) ?></td>
                                    <td>
                                        <span class="badge <?= $result['score'] >= PASSING_SCORE ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $result['score'] ?>/<?= QUESTIONS_PER_TEST ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="results.php?user_id=<?= $result['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Подробнее
                                        </a>
                                        <form action="reset_test.php" method="POST" style="display:inline;" data-confirm="Вы уверены, что хотите сбросить результаты теста для этого пользователя?">
                                            <input type="hidden" name="user_id" value="<?= $result['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-redo"></i> Сбросить
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}

include '../includes/footer.php';
?>