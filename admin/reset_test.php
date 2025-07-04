<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Удаляем все ответы пользователя
        $stmt = $pdo->prepare("DELETE FROM user_answers WHERE result_id IN (SELECT id FROM test_results WHERE user_id = ?)");
        $stmt->execute([$userId]);
        
        // 2. Удаляем все результаты тестов пользователя
        $stmt = $pdo->prepare("DELETE FROM test_results WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // 3. Создаем новую запись с начальным статусом
        $stmt = $pdo->prepare("INSERT INTO test_results (user_id, status) VALUES (?, 'not_started')");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        
        $_SESSION['message'] = 'Результаты теста полностью сброшены';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Ошибка при сбросе результатов: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'users.php'));
    exit;
}