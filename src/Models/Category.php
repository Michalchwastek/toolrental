<?php

class Category {
    private $db; // Obiekt połączenia PDO

    public $id_kategorii;
    public $nazwa_kategorii;
    public $opis_kategorii;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Pobierz wszystkie kategorie
    public function getAll() {
        $sql = "SELECT * FROM KategorieNarzędzi ORDER BY nazwa_kategorii ASC";
        try {
            $stmt = $this->db->query($sql); // Proste zapytanie, bo bez parametrów od użytkownika
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logowanie błędu
            // echo "Błąd przy pobieraniu kategorii: " . $e->getMessage(); // Tylko do debugowania
            return []; // Zwróć pustą tablicę w przypadku błędu
        }
    }

    // Pobierz jedną kategorię po ID
    public function getById($id) {
        $sql = "SELECT * FROM KategorieNarzędzi WHERE id_kategorii = :id_kategorii LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_kategorii', $id, PDO::PARAM_INT);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($category) {
                $this->id_kategorii = $category['id_kategorii'];
                $this->nazwa_kategorii = $category['nazwa_kategorii'];
                $this->opis_kategorii = $category['opis_kategorii'];
                return $category;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Stwórz nową kategorię
    public function create($nazwa, $opis = null) {
        $sql = "INSERT INTO KategorieNarzędzi (nazwa_kategorii, opis_kategorii) 
                VALUES (:nazwa_kategorii, :opis_kategorii)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nazwa_kategorii', $nazwa);
            $stmt->bindParam(':opis_kategorii', $opis);

            if ($stmt->execute()) {
                $this->id_kategorii = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Możliwy błąd: nazwa_kategorii nie jest unikalna
            return false;
        }
    }

    // Zaktualizuj istniejącą kategorię
    public function update($id, $nazwa, $opis = null) {
        $sql = "UPDATE KategorieNarzędzi 
                SET nazwa_kategorii = :nazwa_kategorii, opis_kategorii = :opis_kategorii 
                WHERE id_kategorii = :id_kategorii";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nazwa_kategorii', $nazwa);
            $stmt->bindParam(':opis_kategorii', $opis);
            $stmt->bindParam(':id_kategorii', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Usuń kategorię
    public function delete($id) {
        // UWAGA: Przed usunięciem kategorii warto sprawdzić, czy nie ma do niej przypisanych narzędzi!
        // Na razie proste usuwanie. W przyszłości można dodać walidację lub ustawiać FK na SET NULL / CASCADE.
        $sql = "DELETE FROM KategorieNarzędzi WHERE id_kategorii = :id_kategorii";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_kategorii', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Możliwy błąd, jeśli istnieją narzędzia przypisane do tej kategorii
            // (jeśli FK ma ograniczenie RESTRICT)
            return false;
        }
    }
}
?>