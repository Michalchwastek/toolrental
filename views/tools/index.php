<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Narzędzia - Panel Admina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Panel Admina - Narzędzia</h1>
        <p><a href="index.php?action=home">Strona główna</a> | <a href="index.php?action=tool_create_form">Dodaj nowe narzędzie</a></p>
    </header>
    <main>
        <?php if (isset($_GET['status'])): ?>
            <p style="color: <?= (strpos($_GET['status'], 'error') !== false || strpos($_GET['status'], 'deleted') !== false) ? 'red' : 'green' ?>;">
                <?php
                    if ($_GET['status'] === 'created') echo "Narzędzie zostało pomyślnie dodane!";
                    if ($_GET['status'] === 'updated') echo "Narzędzie zostało pomyślnie zaktualizowane!";
                    if ($_GET['status'] === 'deleted') echo "Narzędzie zostało usunięte.";
                    if ($_GET['status'] === 'delete_error') echo "Błąd: Nie można usunąć narzędzia (możliwe, że jest aktywnie wypożyczone).";
                ?>
            </p>
        <?php endif; ?>

        <h2>Lista Narzędzi</h2>
        <?php if (!empty($tools)): ?>
            <table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Kategoria</th>
                        <th>Cena/doba</th>
                        <th>Dostępność</th>
                        <th>Zdjęcie (URL)</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tools as $tool): ?>
                    <tr>
                        <td><?= htmlspecialchars($tool['id_narzedzia']) ?></td>
                        <td><?= htmlspecialchars($tool['nazwa_narzedzia']) ?></td>
                        <td><?= htmlspecialchars($tool['nazwa_kategorii'] ?? 'Brak') ?></td>
                        <td><?= htmlspecialchars(number_format(floatval($tool['cena_za_dobe']), 2, ',', ' ')) ?> zł</td>
                        <td><?= $tool['dostepnosc'] ? 'Tak' : 'Nie' ?></td>
                        <td>
                            <?php if (!empty($tool['zdjecie_url'])): ?>
                                <img src="<?= htmlspecialchars($tool['zdjecie_url']) ?>" alt="<?= htmlspecialchars($tool['nazwa_narzedzia']) ?>" style="max-width: 100px; max-height: 50px;">
                            <?php else: ?>
                                Brak
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?action=tool_edit_form&id=<?= $tool['id_narzedzia'] ?>">Edytuj</a>
                            <form action="index.php?action=tool_delete" method="POST" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć to narzędzie?');">
                                <input type="hidden" name="id_narzedzia" value="<?= $tool['id_narzedzia'] ?>">
                                <button type="submit">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Brak narzędzi do wyświetlenia.</p>
        <?php endif; ?>
    </main>
    <footer><p>&copy; <?= date('Y') ?> Toolsy</p></footer>
</body>
</html>