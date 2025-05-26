<?php
// $tools, $categories, $selected_category_name są przekazywane z ToolController::publicList()
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Nasze Narzędzia <?= isset($selected_category_name) ? '- ' . htmlspecialchars($selected_category_name) : '' ?> - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Style dla filtrów kategorii - pozostają bez zmian od ostatniej wersji */
        .category-filters {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        .category-filters h3 {
            margin-bottom: 10px;
            font-size: 1.2em;
            color: #555;
        }
        .category-filters a, .category-filters span {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            text-decoration: none;
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.9em;
            transition: background-color 0.2s, color 0.2s;
        }
        .category-filters a:hover {
            background-color: #007bff;
            color: white;
        }
        .category-filters a.active-filter {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        /* --- Poprawki dla karty narzędzia i przycisku --- */
        /* Zakładamy, że .tool-card ma padding: 0; w głównym style.css (jak w ostatniej wersji)
           lub jeśli ma, to musimy to uwzględnić.
           Style dla .tool-card, .tool-card img, h3, .category, .price, .availability
           są w głównym style.css - tutaj dodajemy lub modyfikujemy.
        */

        .tool-card-content {
            padding: 15px; /* Wewnętrzny padding dla treści karty */
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* Aby wypełnić przestrzeń w .tool-card */
        }

        .tool-card-main-info {
            flex-grow: 1; /* Ta część rośnie, wypychając .tool-card-bottom na dół */
        }
        
        .tool-card-bottom {
            margin-top: 10px; /* Odstęp od głównej informacji */
        }

        .tool-card .details-button {
            display: block; /* Przycisk jako blok */
            width: 100%;    /* Przycisk na 100% szerokości swojego rodzica (.tool-card-bottom) */
            /* Padding przycisku jest dziedziczony z .button-link (np. 10px 22px) */
            /* Jeśli padding przycisku + border przycisku + padding .tool-card-content jest za duży, przycisk może wystawać */
            /* Możemy zmniejszyć padding samego przycisku tutaj lub padding .tool-card-content */
            padding: 8px 10px; /* Spróbujmy nieco mniejszy padding dla tego konkretnego przycisku */
            font-size: 0.95em; /* Lekko mniejsza czcionka, jeśli tekst jest problemem */
            box-sizing: border-box; /* WAŻNE: aby padding i border były wliczane w width: 100% */
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h1>Nasze Narzędzia <?= isset($selected_category_name) ? '- ' . htmlspecialchars($selected_category_name) : '' ?></h1>
        </div>

        <div class="category-filters">
            <h3>Filtruj po kategorii:</h3>
            <a href="index.php?action=tools_public_list" class="<?= !isset($_GET['category_id']) || empty($_GET['category_id']) ? 'active-filter' : '' ?>">Wszystkie Kategorie</a>
            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?action=tools_public_list&category_id=<?= $category['id_kategorii'] ?>"
                       class="<?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['id_kategorii']) ? 'active-filter' : '' ?>">
                        <?= htmlspecialchars($category['nazwa_kategorii']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="tool-grid">
            <?php if (isset($tools) && !empty($tools)): ?>
                <?php foreach ($tools as $tool): ?>
                    <div class="tool-card"> <?php if (!empty($tool['zdjecie_url'])): ?>
                            <img src="<?= htmlspecialchars($tool['zdjecie_url']) ?>" alt="<?= htmlspecialchars($tool['nazwa_narzedzia']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/280x200.png?text=Brak+Zdjecia" alt="Brak zdjęcia">
                        <?php endif; ?>
                        
                        <div class="tool-card-content"> <div class="tool-card-main-info">
                                <h3><?= htmlspecialchars($tool['nazwa_narzedzia']) ?></h3>
                                <p class="category">Kategoria: <?= htmlspecialchars($tool['nazwa_kategorii'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div class="tool-card-bottom">
                                <p class="price"><?= htmlspecialchars(number_format(floatval($tool['cena_za_dobe']), 2, ',', ' ')) ?> zł / doba</p>
                                <p class="availability">
                                    Dostępność: 
                                    <span class="<?= $tool['dostepnosc'] ? 'available' : 'unavailable' ?>">
                                        <?= $tool['dostepnosc'] ? 'Dostępne' : 'Niedostępne' ?>
                                    </span>
                                </p>
                                <a href="index.php?action=show_tool_details&id=<?= $tool['id_narzedzia'] ?>" class="button-link details-button">Zobacz szczegóły</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="alert alert-info">Brak narzędzi pasujących do wybranych kryteriów.</p>
            <?php endif; ?>
        </div>
    </main>

    </body>
</html>