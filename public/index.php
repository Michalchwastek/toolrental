<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definicje ścieżek
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');

// Ładowanie konfiguracji i kontrolerów
require_once CONFIG_PATH . '/database.php';
require_once SRC_PATH . '/Controllers/AuthController.php';
require_once SRC_PATH . '/Controllers/CategoryController.php';
require_once SRC_PATH . '/Controllers/ToolController.php'; 
require_once SRC_PATH . '/Controllers/RentalController.php';

$action = $_GET['action'] ?? 'home';

// Instancje kontrolerów
$authController = new AuthController();
$categoryController = new CategoryController();
$toolController = new ToolController(); 
$rentalController = new RentalController();

ob_start(); // Włącz buforowanie wyjścia

switch ($action) {
    case 'home':
        // --- ZMIANA: DOŁĄCZAMY DEDYKOWANY WIDOK ---
        include VIEWS_PATH . '/home.php';
        // --- KONIEC ZMIANY ---
        break;

    // AuthController Akcje
    case 'login': $authController->showLoginForm(); break;
    case 'login_process': $authController->processLogin(); break;
    case 'register': $authController->showRegistrationForm(); break;
    case 'register_process': $authController->processRegistration(); break;
    case 'logout': $authController->logout(); break;
    case 'check_email_availability': $authController->checkEmailAvailability(); break;

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
    case 'tools_public_list': $toolController->publicList(); break;
    case 'show_tool_details': $toolController->showToolDetails(); break;

    // RentalController Akcje
    case 'process_rental': $rentalController->processRental(); break;
    case 'my_rentals': $rentalController->myRentals(); break;
    case 'process_return': $rentalController->processReturn(); break;

    default:
        http_response_code(404);
        // Użyjemy struktury strony dla błędu 404
        echo '<main class="container">'; // Otwieramy kontener
        echo '<div class="page-header"><h1>Błąd 404: Strona nie znaleziona</h1></div>';
        echo "<p class='alert alert-danger'>Przepraszamy, strona o akcji '<strong>" . htmlspecialchars($action) . "</strong>' nie została znaleziona.</p>";
        echo '<p style="text-align:center;"><a href="index.php?action=home" class="button-link secondary">Powrót na stronę główną</a></p>';
        echo '</main>'; // Zamykamy kontener
        break;
}

$mainContent = ob_get_clean(); 

// Pasek informacyjny
if (isset($_SESSION['user_id'])) {
    echo "<div class='user-info-bar'>";
    echo "<a href='index.php?action=home'>Strona główna</a>";
    echo "<a href='index.php?action=tools_public_list'>Narzędzia</a>";
    echo "<span style='margin-right:auto;'>Zalogowany jako: <strong>" . htmlspecialchars($_SESSION['user_imie']) . "</strong> (" . htmlspecialchars($_SESSION['user_email']) . ") - Rola: " . htmlspecialchars($_SESSION['user_role']) . "</span>"; 
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        echo "<a href='index.php?action=categories_list'>Kategorie</a>";
        echo "<a href='index.php?action=tools_list'>Narzędzia (Adm)</a>";
    }
    echo "<a href='index.php?action=my_rentals'>Moje Wypożyczenia</a>"; 
    echo "<a href='index.php?action=logout'>Wyloguj się</a>";
    echo "</div>";
} else {
    echo "<div class='user-info-bar'>";
    echo "<a href='index.php?action=home' style='margin-right:auto;'>Strona główna</a>";
    echo "<a href='index.php?action=tools_public_list'>Narzędzia</a>";
    echo "<a href='index.php?action=login'>Zaloguj się</a>";
    echo "<a href='index.php?action=register'>Zarejestruj się</a>";
    echo "</div>";
}

echo $mainContent;

echo "<footer><p>&copy; " . date('Y') . " Toolsy - Wypożyczalnia Narzędzi</p></footer>";
?>