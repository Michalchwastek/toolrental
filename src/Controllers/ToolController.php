<?php

// Te require_once zakładają, że SRC_PATH zostało poprawnie zdefiniowane
// w public/index.php i jest dostępne globalnie.
require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Tool.php';
require_once SRC_PATH . '/Models/Category.php';

class ToolController { // Ta linia powinna być znacznie wcześniej niż linia 20
    private $db;
    private $toolModel;
    private $categoryModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->toolModel = new Tool($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        $tools = $this->toolModel->getAll();
        include VIEWS_PATH . '/tools/index.php';
    }

    public function createForm() {
        $categories = $this->categoryModel->getAll();
        $errors = $GLOBALS['errors'] ?? []; // Dla wyświetlenia błędów, jeśli są
        $input = $GLOBALS['input'] ?? [];   // Dla zachowania wartości pól
        include VIEWS_PATH . '/tools/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Jeśli błędy, przekaż je i dane wejściowe z powrotem do formularza
            $GLOBALS['errors'] = $errors;
            $GLOBALS['input'] = $input; // Przekaż $input do widoku createForm
            $this->createForm(); // Wywołaj metodę, która dołączy widok z błędami
        } else {
            header('Location: index.php?action=tool_create_form');
            exit;
        }
    }

    public function editForm() {
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) {
            echo "Nieprawidłowe ID narzędzia."; return;
        }
        
        // Jeśli są błędy z poprzedniej próby update, użyj danych z $GLOBALS
        $errors = $GLOBALS['errors'] ?? [];
        $tool_data_from_post = $GLOBALS['input'] ?? null;

        if ($tool_data_from_post && isset($tool_data_from_post['id_narzedzia']) && $tool_data_from_post['id_narzedzia'] == $id_narzedzia) {
            $tool = $tool_data_from_post; // Użyj danych z POST, jeśli to ta sama edycja z błędami
        } else {
            $tool = $this->toolModel->getById($id_narzedzia);
        }

        if (!$tool) {
            echo "Narzędzie nie znalezione."; return;
        }
        $categories = $this->categoryModel->getAll();
        include VIEWS_PATH . '/tools/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
            $input = [
                'id_narzedzia' => $id_narzedzia, // Ważne dla przekazania z powrotem do formularza
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
            // Jeśli błędy, przekaż je i dane wejściowe z powrotem do formularza edycji
            $GLOBALS['errors'] = $errors;
            $GLOBALS['input'] = $input; // Przekaż $input do widoku editForm
            $this->editForm(); // Wywołaj metodę, która dołączy widok z błędami
        } else {
            header('Location: index.php?action=tools_list');
            exit;
        }
    }

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