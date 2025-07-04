<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

// Получаем параметры фильтрации из GET-запроса
$filterName = $_GET['name'] ?? '';
$filterPosition = $_GET['position'] ?? '';
$filterDepartment = $_GET['department'] ?? '';
$filterBranch = $_GET['branch'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterScore = $_GET['score'] ?? '';

// Формируем SQL-запрос с учетом фильтров
$query = "
    SELECT 
        u.id,
        u.full_name,
        u.position,
        u.department,
        u.branch,
        u.email,
        u.registration_date,
        tr.status,
        tr.start_time,
        tr.end_time,
        tr.score,
        CASE 
            WHEN tr.status = 'completed' AND tr.score >= ? THEN 'Пройден'
            WHEN tr.status = 'completed' AND tr.score < ? THEN 'Не пройден'
            WHEN tr.status = 'in_progress' THEN 'В процессе'
            ELSE 'Не начат'
        END as test_status
    FROM users u
    LEFT JOIN test_results tr ON u.id = tr.user_id AND tr.id = (
        SELECT MAX(id) FROM test_results WHERE user_id = u.id
    )
    WHERE 1=1
";

$params = [PASSING_SCORE, PASSING_SCORE];

// Добавляем условия фильтрации
if (!empty($filterName)) {
    $query .= " AND u.full_name LIKE ?";
    $params[] = "%$filterName%";
}

if (!empty($filterPosition)) {
    $query .= " AND u.position = ?";
    $params[] = $filterPosition;
}

if (!empty($filterDepartment)) {
    $query .= " AND u.department = ?";
    $params[] = $filterDepartment;
}

if (!empty($filterBranch)) {
    $query .= " AND u.branch = ?";
    $params[] = $filterBranch;
}

if (!empty($filterStatus)) {
    switch ($filterStatus) {
        case 'passed':
            $query .= " AND tr.status = 'completed' AND tr.score >= ?";
            $params[] = PASSING_SCORE;
            break;
        case 'failed':
            $query .= " AND tr.status = 'completed' AND tr.score < ?";
            $params[] = PASSING_SCORE;
            break;
        case 'in_progress':
            $query .= " AND tr.status = 'in_progress'";
            break;
        case 'not_started':
            $query .= " AND (tr.status IS NULL OR tr.status = 'not_started')";
            break;
    }
}

if (!empty($filterScore)) {
    $query .= " AND tr.score = ?";
    $params[] = $filterScore;
}

$query .= " ORDER BY u.full_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Получаем уникальные значения для выпадающих списков
$positions = $pdo->query("SELECT DISTINCT position FROM users WHERE position != '' ORDER BY position")->fetchAll(PDO::FETCH_COLUMN);
$departments = $pdo->query("SELECT DISTINCT department FROM users WHERE department != '' ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
$branches = $pdo->query("SELECT DISTINCT branch FROM users WHERE branch != '' ORDER BY branch")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1 class="my-4">Панель администратора</h1>
    
    <!-- Форма фильтрации -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Фильтр сотрудников</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="name" class="form-label">ФИО</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($filterName) ?>" placeholder="Поиск по имени">
                </div>
                
                <div class="col-md-2">
                    <label for="position" class="form-label">Должность</label>
                    <select class="form-select" id="position" name="position">
                        <option value="">Все должности</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?= htmlspecialchars($position) ?>" <?= $filterPosition === $position ? 'selected' : '' ?>>
                                <?= htmlspecialchars($position) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="department" class="form-label">Подразделение</label>
                    <select class="form-select" id="department" name="department">
                        <option value="">Все подразделения</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= htmlspecialchars($department) ?>" <?= $filterDepartment === $department ? 'selected' : '' ?>>
                                <?= htmlspecialchars($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="branch" class="form-label">Филиал</label>
                    <select class="form-select" id="branch" name="branch">
                        <option value="">Все филиалы</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= htmlspecialchars($branch) ?>" <?= $filterBranch === $branch ? 'selected' : '' ?>>
                                <?= htmlspecialchars($branch) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Статус теста</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Все статусы</option>
                        <option value="passed" <?= $filterStatus === 'passed' ? 'selected' : '' ?>>Пройден</option>
                        <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Не пройден</option>
                        <option value="in_progress" <?= $filterStatus === 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                        <option value="not_started" <?= $filterStatus === 'not_started' ? 'selected' : '' ?>>Не начат</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label for="score" class="form-label">Баллы</label>
                    <input type="number" class="form-control" id="score" name="score" value="<?= htmlspecialchars($filterScore) ?>" placeholder="Баллы" min="0" max="<?= QUESTIONS_PER_TEST ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Применить фильтр</button>
                    <a href="users.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Таблица с результатами -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Результаты тестирования сотрудников</h3>
            <div>
                <span class="badge bg-primary me-2">Всего: <?= count($users) ?></span>
                <a href="export.php?type=all_results" class="btn btn-success btn-sm">Экспорт в Excel</a>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="usersTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>ФИО</th>
                            <th>Должность</th>
                            <th>Подразделение</th>
                            <th>Филиал</th>
                            <th>Email</th>
                            <th>Дата регистрации</th>
                            <th>Статус теста</th>
                            <th>Завершение теста</th>
                            <th>Результат</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($user['full_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['position'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['department'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['branch'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td><?= isset($user['registration_date']) ? date('d.m.Y', strtotime($user['registration_date'])) : '-' ?></td>
                            <td>
                                <?php if (isset($user['test_status'])): ?>
                                <span class="badge 
                                    <?= $user['test_status'] == 'Пройден' ? 'bg-success' : 
                                       ($user['test_status'] == 'Не пройден' ? 'bg-danger' : 
                                       ($user['test_status'] == 'В процессе' ? 'bg-warning' : 'bg-secondary')) ?>">
                                    <?= $user['test_status'] ?>
                                </span>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= isset($user['end_time']) ? date('d.m.Y H:i', strtotime($user['end_time'])) : '-' ?>
                            </td>
                            <td>
                                <?= isset($user['score']) ? 
                                    $user['score'] . ' из ' . QUESTIONS_PER_TEST . 
                                    ' (' . round(($user['score'] / QUESTIONS_PER_TEST) * 100) . '%)' : 
                                    '-' ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (isset($user['status']) && $user['status'] == 'completed'): ?>
                                        <a href="results.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-info" title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <form action="reset_test.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Сбросить результаты">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                    
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Удалить" <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/ru.json'
        },
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        pageLength: 25,
        columnDefs: [
            {
                targets: -1, // Последняя колонка (Действия)
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>

<?php 
include __DIR__ . '/../includes/footer.php';
?>