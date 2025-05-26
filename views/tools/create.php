<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj Narzędzie - Panel Admina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Panel Admina - Dodaj Nowe Narzędzie</h1>
        <p><a href="index.php?action=tools_list">Powrót do listy narzędzi</a></p>
    </header>
    <main>
        <?php if (!empty($errors)): ?>
            <div class="errors" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">
                <p>Wystąpiły błędy w formularzu:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?action=tool_store" method="POST">
            <div>
                <label for="nazwa_narzedzia">Nazwa narzędzia:</label>
                <input type="text" id="nazwa_narzedzia" name="nazwa_narzedzia" value="<?= htmlspecialchars($_POST['nazwa_narzedzia'] ?? '') ?>" required>
            </div>
            <div>
                <label for="opis_narzedzia">Opis:</label>
                <textarea id="opis_narzedzia" name="opis_narzedzia"><?= htmlspecialchars($_POST['opis_narzedzia'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="id_kategorii">Kategoria:</label>
                <select id="id_kategorii" name="id_kategorii" required>
                    <option value="">-- Wybierz kategorię --</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id_kategorii']) ?>" 
                                <?= (isset($_POST['id_kategorii']) && $_POST['id_kategorii'] == $category['id_kategorii']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['nazwa_kategorii']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="cena_za_dobe">Cena za dobę (zł):</label>
                <input type="text" id="cena_za_dobe" name="cena_za_dobe" value="<?= htmlspecialchars($_POST['cena_za_dobe'] ?? '') ?>" required pattern="^\d+([,.]\d{1,2})?$" title="Podaj poprawną cenę, np. 123 lub 123,45 lub 123.45">
            </div>
            <div>
                <label for="dostepnosc">Dostępne:</label>
                <input type="checkbox" id="dostepnosc" name="dostepnosc" value="1" <?= (isset($_POST['dostepnosc']) || !isset($_POST['nazwa_narzedzia'])) ? 'checked' : '' ?>> 
                </div>
            <div>
                <label for="zdjecie_url">URL zdjęcia (opcjonalnie):</label>
                <input type="url" id="zdjecie_url" name="zdjecie_url" value="<?= htmlspecialchars($_POST['zdjecie_url'] ?? '') ?>" placeholder="np. https://example.com/obrazek.jpg">
            </div>
            <div>
                <button type="submit">Dodaj narzędzie</button>
            </div>
        </form>
    </main>
    <footer><p>&copy; <?= date('Y') ?> Toolsy</p></footer>
</body>
</html>