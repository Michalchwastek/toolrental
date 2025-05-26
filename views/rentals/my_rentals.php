<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje Wypożyczenia - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h1>Moje Wypożyczenia</h1>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <?php
                $message = '';
                $message_type_class = 'alert-info';
                switch ($_GET['status']) {
                    case 'rented_successfully':
                        $message = "Narzędzie zostało pomyślnie wypożyczone!";
                        $message_type_class = 'alert-success';
                        break;
                    case 'returned_successfully':
                        $message = "Narzędzie zostało pomyślnie zwrócone!";
                        $message_type_class = 'alert-success';
                        break;
                    case 'already_returned':
                        $message = "To wypożyczenie zostało już zakończone.";
                        break;
                    case 'return_error_data':
                    case 'return_error_auth':
                    case 'return_error_db':
                    case 'return_error_server':
                        $message = "Wystąpił błąd podczas próby zwrotu narzędzia. Spróbuj ponownie lub skontaktuj się z administratorem.";
                        $message_type_class = 'alert-danger';
                        break;
                }
                if ($message) {
                    echo "<div class='alert {$message_type_class}'>{$message}</div>";
                }
            ?>
        <?php endif; ?>

        <?php if (isset($rentals) && !empty($rentals)): ?>
            <?php foreach ($rentals as $rental): ?>
                <div class="rental-item" style="border: 1px solid #eee; padding: 15px; margin-bottom: 15px; background-color: #fff; border-radius: 5px; display: flex; align-items: center; gap: 15px;">
                    <div>
                    <?php if (!empty($rental['zdjecie_url'])): ?>
                        <img src="<?= htmlspecialchars($rental['zdjecie_url']) ?>" alt="<?= htmlspecialchars($rental['nazwa_narzedzia']) ?>" style="width: 100px; height: 70px; object-fit: cover; border-radius: 3px;">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/100x70.png?text=Brak" alt="Brak zdjęcia" style="width: 100px; height: 70px; object-fit: cover; border-radius: 3px;">
                    <?php endif; ?>
                    </div>
                    <div style="flex-grow: 1;">
                        <h3 style="margin-top:0; margin-bottom: 5px;"><?= htmlspecialchars($rental['nazwa_narzedzia']) ?></h3>
                        <p style="font-size: 0.9em; margin-bottom: 3px;">
                            Data wypożyczenia: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($rental['data_wypozyczenia']))) ?><br>
                            Planowana data zwrotu: <?= htmlspecialchars(date('d.m.Y', strtotime($rental['data_planowanego_zwrotu']))) ?><br>
                            Status: <strong><?= htmlspecialchars(ucfirst($rental['status_wypozyczenia'])) ?></strong>
                        </p>
                        <?php if ($rental['data_rzeczywistego_zwrotu']): ?>
                            <p style="font-size: 0.9em; margin-bottom: 3px;">
                                Data zwrotu: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($rental['data_rzeczywistego_zwrotu']))) ?><br>
                                Obliczony koszt: <strong><?= htmlspecialchars(number_format(floatval($rental['calkowity_koszt'] ?? 0), 2, ',', ' ')) ?> zł</strong>
                            </p>
                        <?php else: ?>
                            <?php if ($rental['status_wypozyczenia'] === 'aktywne'): ?>
                                <form action="index.php?action=process_return" method="POST" onsubmit="return confirm('Czy na pewno chcesz zwrócić to narzędzie?');" style="margin-top: 10px;">
                                    <input type="hidden" name="id_wypozyczenia" value="<?= htmlspecialchars($rental['id_wypozyczenia']) ?>">
                                    <input type="hidden" name="id_narzedzia" value="<?= htmlspecialchars($rental['id_narzedzia']) ?>">
                                    <input type="hidden" name="cena_za_dobe" value="<?= htmlspecialchars($rental['cena_za_dobe'] ?? 0) ?>">
                                    <button type="submit" class="button-link" style="background-color: #28a745; font-size: 0.9em; padding: 6px 12px;">Zwróć narzędzie</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="alert alert-info">Nie masz obecnie żadnych wypożyczeń.</p>
        <?php endif; ?>
        <p style="margin-top: 20px;"><a href="index.php?action=tools_public_list" class="button-link secondary">Wypożycz kolejne narzędzie</a></p>
    </main>

    </body>
</html>