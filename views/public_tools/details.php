<?php
$errors = $_SESSION['rental_errors'] ?? [];
$rental_form_data = $_SESSION['rental_form_data'] ?? [];
if (!empty($errors) && isset($tool) && ($rental_form_data['id_narzedzia'] ?? 0) != ($tool['id_narzedzia'] ?? -1)) {
    $errors = []; 
    $rental_form_data = [];
}
unset($_SESSION['rental_errors']);
unset($_SESSION['rental_form_data']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tool['nazwa_narzedzia'] ?? 'Szczegóły Narzędzia') ?> - Toolsy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container"> 
        <?php if (isset($tool) && $tool): ?>
            <div class="page-header"> 
                 <h1><?= htmlspecialchars($tool['nazwa_narzedzia']) ?></h1>
            </div>

            <div class="tool-details-container"> 
                <?php if (!empty($tool['zdjecie_url'])): ?>
                    <img src="<?= htmlspecialchars($tool['zdjecie_url']) ?>" alt="<?= htmlspecialchars($tool['nazwa_narzedzia']) ?>" style="max-width: 100%; height: auto; max-height: 400px; display: block; margin: 0 auto 20px auto; border-radius: 5px; border: 1px solid #ddd; padding: 5px;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/600x400.png?text=Brak+Zdjecia" alt="Brak zdjęcia" style="max-width: 100%; height: auto; max-height: 400px; display: block; margin: 0 auto 20px auto; border-radius: 5px; border: 1px solid #ddd; padding: 5px;">
                <?php endif; ?>

                <div class="property">
                    <strong>Kategoria:</strong> <?= htmlspecialchars($tool['nazwa_kategorii'] ?? 'N/A') ?>
                </div>
                
                <div class="price" style="font-size: 1.5em; font-weight: bold; color: #007bff; text-align: center; margin: 20px 0;">
                    Cena: <?= htmlspecialchars(number_format(floatval($tool['cena_za_dobe']), 2, ',', ' ')) ?> zł / doba
                </div>

                <div class="availability" style="font-size: 1.2em; text-align: center; margin-bottom: 20px;">
                    Dostępność: 
                    <span class="<?= $tool['dostepnosc'] ? 'available' : 'unavailable' ?>" style="font-weight: bold;">
                        <?= $tool['dostepnosc'] ? 'Dostępne' : 'Niedostępne' ?>
                    </span>
                </div>

                <?php if (!empty($tool['opis_narzedzia'])): ?>
                    <div class="description" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; line-height: 1.6;">
                        <h2>Opis</h2>
                        <p><?= nl2br(htmlspecialchars($tool['opis_narzedzia'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="actions" style="text-align: center; margin-top: 30px;">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <p>Błędy przy próbie wypożyczenia:</p>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'not_logged_in_for_rental' && isset($tool['id_narzedzia']) && (!isset($_SESSION['user_id']))): ?>
                        <p class="alert alert-danger">Musisz być zalogowany, aby wypożyczyć narzędzie. <a href="index.php?action=login&redirect_to=show_tool_details&id=<?= $tool['id_narzedzia'] ?>">Zaloguj się</a>.</p>
                    <?php endif; ?>


                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($tool['dostepnosc']): ?>
                            <form action="index.php?action=process_rental" method="POST" style="margin-top:10px;">
                                <input type="hidden" name="id_narzedzia" value="<?= $tool['id_narzedzia'] ?>">
                                <div>
                                    <label for="data_planowanego_zwrotu">Planowana data zwrotu:</label>
                                    <input type="date" id="data_planowanego_zwrotu" name="data_planowanego_zwrotu" 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required 
                                           value="<?= htmlspecialchars($rental_form_data['data_planowanego_zwrotu'] ?? date('Y-m-d', strtotime('+7 days'))) ?>"> 
                                </div>
                                <button type="submit" class="button-link rent-button" style="margin-top: 15px;">Wypożycz teraz</button>
                            </form>
                        <?php else: ?>
                            <p class="unavailable-message" style="color: #dc3545; font-weight: bold;">Narzędzie obecnie niedostępne.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="margin-top:15px;">Aby wypożyczyć, <a href="index.php?action=login&redirect_to=show_tool_details&id=<?= $tool['id_narzedzia'] ?>">zaloguj się</a>.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="page-header"><h1>Narzędzie nie znalezione</h1></div>
                <p class="alert alert-danger">Przepraszamy, narzędzie o podanym ID nie istnieje lub wystąpił błąd.</p>
            <?php endif; ?>
            
            <a href="index.php?action=tools_public_list" class="back-link" style="display: block; text-align: center; margin-top: 30px;">&laquo; Powrót do listy narzędzi</a>
        </div>
    </main>

    </body>
</html>