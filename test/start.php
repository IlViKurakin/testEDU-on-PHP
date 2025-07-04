<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    // Проверка авторизации
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Пользователь не авторизован');
    }

    $userId = $_SESSION['user_id'];

    // Проверка соединения с БД
    $pdo->query("SELECT 1")->execute();

    // Получаем или создаем запись о тесте
    $stmt = $pdo->prepare("SELECT * FROM test_results WHERE user_id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $userId]);
    $testResult = $stmt->fetch();

    // Если тест завершен - редирект
    if ($testResult && $testResult['status'] === 'completed') {
        header('Location: ../profile.php');
        exit;
    }

    // Если записи нет - создаем
    if (!$testResult) {
        $stmt = $pdo->prepare("INSERT INTO test_results (user_id, status) VALUES (:user_id, 'not_started')");
        if (!$stmt->execute([':user_id' => $userId])) {
            throw new Exception('Не удалось создать запись о тесте');
        }
    }

    // Генерируем вопросы
    $questions = getRandomQuestions();
    if (empty($questions)) {
        throw new Exception('Не удалось получить вопросы для теста');
    }

    // Сохраняем в сессию
    $_SESSION['test_questions'] = $questions;
    $_SESSION['user_answers'] = [];
    $_SESSION['current_question'] = 1;

    // Обновляем статус теста
    $stmt = $pdo->prepare("UPDATE test_results SET status = 'in_progress', start_time = NOW() WHERE user_id = :user_id");
if (!$stmt->execute([':user_id' => $userId])) {
    throw new Exception('Не удалось обновить статус теста');
}

    header('Location: question.php?n=1');
    exit;

} catch (PDOException $e) {
    die('Ошибка базы данных: ' . $e->getMessage());
} catch (Exception $e) {
    die('Произошла ошибка: ' . $e->getMessage());
}
?>