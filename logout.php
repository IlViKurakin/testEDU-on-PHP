<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Выход пользователя
logout();

// Перенаправление на главную страницу с сообщением
$_SESSION['message'] = 'Вы успешно вышли из системы';
$_SESSION['message_type'] = 'success';
header('Location: index.php');
exit;