<?php
// $tool jest przekazywane z ToolController::showToolDetails()
// Zmienne $errors i $rental_form_data są przekazywane przez sesję z RentalController::processRental() w razie błędu
$errors = $_SESSION['rental_errors'] ?? [];
$rental_form_data = $_SESSION['rental_form_data'] ?? [];

// Sprawdzamy, czy błędy dotyczą tego konkretnego narzędzia
// (prosty mechanizm, aby błędy z innego narzędzia nie wyświetlały się tutaj)
if (!empty($errors) && ($rental_form_data['id_narzedzia'] ?? 0) != ($tool['id_narzedzia'] ?? -1)) {
    $errors = []; // Wyzeruj błędy, jeśli nie dotyczą tego narzędzia
    $rental_form_data = [];
}

unset($_SESSION['rental_errors']); // Zawsze usuwaj błędy z sesji po ich potencjalnym użyciu
unset($_SESSION['rental_form_data']); // Zawsze usuwaj dane formularza z sesji
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tool['nazwa_narzedzia'] ?? 'Szczegóły Narzędzia') ?> - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Proste style dla tej strony - można przenieść do style.css */
        .tool-details-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .tool-details-container img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            display: block;
            margin: 0 auto 20px auto;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tool-details-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .tool-details-container .property {
            margin-bottom: 10px;
        }
        .tool-details-container .property strong {
            display: inline-block;
            width: 150px; /* Stała szerokość dla etykiet */
            color: #555;
        }
        .tool-details-container .price {
            font-size: 1.5em;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
            margin: 20px 0;
        }
        .tool-details-container .availability {
            font-size: 1.2em;
            text-align: center;
            margin-bottom: 20px;
        }
        .tool-details-container .description {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            line-height: 1.6;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .actions .rent-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            border: none; /* Dla spójności z button */
            cursor: pointer;
        }
        .actions .rent-button:hover {
            background-color: #0056b3;
        }
        .actions .unavailable-message {
            color: #cc0000;
            font-weight: bold;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .errors { 
            color: red; 
            border: 1px solid red; 
            padding: 10px; 
            margin-bottom: 15px;
            background-color: #ffe0e0;
            border-radius: 5px;
        }
        .errors ul { list-style-position: inside; padding-left: 0; }
    </style>
</head>
<body>
    <header>
        <?php
        if (isset($_SESSION['user_id'])) {
            echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
            echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
            echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
            echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                echo " | <a href='index.php?action=categories_list'>Kategorie (Admin)</a>";
                echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>";
            } else {
                 echo " | <a href='index.php?action=my_rentals'>Moje Wypożyczenia</a>";
            }
            echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f0f0f0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
            echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
            echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
            echo " | <a href='index.php?action=login'>Zaloguj się</a> | <a href='index.php?action=register'>Zarejestruj się</a>";
            echo "</div>";
        }
        ?>
    </header>
    <main>
        <div class="tool-details-container">
            <?php if (isset($tool) && $tool): ?>
                <h1><?= htmlspecialchars($tool['nazwa_narzedzia']) ?></h1>

                <?php if (!empty($tool['zdjecie_url'])): ?>
                    <img src="<?= htmlspecialchars($tool['zdjecie_url']) ?>" alt="<?= htmlspecialchars($tool['nazwa_narzedzia']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/600x400.png?text=Brak+Zdjecia" alt="Brak zdjęcia">
                <?php endif; ?>

                <div class="property">
                    <strong>Kategoria:</strong> <?= htmlspecialchars($tool['nazwa_kategorii'] ?? 'N/A') ?>
                </div>
                
                <div class="price">
                    Cena: <?= htmlspecialchars(number_format(floatval($tool['cena_za_dobe']), 2, ',', ' ')) ?> zł / doba
                </div>

                <div class="availability">
                    Dostępność: 
                    <span style="color: <?= $tool['dostepnosc'] ? 'green' : 'red' ?>; font-weight: bold;">
                        <?= $tool['dostepnosc'] ? 'Dostępne' : 'Niedostępne' ?>
                    </span>
                </div>

                <?php if (!empty($tool['opis_narzedzia'])): ?>
                    <div class="description">
                        <h2>Opis</h2>
                        <p><?= nl2br(htmlspecialchars($tool['opis_narzedzia'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="actions">
                    <?php if (!empty($errors)): ?>
                        <div class="errors">
                            <p>Błędy przy próbie wypożyczenia:</p>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'not_logged_in_for_rental'): ?>
                        <p class="errors">Musisz być zalogowany, aby wypożyczyć narzędzie. <a href="index.php?action=login&redirect_to=show_tool_details&id=<?= $tool['id_narzedzia'] ?>">Zaloguj się</a>.</p>
                    <?php endif; ?>


                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($tool['dostepnosc']): ?>
                            <form action="index.php?action=process_rental" method="POST">
                                <input type="hidden" name="id_narzedzia" value="<?= $tool['id_narzedzia'] ?>">
                                <div>
                                    <label for="data_planowanego_zwrotu">Planowana data zwrotu:</label>
                                    <input type="date" id="data_planowanego_zwrotu" name="data_planowanego_zwrotu" 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required 
                                           value="<?= htmlspecialchars($rental_form_data['data_planowanego_zwrotu'] ?? date('Y-m-d', strtotime('+7 days'))) ?>"> 
                                </div>
                                <button type="submit" class="rent-button" style="margin-top: 10px;">Wypożycz teraz</button>
                            </form>
                        <?php else: ?>
                            <p class="unavailable-message">Narzędzie obecnie niedostępne.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Aby wypożyczyć, <a href="index.php?action=login&redirect_to=show_tool_details&id=<?= $tool['id_narzedzia'] ?>">zaloguj się</a>.</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p>Nie znaleziono informacji o narzędziu.</p>
            <?php endif; ?>
            
            <a href="index.php?action=tools_public_list" class="back-link">&laquo; Powrót do listy narzędzi</a>
        </div>
    </main>
    <footer style='text-align:center; padding: 20px; border-top: 1px solid #ccc; margin-top: 30px;'>
        <p>&copy; <?= date('Y') ?> Toolsy - Wypożyczalnia Narzędzi</p>
    </footer>
</body>
</html>