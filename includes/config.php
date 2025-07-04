<?php
// Настройки приложения
define('DB_HOST', 'localhost');
define('DB_NAME', 'testing');
define('DB_USER', 'root');
define('DB_PASS', '');

// Настройки теста
define('QUESTIONS_PER_TEST', 50);
define('PASSING_SCORE', 40);
define('QUESTIONS_DISTRIBUTION', [
    1 => 7, // МКА+ПШ
    2 => 5, // ШС
    3 => 3, // Travel
    4 => 7, // ПКО
    5 => 5, // Каникулярные программы
    6 => 5, // Школа
    7 => 3, // ВУЗ
    8 => 3, // Сад
    9 => 7, // Спецкурсы
    10 => 5 // Колледж
]);

session_start();
?>