<?php

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Tool.php';
require_once SRC_PATH . '/Models/Category.php'; // Potrzebne do pobrania listy kategorii dla formularzy

class ToolController {
    private $db;
    private $toolModel;
    private $categoryModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->toolModel = new Tool($this->db);
        $this->categoryModel = new Category($this->db); // Inicjalizujemy model kategorii
    }

    // Wyświetl listę wszystkich narzędzi
    public function index() {
        $tools = $this->toolModel->getAll();
        // Ten widok zaraz stworzymy: views/tools/index.php
        include VIEWS_PATH . '/tools/index.php';
    }

    // Wyświetl formularz dodawania nowego narzędzia
    public function createForm() {
        $categories = $this->categoryModel->getAll(); // Pobierz wszystkie kategorie do selecta
        // Ten widok zaraz stworzymy: views/tools/create.php
        include VIEWS_PATH . '/tools/create.php';
    }

    // Przetwórz dane z formularza dodawania narzędzia
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nazwa = trim($_POST['nazwa_narzedzia'] ?? '');
            $opis = trim($_POST['opis_narzedzia'] ?? null);
            $id_kategorii = (int)($_POST['id_kategorii'] ?? 0);
            $cena_za_dobe = trim($_POST['cena_za_dobe'] ?? '0');
            // Konwersja ceny na odpowiedni format, np. zamiana przecinka na kropkę
            $cena_za_dobe = str_replace(',', '.', $cena_za_dobe);

            // Dostępność - checkbox może nie być wysłany, jeśli nie jest zaznaczony
            $dostepnosc = isset($_POST['dostepnosc']) ? true : false; 
            $zdjecie_url = trim($_POST['zdjecie_url'] ?? null);
            $errors = [];

            if (empty($nazwa)) $errors[] = "Nazwa narzędzia jest wymagana.";
            if ($id_kategorii <= 0) $errors[] = "Kategoria jest wymagana.";
            if (!is_numeric($cena_za_dobe) || floatval($cena_za_dobe) < 0) $errors[] = "Cena za dobę musi być poprawną liczbą nieujemną.";
            // Można dodać więcej walidacji (np. dla URL zdjęcia)

            if (empty($errors)) {
                if ($this->toolModel->create($nazwa, $opis, $id_kategorii, floatval($cena_za_dobe), $dostepnosc, $zdjecie_url)) {
                    header('Location: index.php?action=tools_list&status=created');
                    exit;
                } else {
                    $errors[] = "Nie udało się dodać narzędzia. Spróbuj ponownie.";
                }
            }
            // Jeśli błędy, wyświetl formularz ponownie z błędami i danymi
            $categories = $this->categoryModel->getAll(); // Ponownie pobierz kategorie
            include VIEWS_PATH . '/tools/create.php';
        } else {
            header('Location: index.php?action=tool_create_form');
            exit;
        }
    }

    // Wyświetl formularz edycji narzędzia
    public function editForm() {
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) {
            echo "Nieprawidłowe ID narzędzia."; return;
        }
        $tool = $this->toolModel->getById($id_narzedzia);
        if (!$tool) {
            echo "Narzędzie nie znalezione."; return;
        }
        $categories = $this->categoryModel->getAll(); // Pobierz kategorie do selecta
        // Ten widok zaraz stworzymy: views/tools/edit.php
        include VIEWS_PATH . '/tools/edit.php';
    }

    // Przetwórz dane z formularza edycji narzędzia
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            $nazwa = trim($_POST['nazwa_narzedzia'] ?? '');
            $opis = trim($_POST['opis_narzedzia'] ?? null);
            $id_kategorii = (int)($_POST['id_kategorii'] ?? 0);
            $cena_za_dobe = trim($_POST['cena_za_dobe'] ?? '0');
            $cena_za_dobe = str_replace(',', '.', $cena_za_dobe);
            $dostepnosc = isset($_POST['dostepnosc']) ? true : false;
            $zdjecie_url = trim($_POST['zdjecie_url'] ?? null);
            $errors = [];

            if ($id_narzedzia <= 0) $errors[] = "Nieprawidłowe ID narzędzia.";
            if (empty($nazwa)) $errors[] = "Nazwa narzędzia jest wymagana.";
            if ($id_kategorii <= 0) $errors[] = "Kategoria jest wymagana.";
            if (!is_numeric($cena_za_dobe) || floatval($cena_za_dobe) < 0) $errors[] = "Cena za dobę musi być poprawną liczbą nieujemną.";

            if (empty($errors)) {
                if ($this->toolModel->update($id_narzedzia, $nazwa, $opis, $id_kategorii, floatval($cena_za_dobe), $dostepnosc, $zdjecie_url)) {
                    header('Location: index.php?action=tools_list&status=updated');
                    exit;
                } else {
                    $errors[] = "Nie udało się zaktualizować narzędzia.";
                }
            }
            // Jeśli błędy, załaduj dane narzędzia i kategorie, wyświetl formularz edycji ponownie
            $tool = (array)$_POST; // Przekaż dane z formularza z powrotem
            $tool['id_narzedzia'] = $id_narzedzia; // Upewnij się, że ID jest przekazane
            $categories = $this->categoryModel->getAll();
            include VIEWS_PATH . '/tools/edit.php';
        } else {
            header('Location: index.php?action=tools_list');
            exit;
        }
    }

    // Przetwórz żądanie usunięcia narzędzia
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            if ($id_narzedzia > 0) {
                if ($this->toolModel->delete($id_narzedzia)) {
                    header('Location: index.php?action=tools_list&status=deleted');
                    exit;
                } else {
                    header('Location: index.php?action=tools_list&status=delete_error');
                    exit;
                }
            }
        }
        header('Location: index.php?action=tools_list');
        exit;
    }
}
?>