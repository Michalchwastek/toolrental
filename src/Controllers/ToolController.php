<?php

if (!defined('SRC_PATH')) define('SRC_PATH', dirname(__DIR__));
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', dirname(dirname(__DIR__)) . '/views');

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Tool.php';
require_once SRC_PATH . '/Models/Category.php';

class ToolController {
    private $db;
    private $toolModel;
    private $categoryModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->toolModel = new Tool($this->db);
        $this->categoryModel = new Category($this->db);
    }

    // ... (metody admina: index, createForm, store, editForm, update, delete - bez zmian) ...
    public function index() {
        $tools = $this->toolModel->getAll();
        include VIEWS_PATH . '/tools/index.php';
    }
    public function createForm() {
        $categories = $this->categoryModel->getAll();
        $errors = $GLOBALS['errors'] ?? [];
        $input = $GLOBALS['input'] ?? [];
        include VIEWS_PATH . '/tools/create.php';
    }
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = [ /* ... */ ]; // Skrócone dla zwięzłości, kod bez zmian
            // ... (reszta kodu store bez zmian) ...
            // Pełny kod store z poprzedniej odpowiedzi:
            $input = [
                'nazwa_narzedzia' => trim($_POST['nazwa_narzedzia'] ?? ''),
                'opis_narzedzia' => trim($_POST['opis_narzedzia'] ?? null),
                'id_kategorii' => (int)($_POST['id_kategorii'] ?? 0),
                'cena_za_dobe' => trim($_POST['cena_za_dobe'] ?? '0'),
                'zdjecie_url' => trim($_POST['zdjecie_url'] ?? null)
            ];
            $dostepnosc = isset($_POST['dostepnosc']) ? true : false;
            $errors = [];

            if (empty($input['nazwa_narzedzia'])) $errors[] = "Nazwa narzędzia jest wymagana.";
            if ($input['id_kategorii'] <= 0) $errors[] = "Kategoria jest wymagana.";
            
            $cena_za_dobe_processed = str_replace(',', '.', $input['cena_za_dobe']);
            if (!is_numeric($cena_za_dobe_processed) || floatval($cena_za_dobe_processed) < 0) {
                $errors[] = "Cena za dobę musi być poprawną liczbą nieujemną.";
            }

            if (empty($errors)) {
                if ($this->toolModel->create(
                    $input['nazwa_narzedzia'], 
                    $input['opis_narzedzia'], 
                    $input['id_kategorii'], 
                    floatval($cena_za_dobe_processed), 
                    $dostepnosc, 
                    $input['zdjecie_url']
                )) {
                    header('Location: index.php?action=tools_list&status=created');
                    exit;
                } else {
                    $errors[] = "Nie udało się dodać narzędzia. Spróbuj ponownie.";
                }
            }
            $GLOBALS['errors'] = $errors;
            $GLOBALS['input'] = $input; 
            $this->createForm(); 
        } else {
            header('Location: index.php?action=tool_create_form');
            exit;
        }
    }
    public function editForm() {
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) { echo "Nieprawidłowe ID narzędzia."; return; }
        $errors = $GLOBALS['errors'] ?? [];
        $input_data = $GLOBALS['input'] ?? null;
        if ($input_data && isset($input_data['id_narzedzia']) && $input_data['id_narzedzia'] == $id_narzedzia) {
            $tool = $input_data; 
        } else {
            $tool = $this->toolModel->getById($id_narzedzia);
        }
        if (!$tool) { echo "Narzędzie nie znalezione."; return; }
        $categories = $this->categoryModel->getAll(); 
        include VIEWS_PATH . '/tools/edit.php';
    }
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            $input = [ /* ... */ ]; // Skrócone dla zwięzłości, kod bez zmian
            // ... (reszta kodu update bez zmian) ...
            // Pełny kod update z poprzedniej odpowiedzi:
            $input = [
                'id_narzedzia' => $id_narzedzia,
                'nazwa_narzedzia' => trim($_POST['nazwa_narzedzia'] ?? ''),
                'opis_narzedzia' => trim($_POST['opis_narzedzia'] ?? null),
                'id_kategorii' => (int)($_POST['id_kategorii'] ?? 0),
                'cena_za_dobe' => trim($_POST['cena_za_dobe'] ?? '0'),
                'zdjecie_url' => trim($_POST['zdjecie_url'] ?? null)
            ];
            $dostepnosc = isset($_POST['dostepnosc']) ? true : false;
            $errors = [];

            if ($id_narzedzia <= 0) $errors[] = "Nieprawidłowe ID narzędzia.";
            if (empty($input['nazwa_narzedzia'])) $errors[] = "Nazwa narzędzia jest wymagana.";
            if ($input['id_kategorii'] <= 0) $errors[] = "Kategoria jest wymagana.";

            $cena_za_dobe_processed = str_replace(',', '.', $input['cena_za_dobe']);
            if (!is_numeric($cena_za_dobe_processed) || floatval($cena_za_dobe_processed) < 0) {
                $errors[] = "Cena za dobę musi być poprawną liczbą nieujemną.";
            }

            if (empty($errors)) {
                if ($this->toolModel->update(
                    $id_narzedzia, 
                    $input['nazwa_narzedzia'], 
                    $input['opis_narzedzia'], 
                    $input['id_kategorii'], 
                    floatval($cena_za_dobe_processed), 
                    $dostepnosc, 
                    $input['zdjecie_url']
                )) {
                    header('Location: index.php?action=tools_list&status=updated');
                    exit;
                } else {
                    $errors[] = "Nie udało się zaktualizować narzędzia.";
                }
            }
            $GLOBALS['errors'] = $errors;
            $GLOBALS['input'] = $input; 
            $this->editForm(); 
        } else {
            header('Location: index.php?action=tools_list');
            exit;
        }
    }
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            if ($id_narzedzia > 0) { /* ... */ } // Skrócone, kod bez zmian
            // ... (reszta kodu delete bez zmian) ...
            // Pełny kod delete:
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


    // Publiczna lista narzędzi - ZMODYFIKOWANA
    public function publicList() {
        $category_id_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        
        $tools = $this->toolModel->getAll($category_id_filter); 
        $categories = $this->categoryModel->getAll(); // Pobierz wszystkie kategorie do wyświetlenia filtrów
        $selected_category_name = null;
        if ($category_id_filter && !empty($categories)) {
            foreach ($categories as $cat) {
                if ($cat['id_kategorii'] == $category_id_filter) {
                    $selected_category_name = $cat['nazwa_kategorii'];
                    break;
                }
            }
        }

        include VIEWS_PATH . '/public_tools/list.php';
    }

    // Widok szczegółów narzędzia (publiczny) - bez zmian
    public function showToolDetails() {
        // ... (kod metody showToolDetails bez zmian od ostatniej wersji) ...
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) {
            http_response_code(400); 
            echo "<h1>Błąd 400: Nieprawidłowe żądanie</h1><p>Nie podano poprawnego ID narzędzia.</p>";
            echo '<p><a href="index.php?action=tools_public_list">Powrót do listy narzędzi</a></p>';
            return;
        }
        $tool = $this->toolModel->getById($id_narzedzia);
        if (!$tool) {
            http_response_code(404); 
            echo "<h1>Błąd 404: Narzędzie nie znalezione</h1><p>Przepraszamy, narzędzie o podanym ID nie istnieje.</p>";
            echo '<p><a href="index.php?action=tools_public_list">Powrót do listy narzędzi</a></p>';
            return;
        }
        include VIEWS_PATH . '/public_tools/details.php';
    }
}
?>