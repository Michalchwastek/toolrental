<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nasze Narzędzia - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Proste style dla tej strony - można przenieść do style.css */
        .tool-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .tool-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .tool-card img {
            max-width: 100%;
            height: 150px; /* Stała wysokość dla obrazków */
            object-fit: contain; /* Dopasuj obrazek, zachowując proporcje */
            margin-bottom: 10px;
        }
        .tool-card h3 {
            margin-top: 0;
            font-size: 1.2em;
        }
        .tool-card .price {
            font-weight: bold;
            color: #27ae60;
            margin: 10px 0;
        }
        .tool-card .availability {
            font-size: 0.9em;
            color: #555;
        }
        .tool-card .details-button {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .tool-card .details-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Toolsy - Przeglądaj Nasze Narzędzia</h1>
        <nav>
            <a href="index.php?action=home">Strona główna</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    | <a href="index.php?action=tools_list">Panel Admina Narzędzi</a>
                <?php endif; ?>
                | <a href="index.php?action=logout">Wyloguj się (<?= htmlspecialchars($_SESSION['user_imie']) ?>)</a>
            <?php else: ?>
                | <a href="index.php?action=login">Zaloguj się</a>
                | <a href="index.php?action=register">Zarejestruj się</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <div class="tool-grid">
            <?php if (!empty($tools)): ?>
                <?php foreach ($tools as $tool): ?>
                    <div class="tool-card">
                        <?php if (!empty($tool['zdjecie_url'])): ?>
                            <img src="<?= htmlspecialchars($tool['zdjecie_url']) ?>" alt="<?= htmlspecialchars($tool['nazwa_narzedzia']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/250x150.png?text=Brak+Zdjecia" alt="Brak zdjęcia"> <?php endif; ?>
                        <h3><?= htmlspecialchars($tool['nazwa_narzedzia']) ?></h3>
                        <p class="category">Kategoria: <?= htmlspecialchars($tool['nazwa_kategorii'] ?? 'N/A') ?></p>
                        <p class="price"><?= htmlspecialchars(number_format(floatval($tool['cena_za_dobe']), 2, ',', ' ')) ?> zł / doba</p>
                        <p class="availability">
                            Dostępność: 
                            <span style="color: <?= $tool['dostepnosc'] ? 'green' : 'red' ?>;">
                                <?= $tool['dostepnosc'] ? 'Dostępne' : 'Niedostępne' ?>
                            </span>
                        </p>
                        <a href="index.php?action=show_tool_details&id=<?= $tool['id_narzedzia'] ?>" class="details-button">Zobacz szczegóły</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Obecnie brak dostępnych narzędzi w ofercie.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Toolsy</p>
    </footer>
</body>
</html>