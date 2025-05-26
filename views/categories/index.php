<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kategorie Narzędzi - Panel Admina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Panel Admina - Kategorie Narzędzi</h1>
        <p><a href="index.php?action=home">Strona główna</a> | <a href="index.php?action=category_create_form">Dodaj nową kategorię</a></p>
    </header>
    <main>
        <?php if (isset($_GET['status'])): ?>
            <p style="color: <?= ($_GET['status'] === 'deleted' || $_GET['status'] === 'delete_error') ? 'red' : 'green' ?>;">
                <?php
                    if ($_GET['status'] === 'created') echo "Kategoria została pomyślnie dodana!";
                    if ($_GET['status'] === 'updated') echo "Kategoria została pomyślnie zaktualizowana!";
                    if ($_GET['status'] === 'deleted') echo "Kategoria została usunięta.";
                    if ($_GET['status'] === 'delete_error') echo "Błąd: Nie można usunąć kategorii (możliwe, że zawiera narzędzia).";
                ?>
            </p>
        <?php endif; ?>

        <h2>Lista Kategorii</h2>
        <?php if (!empty($categories)): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa Kategorii</th>
                        <th>Opis</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['id_kategorii']) ?></td>
                        <td><?= htmlspecialchars($category['nazwa_kategorii']) ?></td>
                        <td><?= htmlspecialchars($category['opis_kategorii'] ?? '') ?></td>
                        <td>
                            <a href="index.php?action=category_edit_form&id=<?= $category['id_kategorii'] ?>">Edytuj</a>
                            <form action="index.php?action=category_delete" method="POST" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć tę kategorię?');">
                                <input type="hidden" name="id_kategorii" value="<?= $category['id_kategorii'] ?>">
                                <button type="submit">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Brak kategorii do wyświetlenia.</p>
        <?php endif; ?>
    </main>
    <footer><p>&copy; <?= date('Y') ?> Toolsy</p></footer>
</body>
</html>