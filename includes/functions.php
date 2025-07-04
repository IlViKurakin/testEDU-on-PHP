<?php
require_once 'db.php';

function getRandomQuestions() {
    global $pdo;
    
    $questions = [];
    foreach (QUESTIONS_DISTRIBUTION as $category => $count) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE category_id = :category ORDER BY RAND() LIMIT :limit");
        $stmt->bindValue(':category', $category, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $count, PDO::PARAM_INT);
        $stmt->execute();
        $questions = array_merge($questions, $stmt->fetchAll());
    }
    
    shuffle($questions);
    return $questions;
}

function saveTestResult($userId, $questions, $answers) {
    global $pdo;
    
    $score = 0;
    $userAnswers = [];
    
    // Подсчет правильных ответов
    foreach ($questions as $index => $question) {
        $answer = $answers[$index + 1] ?? null;
        // Преобразуем в integer и проверяем, был ли ответ
        $answerInt = $answer !== null ? (int)$answer : null;
        $isCorrect = ($answerInt !== null && $answerInt == $question['correct_option']) ? 1 : 0;
        
        if ($isCorrect) {
            $score++;
        }
        
        $userAnswers[] = [
            'question_id' => $question['id'],
            'answer' => $answerInt,
            'is_correct' => $isCorrect // Гарантированно будет 0 или 1
        ];
    }
    
    $pdo->beginTransaction();
    
    try {
        // Удаляем ВСЕ старые записи "В процессе" для этого пользователя
        $stmt = $pdo->prepare("DELETE FROM test_results WHERE user_id = ? AND status = 'in_progress'");
        $stmt->execute([$userId]);
        
        // Создаем новую запись с результатами
        $stmt = $pdo->prepare("
            INSERT INTO test_results (user_id, start_time, end_time, score, status)
            VALUES (?, ?, NOW(), ?, 'completed')
        ");
        $stmt->execute([
            $userId,
            $_SESSION['test_start_time'] ?? date('Y-m-d H:i:s'),
            $score
        ]);
        $resultId = $pdo->lastInsertId();
        
        // Сохраняем ответы пользователя
        $stmt = $pdo->prepare("
            INSERT INTO user_answers (result_id, question_id, answer, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($userAnswers as $answer) {
            $stmt->execute([
                $resultId,
                $answer['question_id'],
                $answer['answer'],
                $answer['is_correct']
            ]);
        }
        
        $pdo->commit();
        return $score;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getUserTestResult($userId) {
    global $pdo;
    
    // Всегда получаем последний результат (удаление старых записей гарантирует актуальность)
    $stmt = $pdo->prepare("
        SELECT * FROM test_results 
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function getUserAnswers($resultId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT ua.*, q.question_text, q.option1, q.option2, q.option3, q.option4, q.correct_option
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        WHERE ua.result_id = ?
    ");
    $stmt->execute([$resultId]);
    return $stmt->fetchAll();
}
// -------------------------------------------------------------------
function getCategoryName($categoryId) {
    $categories = [
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
    return $categories[$categoryId] ?? 'Неизвестная категория';
}
?>