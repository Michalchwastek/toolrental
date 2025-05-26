<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');

require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Controllers/AuthController.php'; // AuthController musi być załadowany przed wywołaniem jego statycznej metody
require_once SRC_PATH . '/Controllers/CategoryController.php';
require_once SRC_PATH . '/Controllers/ToolController.php';

$action = $_GET['action'] ?? 'home';

$authController = new AuthController();
$categoryController = new CategoryController();
$toolController = new ToolController();

ob_start(); 

switch ($action) {
    case 'home':
        // ... (bez zmian) ...
        echo "<h1>Witaj na stronie głównej Toolsy!</h1>";
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'loggedin') echo "<p style='color:green;'>Zalogowano pomyślnie!</p>";
            if ($_GET['status'] === 'loggedout') echo "<p style='color:blue;'>Wylogowano pomyślnie!</p>";
        }
        if (!isset($_SESSION['user_id'])) {
            echo '<p><a href="index.php?action=login">Przejdź do logowania</a></p>';
            echo '<p><a href="index.php?action=register">Zarejestruj się</a></p>';
        } else {
            echo "<p>Jesteś zalogowany. Możesz przeglądać narzędzia (funkcjonalność wkrótce).</p>";
        }
        break;

    // AuthController (bez zmian)
    case 'login': $authController->showLoginForm(); break;
    case 'login_process': $authController->processLogin(); break;
    case 'register': $authController->showRegistrationForm(); break;
    case 'register_process': $authController->processRegistration(); break;
    case 'logout': $authController->logout(); break;

    // CategoryController - ZABEZPIECZONE AKCJE
    case 'categories_list':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->index();
        break;
    case 'category_create_form':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->createForm();
        break;
    case 'category_store':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->store();
        break;
    case 'category_edit_form':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->editForm();
        break;
    case 'category_update':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->update();
        break;
    case 'category_delete':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $categoryController->delete();
        break;

    // ToolController - ZABEZPIECZONE AKCJE
    case 'tools_list':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->index();
        break;
    case 'tool_create_form':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->createForm();
        break;
    case 'tool_store':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->store();
        break;
    case 'tool_edit_form':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->editForm();
        break;
    case 'tool_update':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->update();
        break;
    case 'tool_delete':
        AuthController::checkAdmin(); // SPRAWDZENIE UPRAWNIEŃ
        $toolController->delete();
        break;

    default:
        // ... (bez zmian) ...
        http_response_code(404);
        echo "<h1>Błąd 404: Strona nie znaleziona</h1>";
        echo "<p>Akcja '<strong>" . htmlspecialchars($action) . "</strong>' nie została znaleziona.</p>";
        echo '<p><a href="index.php?action=home">Powrót na stronę główną</a></p>';
        break;
}

$mainContent = ob_get_clean(); 

// Pasek informacyjny - dostosuj wyświetlanie linków administracyjnych
if (isset($_SESSION['user_id'])) {
    echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
    echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
    
    // Pokaż linki administracyjne tylko jeśli użytkownik jest adminem
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        echo " | <a href='index.php?action=categories_list'>Kategorie</a>";
        echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>";
    }
    
    echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
    echo "</div>";
}

echo $mainContent;
?>