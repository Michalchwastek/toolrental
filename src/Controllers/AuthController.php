<?php

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/User.php';

class AuthController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function showRegistrationForm() {
        // Jeśli kod debugujący był tu, upewnij się, że jest usunięty lub zakomentowany
        include VIEWS_PATH . '/auth/register.php';
    }

    public function processRegistration() {
        $errors = []; 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imie = trim($_POST['imie'] ?? '');
            $nazwisko = trim($_POST['nazwisko'] ?? '');
            $email = trim($_POST['email'] ?? '');
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
                    // Przekierowanie na stronę logowania z komunikatem o sukcesie
                    // Użyjemy parametru GET do przekazania komunikatu
                    header('Location: index.php?action=login&status=registered');
                    exit;
                } else {
                    $errors[] = "Nie udało się zarejestrować użytkownika. Możliwe, że email jest już zajęty.";
                }
            }
        }
        // Jeśli błędy, wyświetl formularz ponownie (przekazując $errors)
        include VIEWS_PATH . '/auth/register.php';
    }

    public function showLoginForm() {
        $errors = []; // Inicjalizacja tablicy błędów dla widoku logowania
        $success_message = ''; // Inicjalizacja komunikatu o sukcesie

        // Sprawdź, czy jest komunikat o pomyślnej rejestracji
        if (isset($_GET['status']) && $_GET['status'] === 'registered') {
            $success_message = "Rejestracja zakończona pomyślnie! Możesz się teraz zalogować.";
        }
        // Tutaj można też obsłużyć inne statusy, np. wylogowanie

        // Odkomentuj wyświetlanie błędów i sukcesu w pliku login.php
        include VIEWS_PATH . '/auth/login.php';
    }

    public function processLogin() {
        $errors = [];
        $email_value = ''; // Do przechowania emaila przy błędnym logowaniu

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $haslo = $_POST['haslo'] ?? '';
            $email_value = $email; // Zapisz email do ponownego wyświetlenia

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
                    // Logowanie pomyślne - ustaw dane sesji
                    $_SESSION['user_id'] = $loggedInUser['id_uzytkownika'];
                    $_SESSION['user_email'] = $loggedInUser['email'];
                    $_SESSION['user_role'] = $loggedInUser['rola'];
                    $_SESSION['user_imie'] = $loggedInUser['imie'];

                    // Przekieruj na stronę główną lub dashboard
                    header('Location: index.php?action=home&status=loggedin');
                    exit;
                } else {
                    $errors[] = "Nieprawidłowy email lub hasło.";
                }
            }
        }
        // Jeśli błędy lub nie POST, wyświetl formularz logowania ponownie
        // Przekaż $errors i $email_value do widoku login.php
        include VIEWS_PATH . '/auth/login.php';
    }

    // Metoda wylogowania (dodamy później)
    public function logout() {
        session_destroy();
        header('Location: index.php?action=home&status=loggedout');
        exit;
    }
}
?>