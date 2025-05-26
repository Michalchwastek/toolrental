<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definicje ścieżek - kluczowe, aby były na początku i tylko tutaj
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');

// Ładowanie konfiguracji i kontrolerów
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Controllers/AuthController.php';
require_once SRC_PATH . '/Controllers/CategoryController.php';
require_once SRC_PATH . '/Controllers/ToolController.php'; 

$action = $_GET['action'] ?? 'home';

// Instancje kontrolerów
$authController = new AuthController();
$categoryController = new CategoryController();
$toolController = new ToolController(); 

ob_start(); // Włącz buforowanie wyjścia

switch ($action) {
    case 'home':
        echo "<h1>Witaj na stronie głównej Toolsy!</h1>";
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'loggedin') echo "<p style='color:green;'>Zalogowano pomyślnie!</p>";
            if ($_GET['status'] === 'loggedout') echo "<p style='color:blue;'>Wylogowano pomyślnie!</p>";
        }
        echo '<p><a href="index.php?action=tools_public_list">Zobacz dostępne narzędzia</a></p>';

        if (!isset($_SESSION['user_id'])) {
            echo '<p><a href="index.php?action=login">Przejdź do logowania</a></p>';
            echo '<p><a href="index.php?action=register">Zarejestruj się</a></p>';
        } else {
            // Komunikat dla zalogowanego użytkownika, np.
            // echo "<p>Cieszymy się, że jesteś z nami, " . htmlspecialchars($_SESSION['user_imie']) . "!</p>";
        }
        break;

    // AuthController Akcje
    case 'login': $authController->showLoginForm(); break;
    case 'login_process': $authController->processLogin(); break;
    case 'register': $authController->showRegistrationForm(); break;
    case 'register_process': $authController->processRegistration(); break;
    case 'logout': $authController->logout(); break;

    // CategoryController Akcje (Admin)
    case 'categories_list': AuthController::checkAdmin(); $categoryController->index(); break;
    case 'category_create_form': AuthController::checkAdmin(); $categoryController->createForm(); break;
    case 'category_store': AuthController::checkAdmin(); $categoryController->store(); break;
    case 'category_edit_form': AuthController::checkAdmin(); $categoryController->editForm(); break;
    case 'category_update': AuthController::checkAdmin(); $categoryController->update(); break;
    case 'category_delete': AuthController::checkAdmin(); $categoryController->delete(); break;

    // ToolController Akcje (Admin)
    case 'tools_list': AuthController::checkAdmin(); $toolController->index(); break;
    case 'tool_create_form': AuthController::checkAdmin(); $toolController->createForm(); break;
    case 'tool_store': AuthController::checkAdmin(); $toolController->store(); break;
    case 'tool_edit_form': AuthController::checkAdmin(); $toolController->editForm(); break;
    case 'tool_update': AuthController::checkAdmin(); $toolController->update(); break;
    case 'tool_delete': AuthController::checkAdmin(); $toolController->delete(); break;

    // ToolController Akcje (Publiczne)
    case 'tools_public_list':
        $toolController->publicList();
        break;
    case 'show_tool_details': 
        $toolController->showToolDetails();
        break;

    default:
        http_response_code(404);
        echo "<h1>Błąd 404: Strona nie znaleziona</h1>";
        echo "<p>Akcja '<strong>" . htmlspecialchars($action) . "</strong>' nie została znaleziona.</p>";
        echo '<p><a href="index.php?action=home">Powrót na stronę główną</a></p>';
        break;
}

$mainContent = ob_get_clean(); // Pobierz zawartość bufora i wyczyść go

// ----- WYŚWIETLANIE PASKA INFORMACYJNEGO I GŁÓWNEJ ZAWARTOŚCI -----

// Pasek informacyjny
if (isset($_SESSION['user_id'])) {
    // Pasek dla zalogowanego użytkownika
    echo "<div style='background-color: #e0e0e0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
    echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
    echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
    echo "Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']);
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        echo " | <a href='index.php?action=categories_list'>Kategorie (Admin)</a>";
        echo " | <a href='index.php?action=tools_list'>Narzędzia (Admin)</a>";
    }
    // Tutaj można dodać link do panelu "Moje Wypożyczenia" dla zwykłego użytkownika
    // else { echo " | <a href='index.php?action=my_rentals'>Moje Wypożyczenia</a>"; }
    echo " | <a href='index.php?action=logout'>Wyloguj się</a>";
    echo "</div>";
} else {
    // Pasek dla niezalogowanego użytkownika
    echo "<div style='background-color: #f0f0f0; padding: 10px; text-align: right; border-bottom: 1px solid #ccc;'>";
    echo "<a href='index.php?action=home' style='text-decoration:none; color:black; margin-right:15px;'>Strona główna</a>";
    echo "<a href='index.php?action=tools_public_list' style='text-decoration:none; color:black; margin-right:15px;'>Narzędzia</a>";
    echo " | <a href='index.php?action=login'>Zaloguj się</a> | <a href='index.php?action=register'>Zarejestruj się</a>";
    echo "</div>";
}

// Wyświetl główną zawartość (wygenerowaną przez switch)
echo $mainContent;

// Globalna stopka (opcjonalnie)
// echo "<footer style='text-align:center; padding: 20px; border-top: 1px solid #ccc; margin-top: 30px;'><p>&copy; " . date('Y') . " Toolsy - Wypożyczalnia Narzędzi</p></footer>";

?>