<?php
$errors = $GLOBALS['errors'] ?? []; 
$input = $GLOBALS['input'] ?? []; 
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - Toolsy</title>
    <link rel="stylesheet" href="css/style.css"> <style>
        /* Style dla komunikatów o dostępności emaila */
        .email-status { 
            font-size: 0.9em; 
            margin-top: 5px; 
            min-height: 1.2em; /* Aby div nie skakał przy zmianie treści */
            font-weight: bold;
        }
        .email-available { 
            color: green !important; 
        }
        .email-taken { 
            color: red !important; 
        }
        /* Ogólne style błędów formularza */
        .errors { 
            color: red; 
            border: 1px solid red; 
            padding: 10px; 
            margin-bottom: 15px; 
            background-color: #ffe0e0; 
            border-radius: 5px; 
        }
        .errors ul { 
            list-style-position: inside; 
            padding-left: 0; 
            margin-top: 5px;
            margin-bottom: 0;
        }
        .errors p {
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Zarejestruj się w Toolsy</h1>
    </header>
    <main style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <p>Wystąpiły błędy w formularzu:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?action=register_process" method="POST" id="registrationForm">
            <div style="margin-bottom: 15px;">
                <label for="imie" style="display: block; margin-bottom: 5px;">Imię:</label>
                <input type="text" id="imie" name="imie" value="<?= htmlspecialchars($input['imie'] ?? '') ?>" required style="width: 98%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="nazwisko" style="display: block; margin-bottom: 5px;">Nazwisko:</label>
                <input type="text" id="nazwisko" name="nazwisko" value="<?= htmlspecialchars($input['nazwisko'] ?? '') ?>" required style="width: 98%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="email" style="display: block; margin-bottom: 5px;">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($input['email'] ?? '') ?>" required style="width: 98%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <div id="emailAvailabilityStatus" class="email-status"></div>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="haslo" style="display: block; margin-bottom: 5px;">Hasło:</label>
                <input type="password" id="haslo" name="haslo" required style="width: 98%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="haslo_confirm" style="display: block; margin-bottom: 5px;">Powtórz hasło:</label>
                <input type="password" id="haslo_confirm" name="haslo_confirm" required style="width: 98%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div>
                <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Zarejestruj się</button>
            </div>
        </form>
        <p style="margin-top: 20px; text-align: center;">Masz już konto? <a href="index.php?action=login">Zaloguj się</a></p>
    </main>
    <footer style="text-align:center; padding: 20px; border-top: 1px solid #eee; margin-top: 30px;">
        <p>&copy; <?= date('Y') ?> Toolsy</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const emailStatusDiv = document.getElementById('emailAvailabilityStatus');
            let debounceTimer;

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    
                    // Wyczyść status i klasy przed każdym sprawdzeniem
                    emailStatusDiv.textContent = '';
                    emailStatusDiv.classList.remove('email-available', 'email-taken');

                    if (email.length > 0 && isValidEmail(email)) {
                        // Prosty debounce, aby nie wysyłać żądania przy każdym naciśnięciu klawisza, jeśli używasz 'input'
                        // Dla 'blur' debounce nie jest aż tak krytyczny, ale nie zaszkodzi.
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            checkEmail(email);
                        }, 300); // Zmniejszyłem opóźnienie
                    } else if (email.length > 0) { // Jeśli nie jest pusty, ale nie jest poprawnym emailem
                        emailStatusDiv.textContent = 'Niepoprawny format email.';
                        emailStatusDiv.classList.add('email-taken');
                    }
                });
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function checkEmail(email) {
                emailStatusDiv.textContent = 'Sprawdzam...'; // Informacja dla użytkownika
                emailStatusDiv.classList.remove('email-available', 'email-taken'); // Wyczyść poprzednie klasy

                fetch(`index.php?action=check_email_availability&email=${encodeURIComponent(email)}`)
                    .then(response => {
                        if (!response.ok) {
                            // Jeśli serwer zwrócił błąd HTTP (np. 404, 500)
                            throw new Error('Problem z odpowiedzią serwera: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) { // Najpierw sprawdź, czy serwer nie zwrócił błędu w JSON
                            emailStatusDiv.textContent = data.error;
                            emailStatusDiv.classList.add('email-taken');
                        } else if (data.available) {
                            emailStatusDiv.textContent = 'Email jest dostępny!';
                            emailStatusDiv.classList.add('email-available');
                        } else {
                            emailStatusDiv.textContent = 'Ten email jest już zajęty.';
                            emailStatusDiv.classList.add('email-taken');
                        }
                    })
                    .catch(error => {
                        console.error('Błąd Fetch:', error);
                        emailStatusDiv.textContent = 'Nie udało się sprawdzić emaila. Błąd komunikacji.';
                        emailStatusDiv.classList.add('email-taken');
                    });
            }
        });
    </script>
</body>
</html>