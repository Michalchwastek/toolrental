<?php

if (!defined('SRC_PATH')) define('SRC_PATH', dirname(__DIR__));
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', dirname(dirname(__DIR__)) . '/views');

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Rental.php';
require_once SRC_PATH . '/Models/Tool.php';

class RentalController {
    private $db;
    private $rentalModel;
    private $toolModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->rentalModel = new Rental($this->db);
        $this->toolModel = new Tool($this->db);
    }

    public function processRental() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login&error=not_logged_in_for_rental&tool_id=' . ($_POST['id_narzedzia'] ?? ''));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            $data_planowanego_zwrotu = trim($_POST['data_planowanego_zwrotu'] ?? '');
            $id_uzytkownika = $_SESSION['user_id'];
            $errors = [];

            if ($id_narzedzia <= 0) $errors[] = "Nieprawidłowe ID narzędzia.";
            if (empty($data_planowanego_zwrotu)) $errors[] = "Data planowanego zwrotu jest wymagana.";
            else {
                $today = date('Y-m-d');
                if ($data_planowanego_zwrotu <= $today) {
                    $errors[] = "Data planowanego zwrotu musi być datą przyszłą.";
                }
            }

            $tool = $this->toolModel->getById($id_narzedzia);
            if (!$tool) $errors[] = "Wybrane narzędzie nie istnieje.";
            elseif (!$tool['dostepnosc']) $errors[] = "To narzędzie jest obecnie niedostępne.";

            if (empty($errors)) {
                $this->db->beginTransaction();
                try {
                    $rentalId = $this->rentalModel->create($id_uzytkownika, $id_narzedzia, $data_planowanego_zwrotu);
                    $toolAvailabilityUpdated = $this->toolModel->setAvailability($id_narzedzia, false);

                    if ($rentalId && $toolAvailabilityUpdated) {
                        $this->db->commit();
                        header('Location: index.php?action=my_rentals&status=rented_successfully');
                        exit;
                    } else {
                        $this->db->rollBack();
                        $errors[] = "Wystąpił błąd podczas przetwarzania wypożyczenia. Spróbuj ponownie.";
                    }
                } catch (PDOException $e) {
                    $this->db->rollBack();
                    // error_log("Błąd PDO w processRental: " . $e->getMessage());
                    $errors[] = "Wystąpił krytyczny błąd serwera. Spróbuj ponownie później.";
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['rental_errors'] = $errors;
                $_SESSION['rental_form_data'] = $_POST; 
                $_SESSION['rental_form_data']['id_narzedzia_error_ref'] = $id_narzedzia; // Dodajemy referencję ID narzędzia
                header('Location: index.php?action=show_tool_details&id=' . $id_narzedzia . '&error=rental_failed');
                exit;
            }
        } else {
            header('Location: index.php?action=tools_public_list');
            exit;
        }
    }
    
    public function myRentals() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login&error=not_logged_in');
            exit;
        }
        $id_uzytkownika = $_SESSION['user_id'];
        $rentals = $this->rentalModel->getByUserId($id_uzytkownika);
        
        include VIEWS_PATH . '/rentals/my_rentals.php';
    }

    // NOWA METODA: Przetwórz zwrot narzędzia
    public function processReturn() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login&error=not_logged_in');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_wypozyczenia = (int)($_POST['id_wypozyczenia'] ?? 0);
            $id_narzedzia_zwracanego = (int)($_POST['id_narzedzia'] ?? 0); // ID narzędzia do zmiany dostępności
            $cena_za_dobe_zwracanego = floatval($_POST['cena_za_dobe'] ?? 0); // Cena za dobę do obliczeń

            if ($id_wypozyczenia <= 0 || $id_narzedzia_zwracanego <= 0 || $cena_za_dobe_zwracanego <= 0) {
                // Prosta walidacja - błąd, jeśli brakuje kluczowych danych
                header('Location: index.php?action=my_rentals&status=return_error_data');
                exit;
            }

            // Sprawdź, czy wypożyczenie należy do zalogowanego użytkownika (dla bezpieczeństwa)
            $rental_data = $this->rentalModel->getById($id_wypozyczenia);
            if (!$rental_data || $rental_data['id_uzytkownika'] != $_SESSION['user_id']) {
                header('Location: index.php?action=my_rentals&status=return_error_auth');
                exit;
            }
            // Sprawdź, czy narzędzie nie zostało już zwrócone
            if ($rental_data['status_wypozyczenia'] !== 'aktywne') {
                 header('Location: index.php?action=my_rentals&status=already_returned');
                 exit;
            }


            $this->db->beginTransaction();
            try {
                // 1. Zaktualizuj wpis w tabeli Wypozyczenia (status, data zwrotu, koszt)
                $rentalUpdated = $this->rentalModel->returnTool($id_wypozyczenia, $cena_za_dobe_zwracanego);
                
                // 2. Zmień status dostępności narzędzia na true
                $toolAvailabilityUpdated = $this->toolModel->setAvailability($id_narzedzia_zwracanego, true);

                if ($rentalUpdated && $toolAvailabilityUpdated) {
                    $this->db->commit();
                    header('Location: index.php?action=my_rentals&status=returned_successfully');
                    exit;
                } else {
                    $this->db->rollBack();
                    header('Location: index.php?action=my_rentals&status=return_error_db');
                    exit;
                }
            } catch (PDOException $e) {
                $this->db->rollBack();
                // error_log("Błąd PDO w processReturn: " . $e->getMessage());
                header('Location: index.php?action=my_rentals&status=return_error_server');
                exit;
            }
        } else {
            header('Location: index.php?action=my_rentals');
            exit;
        }
    }
}
?>