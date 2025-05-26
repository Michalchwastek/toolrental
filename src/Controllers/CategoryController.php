<?php

require_once SRC_PATH . '/Core/Database.php';
require_once SRC_PATH . '/Models/Category.php';

class CategoryController {
    private $db;
    private $categoryModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->categoryModel = new Category($this->db);
    }

    // Wyświetl listę wszystkich kategorii
    public function index() {
        $categories = $this->categoryModel->getAll();
        // Dołączamy widok, przekazując do niego listę kategorii
        // Ten widok zaraz stworzymy: views/categories/index.php
        include VIEWS_PATH . '/categories/index.php';
    }

    // Wyświetl formularz dodawania nowej kategorii
    public function createForm() {
        // Dołączamy widok: views/categories/create.php
        include VIEWS_PATH . '/categories/create.php';
    }

    // Przetwórz dane z formularza dodawania kategorii
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nazwa = trim($_POST['nazwa_kategorii'] ?? '');
            $opis = trim($_POST['opis_kategorii'] ?? null);
            $errors = [];

            if (empty($nazwa)) {
                $errors[] = "Nazwa kategorii jest wymagana.";
            }
            // Można dodać więcej walidacji

            if (empty($errors)) {
                if ($this->categoryModel->create($nazwa, $opis)) {
                    header('Location: index.php?action=categories_list&status=created');
                    exit;
                } else {
                    $errors[] = "Nie udało się dodać kategorii. Możliwe, że nazwa już istnieje.";
                }
            }
            // Jeśli błędy, wyświetl formularz ponownie z błędami
            include VIEWS_PATH . '/categories/create.php';
        } else {
            // Jeśli nie POST, przekieruj do formularza
            header('Location: index.php?action=category_create_form');
            exit;
        }
    }

    // Wyświetl formularz edycji kategorii
    public function editForm() {
        $id_kategorii = (int)($_GET['id'] ?? 0);
        if ($id_kategorii <= 0) {
            echo "Nieprawidłowe ID kategorii."; // Prosta obsługa błędu
            return;
        }
        $category = $this->categoryModel->getById($id_kategorii);
        if (!$category) {
            echo "Kategoria nie znaleziona.";
            return;
        }
        // Dołączamy widok: views/categories/edit.php
        include VIEWS_PATH . '/categories/edit.php';
    }

    // Przetwórz dane z formularza edycji kategorii
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_kategorii = (int)($_POST['id_kategorii'] ?? 0);
            $nazwa = trim($_POST['nazwa_kategorii'] ?? '');
            $opis = trim($_POST['opis_kategorii'] ?? null);
            $errors = [];

            if ($id_kategorii <= 0) {
                $errors[] = "Nieprawidłowe ID kategorii.";
            }
            if (empty($nazwa)) {
                $errors[] = "Nazwa kategorii jest wymagana.";
            }

            if (empty($errors)) {
                if ($this->categoryModel->update($id_kategorii, $nazwa, $opis)) {
                    header('Location: index.php?action=categories_list&status=updated');
                    exit;
                } else {
                    $errors[] = "Nie udało się zaktualizować kategorii. Możliwe, że nowa nazwa już istnieje lub wystąpił inny błąd.";
                }
            }
            // Jeśli błędy, załaduj dane kategorii i wyświetl formularz edycji ponownie
            $category = ['id_kategorii' => $id_kategorii, 'nazwa_kategorii' => $nazwa, 'opis_kategorii' => $opis]; // Przekaż dane z powrotem
            include VIEWS_PATH . '/categories/edit.php';
        } else {
            header('Location: index.php?action=categories_list'); // Przekieruj jeśli nie POST
            exit;
        }
    }

    // Przetwórz żądanie usunięcia kategorii
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Używamy POST dla bezpieczeństwa przy usuwaniu
            $id_kategorii = (int)($_POST['id_kategorii'] ?? 0);
            if ($id_kategorii > 0) {
                if ($this->categoryModel->delete($id_kategorii)) {
                    header('Location: index.php?action=categories_list&status=deleted');
                    exit;
                } else {
                    // Błąd usuwania (np. istnieją narzędzia w tej kategorii)
                    header('Location: index.php?action=categories_list&status=delete_error');
                    exit;
                }
            }
        }
        // Jeśli nie POST lub błąd ID, przekieruj do listy
        header('Location: index.php?action=categories_list');
        exit;
    }
}
?>