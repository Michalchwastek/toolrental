<?php
// Tutaj można będzie dodać np. zmienne przekazane z kontrolera, np. komunikaty o błędach
// $errors = $errors ?? []; // Jeśli $errors nie istnieje, ustaw na pustą tablicę
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-G">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - Toolsy</title>
    <link rel="stylesheet" href="css/style.css"> </head>
<body>
    <header>
        <h1>Zarejestruj się w Toolsy</h1>
    </header>
    <main>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <p>Wystąpiły błędy w formularzu:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif;  ?>

        <form action="index.php?action=register_process" method="POST">
            <div>
                <label for="imie">Imię:</label>
                <input type="text" id="imie" name="imie" required>
            </div>
            <div>
                <label for="nazwisko">Nazwisko:</label>
                <input type="text" id="nazwisko" name="nazwisko" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="haslo">Hasło:</label>
                <input type="password" id="haslo" name="haslo" required>
            </div>
            <div>
                <label for="haslo_confirm">Powtórz hasło:</label>
                <input type="password" id="haslo_confirm" name="haslo_confirm" required>
            </div>
            <div>
                <button type="submit">Zarejestruj się</button>
            </div>
        </form>
        <p>Masz już konto? <a href="index.php?action=login">Zaloguj się</a></p>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Toolsy</p>
    </footer>
</body>
</html>