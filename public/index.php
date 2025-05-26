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
require_once SRC_PATH . '/Controllers/CategoryController.php';
require_once SRC_PATH . '/Controllers/ToolController.php'; // <-- DODAJ

$action = $_GET['action'] ?? 'home';

$authController = new AuthController();
$categoryController = new CategoryController();
$toolController = new ToolController(); // <-- DODAJ

ob_start(); 

switch ($action) {
    case 'home':
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

    // CategoryController (bez zmian)
    case 'categories_list': $categoryController->index(); break;
    case 'category_create_form': $categoryController->createForm(); break;
    case 'category_store': $categoryController->store(); break;
    case 'category_edit_form': $categoryController->editForm(); break;
    case 'category_update': $categoryController->update(); break;
    case 'category_delete': $categoryController->delete(); break;

    // NOWE AKCJE DLA NARZĘDZI (ToolController)
    case 'tools_list':
        // Tutaj też w przyszłości dodamy sprawdzenie roli admina
        $toolController->index();
        break;
    case 'tool_create_form':
        $toolController->createForm();
        break;
    case 'tool_store':
        $toolController->store();
        break;
    case 'tool_edit_form':
        $toolController->editForm();
        break;
    case 'tool_update':
        $toolController->update();
        break;
    case 'tool_delete':
        $toolController->delete();
        break;

    default:
        http_response_code(404);
        echo "<h1>Błąd 404: Strona nie znaleziona</h1>";
        echo "<p>Akcja '<strong>" . htmlspecialchars($action) . "</strong>' nie została znaleziona.</p>";
        echo '<p><a href="index.php?action=home">Powrót na stronę główną</a></p>';
        break;
}

$mainContent = ob_get_clean(); 

if (isset($_SESSION['user_id'])) {
    echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
    echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
    echo " | <a href='index.php?action=categories_list'>Kategorie</a>"; // Zmieniony link
    echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>"; // NOWY LINK
    echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
    echo "</div>";
}

echo $mainContent;
?>