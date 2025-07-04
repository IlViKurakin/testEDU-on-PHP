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

// Очистка буфера вывода
if (ob_get_level()) {
    ob_end_clean();
}

$exportType = $_GET['type'] ?? '';

// Полный маппинг категорий
$categoryMap = [
    1 => 'МКА+ПШ',
    2 => 'Школьник-Студент',
    3 => 'TOP Travel',
    4 => 'ПКО',
    5 => 'Каникулярные программы',
    6 => 'Школа',
    7 => 'ВУЗ',
    8 => 'Детский сад',
    9 => 'Спецкурсы',
    10 => 'Колледж'
];

// Заголовки для экспорта
$headers = [
    'user_results' => ['ID вопроса', 'Категория', 'Вопрос', 'Ответ пользователя', 'Правильный ответ', 'Результат'],
    'questions' => ['ID', 'Категория', 'Текст вопроса', 'Вариант 1', 'Вариант 2', 'Вариант 3', 'Вариант 4', 'Правильный вариант']
];

// Проверка типа экспорта
if (!isset($headers[$exportType])) {
    $_SESSION['message'] = 'Неверный тип экспорта';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Получение данных
$data = [];
$filename = '';

if ($exportType === 'user_results') {
    $userId = (int)$_GET['user_id'];
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['message'] = 'Пользователь не найден';
        $_SESSION['message_type'] = 'danger';
        header('Location: users.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT q.id, q.category_id, q.question_text, 
               q.option1, q.option2, q.option3, q.option4,
               q.correct_option, ua.answer, ua.is_correct
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        JOIN test_results tr ON ua.result_id = tr.id
        WHERE tr.user_id = ?
        ORDER BY q.category_id, q.id
    ");
    $stmt->execute([$userId]);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Формируем данные с текстовыми названиями категорий
    foreach ($rawData as $row) {
        $data[] = [
            $row['id'],
            $categoryMap[$row['category_id']] ?? 'Неизвестная категория',
            $row['question_text'],
            $row['answer'] ? $row['option'.$row['answer']] : 'Нет ответа',
            $row['option'.$row['correct_option']],
            $row['is_correct'] ? 'Правильно' : 'Неправильно'
        ];
    }

    $filename = 'Результаты_'.preg_replace('/[^a-z0-9]/i', '_', $user['full_name']).'_'.date('Y-m-d').'.csv';
} 
elseif ($exportType === 'questions') {
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY category_id, id");
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rawData as $row) {
        $data[] = [
            $row['id'],
            $categoryMap[$row['category_id']] ?? 'Неизвестная категория',
            $row['question_text'],
            $row['option1'],
            $row['option2'],
            $row['option3'],
            $row['option4'],
            $row['correct_option']
        ];
    }

    $filename = 'Вопросы_'.date('Y-m-d').'.csv';
}

// Настройка заголовков для скачивания
header('Content-Encoding: UTF-8');
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

// Вывод данных
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // BOM для UTF-8
fputcsv($output, $headers[$exportType], ';');

foreach ($data as $row) {
    fputcsv($output, $row, ';');
}

fclose($output);
exit;