<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');

require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Controllers/AuthController.php';

$action = $_GET['action'] ?? 'home';
$authController = new AuthController();

// Prosty sposób na wyświetlenie informacji o zalogowanym użytkowniku na każdej stronie (do celów testowych)
// Można to przenieść do jakiegoś wspólnego layoutu/nagłówka później
if (isset($_SESSION['user_id'])) {
    echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right;'>";
    echo "Zalogowany jako: " . htmlspecialchars($_SESSION['user_imie']) . " (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
    echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
    echo "</div>";
}


switch ($action) {
    case 'home':
        echo "<h1>Witaj na stronie głównej Toolsy!</h1>";
        // Prosty komunikat o statusie logowania/wylogowania
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'loggedin') echo "<p style='color:green;'>Zalogowano pomyślnie!</p>";
            if ($_GET['status'] === 'loggedout') echo "<p style='color:blue;'>Wylogowano pomyślnie!</p>";
        }
        echo "<p>To jest nasza przyszła wypożyczalnia narzędzi.</p>";
        // Pokaż różne linki w zależności od tego, czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            echo '<p><a href="index.php?action=login">Przejdź do logowania</a></p>';
            echo '<p><a href="index.php?action=register">Zarejestruj się</a></p>';
        } else {
            // Tutaj mogą być linki do profilu, narzędzi itp.
            echo "<p>Jesteś zalogowany. Możesz przeglądać narzędzia (funkcjonalność wkrótce).</p>";
        }
        break;

    case 'login':
        $authController->showLoginForm();
        break;

    case 'login_process': // <-- NOWA AKCJA
        $authController->processLogin();
        break;

    case 'register':
        $authController->showRegistrationForm();
        break;

    case 'register_process':
        $authController->processRegistration();
        break;

    case 'logout': // <-- NOWA AKCJA
        $authController->logout();
        break;

    default:
        http_response_code(404);
        echo "<h1>Błąd 404: Strona nie znaleziona</h1>";
        echo "<p>Akcja '<strong>" . htmlspecialchars($action) . "</strong>' nie została znaleziona.</p>";
        echo '<p><a href="index.php?action=home">Powrót na stronę główną</a></p>';
        break;
}
?>