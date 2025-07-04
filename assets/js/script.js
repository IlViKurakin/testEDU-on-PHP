// Обработка форм с подтверждением
document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение действий
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Валидация email
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !this.value.endsWith('@top-academy.ru')) {
                this.setCustomValidity('Email должен оканчиваться на @top-academy.ru');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Инициализация tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Инициализация datatables
    $('.datatable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Russian.json'
        }
    });
});

// Функция для экспорта в Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    const html = table.outerHTML;
    
    // Создаем blob и ссылку для скачивания
    const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename || 'data.xls';
    a.click();
}

// ---------------------------------------------------------
// Валидация сложности пароля при регистрации
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthText = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthText.textContent = '';
        return;
    }
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^A-Za-z0-9]/)) strength++;
    
    const messages = ['Слабый', 'Средний', 'Хороший', 'Надежный'];
    strengthText.textContent = `Уровень пароля: ${messages[strength]}`;
    strengthText.className = strength < 2 ? 'text-danger' : 
                           strength < 3 ? 'text-warning' : 'text-success';
});