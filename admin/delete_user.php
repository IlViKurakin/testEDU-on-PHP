<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['id'];
    
    if ($userId == $_SESSION['user_id']) {
        $_SESSION['message'] = 'Вы не можете удалить свой собственный аккаунт';
        $_SESSION['message_type'] = 'danger';
        header('Location: users.php');
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // 1. Удаляем все ответы пользователя
        $stmt = $pdo->prepare("DELETE FROM user_answers WHERE result_id IN (SELECT id FROM test_results WHERE user_id = ?)");
        $stmt->execute([$userId]);
        
        // 2. Удаляем все результаты тестов пользователя
        $stmt = $pdo->prepare("DELETE FROM test_results WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // 3. Удаляем самого пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        
        $_SESSION['message'] = 'Пользователь и все его данные полностью удалены';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Ошибка при удалении: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header('Location: users.php');
    exit;
}