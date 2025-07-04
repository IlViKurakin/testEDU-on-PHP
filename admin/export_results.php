<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';

checkAdmin();

// Устанавливаем заголовки для скачивания CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=test_results_'.date('Y-m-d').'.csv');

// Открываем поток вывода
$output = fopen('php://output', 'w');

// Заголовки CSV
fputcsv($output, [
    'ID пользователя',
    'ФИО',
    'Должность',
    'Подразделение',
    'Филиал',
    'Дата начала',
    'Дата завершения',
    'Результат (баллы)',
    'Статус',
    'ID вопроса',
    'Категория вопроса',
    'Вопрос',
    'Ответ пользователя',
    'Правильный ответ',
    'Результат'
], ';');

// Получаем данные
$query = "
    SELECT 
        u.id AS user_id,
        u.full_name,
        u.position,
        u.department,
        u.branch,
        tr.start_time,
        tr.end_time,
        tr.score,
        tr.status,
        q.id AS question_id,
        q.category_id,
        q.question_text,
        ua.answer AS user_answer,
        q.correct_option,
        CASE WHEN ua.answer = q.correct_option THEN 'Правильно' ELSE 'Неправильно' END AS answer_result
    FROM test_results tr
    JOIN users u ON tr.user_id = u.id
    JOIN user_answers ua ON ua.result_id = tr.id
    JOIN questions q ON ua.question_id = q.id
    ORDER BY tr.end_time DESC, u.full_name
";

$stmt = $pdo->query($query);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['user_id'],
        $row['full_name'],
        $row['position'],
        $row['department'],
        $row['branch'],
        $row['start_time'],
        $row['end_time'],
        $row['score'],
        $row['status'],
        $row['question_id'],
        $row['category_id'],
        $row['question_text'],
        $row['user_answer'],
        $row['correct_option'],
        $row['answer_result']
    ], ';');
}

fclose($output);
exit;