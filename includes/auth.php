<?php
require_once 'config.php';
require_once 'db.php';

// Функция для проверки аутентификации пользователя
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

// Функция для проверки административных прав
function checkAdmin() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || !$user['is_admin']) {
        $_SESSION['message'] = 'У вас нет прав для доступа к этой странице';
        $_SESSION['message_type'] = 'danger';
        header('Location: /profile.php');
        exit;
    }
}

// Функция для входа пользователя
function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        
        // Проверяем, является ли пользователь администратором
        $_SESSION['is_admin'] = ($user['email'] === 'admin@top-academy.ru');
        
        return true;
    }
    
    return false;
}

// Функция для выхода пользователя
function logout() {
    session_unset();
    session_destroy();
    session_start();
}

// Функция для получения текущего пользователя
function getCurrentUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Функция для проверки, прошел ли пользователь тест
function hasUserPassedTest($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT score 
        FROM test_results 
        WHERE user_id = ? AND status = 'completed'
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result && $result['score'] >= PASSING_SCORE;
}

// Функция для проверки, может ли пользователь пройти тест
function canUserTakeTest($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT status 
        FROM test_results 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    // Пользователь может пройти тест, если:
    // 1. У него нет записи о тесте (первый раз)
    // 2. Тест не завершен (в процессе)
    // 3. Тест завершен, но не пройден (можно разрешить пересдачу, если нужно)
    return !$result || 
           $result['status'] === 'not_started' || 
           $result['status'] === 'in_progress' ||
           ($result['status'] === 'completed' && $result['score'] < PASSING_SCORE);
}

?>