<?php
// $tool jest przekazywane z ToolController::showToolDetails()
// $errors, $success_message itp. mogą być potrzebne, jeśli dodamy tu formularze (np. wypożyczenia)
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
    </style>
</head>
<body>
    <header>
        <?php
        // Pasek informacyjny - skopiowany z index.php dla spójności
        if (isset($_SESSION['user_id'])) {
            echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
            echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
            echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
            echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                echo " | <a href='index.php?action=categories_list'>Kategorie (Admin)</a>";
                echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>";
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
                    <img src="https://via.placeholder.com/600x400.png?text=Brak+Zdjecia" alt="Brak zdjęcia"> <?php endif; ?>

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
                    <?php if (isset($_SESSION['user_id'])): // Tylko dla zalogowanych użytkowników ?>
                        <?php if ($tool['dostepnosc']): ?>
                            <a href="index.php?action=rent_tool_form&id=<?= $tool['id_narzedzia'] ?>" class="rent-button">Wypożycz teraz</a>
                            <p style="margin-top:10px; font-size:0.9em;">(Funkcjonalność wypożyczania wkrótce!)</p>
                        <?php else: ?>
                            <p class="unavailable-message">Narzędzie obecnie niedostępne.</p>
                        <?php endif; ?>
                    <?php else: // Dla niezalogowanych ?>
                        <p><a href="index.php?action=login&redirect_to=show_tool_details&id=<?= $tool['id_narzedzia'] ?>">Zaloguj się, aby wypożyczyć</a></p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p>Nie znaleziono informacji o narzędziu.</p>
            <?php endif; ?>

            <a href="index.php?action=tools_public_list" class="back-link">&laquo; Powrót do listy narzędzi</a>
        </div>
    </main>
    <footer>
        <p style='text-align:center; padding: 20px; border-top: 1px solid #ccc; margin-top: 30px;'>&copy; <?= date('Y') ?> Toolsy - Wypożyczalnia Narzędzi</p>
    </footer>
</body>
</html>