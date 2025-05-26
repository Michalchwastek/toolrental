<?php

// Wymagane klasy - w przyszłości autoloader
// Upewnij się, że stała SRC_PATH jest zdefiniowana w public/index.php przed dołączeniem tego pliku
if (!defined('SRC_PATH')) {
    // Definicja awaryjna, jeśli plik jest wywoływany w innym kontekście,
    // ale najlepiej, aby SRC_PATH było zdefiniowane globalnie przez index.php
    define('SRC_PATH', dirname(__DIR__)); // Zakłada, że Controllers jest w src/, a src/ jest o jeden poziom wyżej
}

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/User.php';

class AuthController {
    
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function showRegistrationForm() {
        $errors = $GLOBALS['errors'] ?? []; 
        $input = $GLOBALS['input'] ?? [];
        include VIEWS_PATH . '/auth/register.php';
    }

    public function processRegistration() {
        $errors = []; 
        $input = [
            'imie' => trim($_POST['imie'] ?? ''),
            'nazwisko' => trim($_POST['nazwisko'] ?? ''),
            'email' => trim($_POST['email'] ?? '')
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imie = $input['imie'];
            $nazwisko = $input['nazwisko'];
            $email = $input['email'];
            $haslo = $_POST['haslo'] ?? '';
            $haslo_confirm = $_POST['haslo_confirm'] ?? '';

            if (empty($imie)) $errors[] = "Imię jest wymagane.";
            if (empty($nazwisko)) $errors[] = "Nazwisko jest wymagane.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Niepoprawny format email.";
            if (empty($haslo)) $errors[] = "Hasło jest wymagane.";
            if (strlen($haslo) < 6) $errors[] = "Hasło musi mieć co najmniej 6 znaków.";
            if ($haslo !== $haslo_confirm) $errors[] = "Hasła nie są takie same.";

            if (empty($errors)) {
                $user = new User($this->db);
                if ($user->create($imie, $nazwisko, $email, $haslo)) {
                    header('Location: index.php?action=login&status=registered');
                    exit;
                } else {
                    $errors[] = "Nie udało się zarejestrować użytkownika. Możliwe, że email jest już zajęty.";
                }
            }
        }
        $GLOBALS['errors'] = $errors;
        $GLOBALS['input'] = $input; 
        include VIEWS_PATH . '/auth/register.php';
    }

    public function showLoginForm() {
        $errors = $GLOBALS['errors'] ?? []; 
        $email_value = $GLOBALS['email_value'] ?? ''; 
        $success_message = ''; 

        if (isset($_GET['status']) && $_GET['status'] === 'registered') {
            $success_message = "Rejestracja zakończona pomyślnie! Możesz się teraz zalogować.";
        }
        
        include VIEWS_PATH . '/auth/login.php';
    }

    public function processLogin() {
        $errors = [];
        $email_value = trim($_POST['email'] ?? ''); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $email_value;
            $haslo = $_POST['haslo'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Niepoprawny format email.";
            }
            if (empty($haslo)) {
                $errors[] = "Hasło jest wymagane.";
            }

            if (empty($errors)) {
                $userModel = new User($this->db);
                $loggedInUser = $userModel->authenticate($email, $haslo);

                if ($loggedInUser) {
                    $_SESSION['user_id'] = $loggedInUser['id_uzytkownika'];
                    $_SESSION['user_email'] = $loggedInUser['email'];
                    $_SESSION['user_role'] = $loggedInUser['rola'];
                    $_SESSION['user_imie'] = $loggedInUser['imie'];
                    
                    header('Location: index.php?action=home&status=loggedin');
                    exit;
                } else {
                    $errors[] = "Nieprawidłowy email lub hasło.";
                }
            }
        }
        $GLOBALS['errors'] = $errors;
        $GLOBALS['email_value'] = $email_value; 
        include VIEWS_PATH . '/auth/login.php';
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: index.php?action=home&status=loggedout');
        exit;
    }

    // STATYCZNA METODA DO SPRAWDZANIA UPRAWNIEŃ ADMINA
    public static function checkAdmin() { // <-- KLUCZOWE JEST "static"
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); 
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403); // Forbidden
            echo "<!DOCTYPE html><html lang='pl'><head><meta charset='UTF-8'><title>Brak uprawnień</title><link rel='stylesheet' href='css/style.css'></head><body>"; // Zakładamy, że css/style.css jest dostępne z głównego katalogu public
            echo "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>"; // Dodano font-family dla lepszego wyglądu
            echo "<h1>Brak uprawnień (403 Forbidden)</h1>";
            echo "<p>Nie masz uprawnień do dostępu do tej strony. Tylko administratorzy mogą tu wejść.</p>";
            echo '<p><a href="index.php?action=home" style="color: #007bff; text-decoration: none;">Powrót na stronę główną</a></p>'; // Dodano prosty styl dla linku
            echo "</div>";
            echo "</body></html>";
            exit; 
        }
        return true;
    }
}
?>