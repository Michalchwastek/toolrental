<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Kategorię - Panel Admina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Panel Admina - Edytuj Kategorię</h1>
        <p><a href="index.php?action=categories_list">Powrót do listy kategorii</a></p>
    </header>
    <main>
        <?php if (empty($category)): ?>
            <p>Nie znaleziono kategorii do edycji.</p>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="errors" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">
                    <p>Wystąpiły błędy:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="index.php?action=category_update" method="POST">
                <input type="hidden" name="id_kategorii" value="<?= htmlspecialchars($category['id_kategorii']) ?>">
                <div>
                    <label for="nazwa_kategorii">Nazwa kategorii:</label>
                    <input type="text" id="nazwa_kategorii" name="nazwa_kategorii" value="<?= htmlspecialchars($category['nazwa_kategorii'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="opis_kategorii">Opis (opcjonalnie):</label>
                    <textarea id="opis_kategorii" name="opis_kategorii"><?= htmlspecialchars($category['opis_kategorii'] ?? '') ?></textarea>
                </div>
                <div>
                    <button type="submit">Zaktualizuj kategorię</button>
                </div>
            </form>
        <?php endif; ?>
    </main>
    <footer><p>&copy; <?= date('Y') ?> Toolsy</p></footer>
</body>
</html>