<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Если пользователь авторизован, перенаправляем в личный кабинет
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <div class="jumbotron mt-5 text-center">
        <h1 class="display-4">Погружение в продукт: тест знаний</h1>
        <p class="lead">Пройдите тестирование для проверки знаний о продуктах компании</p>
        <hr class="my-4">
        <p>Для доступа к тестированию необходимо войти в систему или зарегистрироваться</p>
        <div class="d-flex justify-content-center gap-3">
            <a class="btn btn-primary btn-lg" href="login.php" role="button">Войти</a>
            <a class="btn btn-success btn-lg" href="register.php" role="button">Зарегистрироваться</a>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">50 вопросов</h5>
                    <p class="card-text">Тест состоит из 50 вопросов по различным направлениям деятельности компании</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">10 категорий</h5>
                    <p class="card-text">Вопросы охватывают 10 основных категорий продуктов компании</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">1 попытка</h5>
                    <p class="card-text">У вас есть только одна попытка для прохождения теста</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>