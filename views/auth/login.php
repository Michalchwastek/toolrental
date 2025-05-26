<?php
// $errors = $errors ?? []; // Do obsługi błędów logowania
// $success_message = $success_message ?? ''; // Do komunikatu po rejestracji
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Zaloguj się do Toolsy</h1>
    </header>
    <main>
        <?php if (!empty($success_message)): ?>
            <div class="success" style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px;">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif;  ?>

        <?php  if (!empty($errors)): ?>
            <div class="errors" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">
                <p>Wystąpiły błędy:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif;  ?>

        <form action="index.php?action=login_process" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php /* echo htmlspecialchars($_POST['email'] ?? ''); */ ?>">
            </div>
            <div>
                <label for="haslo">Hasło:</label>
                <input type="password" id="haslo" name="haslo" required>
            </div>
            <div>
                <button type="submit">Zaloguj się</button>
            </div>
        </form>
        <p>Nie masz konta? <a href="index.php?action=register">Zarejestruj się</a></p>
        </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Toolsy</p>
    </footer>
</body>
</html>