<?php include __DIR__ . '/partials/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h2>Создать аккаунт</h2>
            <p>Присоединяйтесь к CloudDrive</p>
        </div>

        <form action="/register" method="post">
             <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
        </form>

        <div class="auth-footer">
            <p>Уже есть аккаунт? <a href="/login">Войдите</a></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
