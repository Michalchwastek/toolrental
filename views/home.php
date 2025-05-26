<?php
// Ewentualne zmienne przekazane z kontrolera (na razie nie mamy dedykowanego kontrolera dla strony głównej,
// więc komunikaty o statusie bierzemy bezpośrednio z $_GET, a informacje o sesji z $_SESSION)
$status_message = '';
$status_type_class = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'loggedin') {
        $status_message = "Zalogowano pomyślnie!";
        $status_type_class = 'alert-success';
    }
    if ($_GET['status'] === 'loggedout') {
        $status_message = "Wylogowano pomyślnie!";
        $status_type_class = 'alert-info';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wypożyczalnia Narzędzi Toolsy - Strona Główna</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h1>Witaj w Toolsy - Twojej Wypożyczalni Narzędzi!</h1>
        </div>

        <?php if ($status_message): ?>
            <p class="alert <?= $status_type_class ?>"><?= htmlspecialchars($status_message) ?></p>
        <?php endif; ?>

        <p style="text-align: center; font-size: 1.1em; margin-bottom: 30px;">
            Znajdź i wypożycz narzędzia, których potrzebujesz, szybko i wygodnie.
        </p>

        <div style="text-align: center; margin-bottom: 20px;">
            <a href="index.php?action=tools_public_list" class="button-link" style="margin: 5px; padding: 12px 25px; font-size: 1.1em;">Przeglądaj Dostępne Narzędzia</a>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div style="text-align: center; margin-top: 30px; padding-top:20px; border-top: 1px solid #eee;">
                <p style="margin-bottom:10px;">Masz już konto lub chcesz dołączyć?</p>
                <a href="index.php?action=login" class="button-link secondary" style="margin: 5px;">Zaloguj się</a>
                <a href="index.php?action=register" class="button-link" style="background-color: #28a745; margin: 5px;">Zarejestruj się</a>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 20px;">
                <p>Jesteś zalogowany. Przejdź do <a href="index.php?action=my_rentals">swoich wypożyczeń</a> lub <a href="index.php?action=tools_public_list">przeglądaj narzędzia</a>.</p>
            </div>
        <?php endif; ?>
    </main>

    </body>
</html>