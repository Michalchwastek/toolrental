<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje Wypożyczenia - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .rental-item { border: 1px solid #eee; padding: 10px; margin-bottom: 10px; background-color: #f9f9f9; border-radius: 5px;}
        .rental-item img { max-width: 100px; max-height: 60px; vertical-align: middle; margin-right: 10px;}
        .rental-item h3 { margin-top: 0; }
        .return-button { 
            display: inline-block; 
            padding: 5px 10px; 
            background-color: #28a745; 
            color: white; 
            text-decoration: none; 
            border-radius: 3px; 
            border: none; 
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .return-button:hover { background-color: #218838; }
        .status-message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .status-success { color: green; border: 1px solid green; background-color: #e6ffe6; }
        .status-error { color: red; border: 1px solid red; background-color: #ffe0e0; }
        .status-info { color: blue; border: 1px solid blue; background-color: #e0e0ff; }
    </style>
</head>
<body>
    <header>
        <?php
        // Pasek informacyjny
        if (isset($_SESSION['user_id'])) {
            echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
            echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
            echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
            echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                echo " | <a href='index.php?action=categories_list'>Kategorie (Admin)</a>";
                echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>";
                 echo " | <a href='index.php?action=my_rentals'>Moje Wypożyczenia</a>"; 
            } else {
                 echo " | <a href='index.php?action=my_rentals'>Moje Wypożyczenia</a>";
            }
            echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
            echo "</div>";
        }
        // Pasek dla niezalogowanych nie jest tu potrzebny, bo ta strona wymaga zalogowania
        ?>
        <h1>Moje Wypożyczenia</h1>
    </header>
    <main style="padding: 20px;">
        <?php if (isset($_GET['status'])): ?>
            <?php
                $message = '';
                $message_type = 'info'; // Domyślny typ
                switch ($_GET['status']) {
                    case 'rented_successfully':
                        $message = "Narzędzie zostało pomyślnie wypożyczone!";
                        $message_type = 'success';
                        break;
                    case 'returned_successfully':
                        $message = "Narzędzie zostało pomyślnie zwrócone!";
                        $message_type = 'success';
                        break;
                    case 'already_returned':
                        $message = "To wypożyczenie zostało już zakończone.";
                        $message_type = 'info';
                        break;
                    case 'return_error_data':
                    case 'return_error_auth':
                    case 'return_error_db':
                    case 'return_error_server':
                        $message = "Wystąpił błąd podczas próby zwrotu narzędzia. Spróbuj ponownie lub skontaktuj się z administratorem.";
                        $message_type = 'error';
                        break;
                }
                if ($message) {
                    echo "<p class='status-message status-{$message_type}'>{$message}</p>";
                }
            ?>
        <?php endif; ?>

        <?php if (isset($rentals) && !empty($rentals)): ?>
            <?php foreach ($rentals as $rental): ?>
                <div class="rental-item">
                    <h3><?= htmlspecialchars($rental['nazwa_narzedzia']) ?></h3>
                    <?php if (!empty($rental['zdjecie_url'])): ?>
                        <img src="<?= htmlspecialchars($rental['zdjecie_url']) ?>" alt="<?= htmlspecialchars($rental['nazwa_narzedzia']) ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/100x60.png?text=Brak+Zdjecia" alt="Brak zdjęcia">
                    <?php endif; ?>
                    <p>
                        Data wypożyczenia: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($rental['data_wypozyczenia']))) ?><br>
                        Planowana data zwrotu: <?= htmlspecialchars(date('d.m.Y', strtotime($rental['data_planowanego_zwrotu']))) ?><br>
                        Status: <strong><?= htmlspecialchars(ucfirst($rental['status_wypozyczenia'])) ?></strong><br>
                        <?php if ($rental['data_rzeczywistego_zwrotu']): ?>
                            Data zwrotu: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($rental['data_rzeczywistego_zwrotu']))) ?><br>
                            Obliczony koszt: <?= htmlspecialchars(number_format(floatval($rental['calkowity_koszt'] ?? 0), 2, ',', ' ')) ?> zł
                        <?php else: ?>
                            <?php if ($rental['status_wypozyczenia'] === 'aktywne'): ?>
                                <form action="index.php?action=process_return" method="POST" onsubmit="return confirm('Czy na pewno chcesz zwrócić to narzędzie?');">
                                    <input type="hidden" name="id_wypozyczenia" value="<?= htmlspecialchars($rental['id_wypozyczenia']) ?>">
                                    <input type="hidden" name="id_narzedzia" value="<?= htmlspecialchars($rental['id_narzedzia']) ?>">
                                    <input type="hidden" name="cena_za_dobe" value="<?= htmlspecialchars($rental['cena_za_dobe'] ?? 0) ?>">
                                    <button type="submit" class="return-button">Zwróć narzędzie</button>
                                </form>
                            <?php endif; // koniec if status_wypozyczenia === 'aktywne' ?>
                        <?php endif; // koniec if data_rzeczywistego_zwrotu ?>
                    </p>
                </div>
            <?php endforeach; // koniec foreach rentals ?>
        <?php else: ?>
            <p>Nie masz obecnie żadnych wypożyczeń.</p>
        <?php endif; // koniec if !empty(rentals) ?>
        <p style="margin-top: 20px;"><a href="index.php?action=tools_public_list">Wypożycz kolejne narzędzie</a></p>
    </main>
    <footer style='text-align:center; padding: 20px; border-top: 1px solid #ccc; margin-top: 30px;'>
        <p>&copy; <?= date('Y') ?> Toolsy - Wypożyczalnia Narzędzi</p>
    </footer>
</body>
</html>