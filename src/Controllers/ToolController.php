<?php

// Upewnij się, że SRC_PATH jest zdefiniowane w public/index.php przed dołączeniem tego pliku
if (!defined('SRC_PATH')) {
    // Definicja awaryjna - niezalecane w produkcji, lepiej zapewnić definicję w punkcie wejścia
    define('SRC_PATH', dirname(__DIR__)); 
}
if (!defined('VIEWS_PATH')) {
    // Definicja awaryjna
    define('VIEWS_PATH', dirname(dirname(__DIR__)) . '/views');
}


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

    // Wyświetl listę wszystkich narzędzi (dla admina)
    public function index() {
        $tools = $this->toolModel->getAll();
        include VIEWS_PATH . '/tools/index.php';
    }

    // Wyświetl formularz dodawania nowego narzędzia (dla admina)
    public function createForm() {
        $categories = $this->categoryModel->getAll(); 
        $errors = $GLOBALS['errors'] ?? []; 
        $input = $GLOBALS['input'] ?? [];   
        include VIEWS_PATH . '/tools/create.php';
    }

    // Przetwórz dane z formularza dodawania narzędzia (dla admina)
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
            $GLOBALS['errors'] = $errors;
            $GLOBALS['input'] = $input; 
            $this->createForm(); 
        } else {
            header('Location: index.php?action=tool_create_form');
            exit;
        }
    }

    // Wyświetl formularz edycji narzędzia (dla admina)
    public function editForm() {
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) {
            echo "Nieprawidłowe ID narzędzia."; return;
        }
        
        $errors = $GLOBALS['errors'] ?? [];
        $input_data = $GLOBALS['input'] ?? null; // Zmieniono nazwę zmiennej dla jasności

        if ($input_data && isset($input_data['id_narzedzia']) && $input_data['id_narzedzia'] == $id_narzedzia) {
            $tool = $input_data; 
        } else {
            $tool = $this->toolModel->getById($id_narzedzia);
        }

        if (!$tool) {
            echo "Narzędzie nie znalezione."; return;
        }
        $categories = $this->categoryModel->getAll(); 
        include VIEWS_PATH . '/tools/edit.php';
    }

    // Przetwórz dane z formularza edycji narzędzia (dla admina)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_narzedzia = (int)($_POST['id_narzedzia'] ?? 0);
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

    // Usuń narzędzie (dla admina)
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

    // NOWA METODA: Publiczna lista narzędzi
    public function publicList() {
        $tools = $this->toolModel->getAll(); 
        // W przyszłości można dodać filtrowanie, np. tylko dostępne:
        // $tools = array_filter($tools, function($tool) { return $tool['dostepnosc'] == true; });
        include VIEWS_PATH . '/public_tools/list.php';
    }

    // NOWA METODA: Widok szczegółów narzędzia (publiczny)
    public function showToolDetails() {
        $id_narzedzia = (int)($_GET['id'] ?? 0);
        if ($id_narzedzia <= 0) {
            // Lepsza obsługa błędu
            http_response_code(400); // Bad Request
            echo "<h1>Błąd 400: Nieprawidłowe żądanie</h1><p>Nie podano poprawnego ID narzędzia.</p>";
            echo '<p><a href="index.php?action=tools_public_list">Powrót do listy narzędzi</a></p>';
            return;
        }
        $tool = $this->toolModel->getById($id_narzedzia);
        if (!$tool) {
            http_response_code(404); // Not Found
            echo "<h1>Błąd 404: Narzędzie nie znalezione</h1><p>Przepraszamy, narzędzie o podanym ID nie istnieje.</p>";
            echo '<p><a href="index.php?action=tools_public_list">Powrót do listy narzędzi</a></p>';
            return;
        }
        // Ten widok stworzymy w następnym kroku: views/public_tools/details.php
        include VIEWS_PATH . '/public_tools/details.php';
    }
}
?>