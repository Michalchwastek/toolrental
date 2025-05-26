<?php

class Tool {
    private $db; // Obiekt połączenia PDO

    public $id_narzedzia;
    public $nazwa_narzedzia;
    public $opis_narzedzia;
    public $id_kategorii; 
    public $nazwa_kategorii; 
    public $cena_za_dobe;
    public $dostepnosc;
    public $zdjecie_url;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // ZMODYFIKOWANA METODA: Pobierz wszystkie narzędzia, opcjonalnie filtrowane po kategorii
    public function getAll($id_kategorii_filter = null) {
        $sql = "SELECT n.*, k.nazwa_kategorii 
                FROM Narzedzia n
                LEFT JOIN KategorieNarzędzi k ON n.id_kategorii = k.id_kategorii";
        
        $params = [];
        if ($id_kategorii_filter !== null && $id_kategorii_filter > 0) {
            $sql .= " WHERE n.id_kategorii = :id_kategorii_filter";
            $params[':id_kategorii_filter'] = (int)$id_kategorii_filter;
        }
        
        $sql .= " ORDER BY n.nazwa_narzedzia ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Błąd przy pobieraniu narzędzi: " . $e->getMessage());
            return [];
        }
    }

    // ... (reszta metod: getById, create, update, delete, setAvailability - bez zmian od ostatniej wersji) ...
    public function getById($id) {
        $sql = "SELECT n.*, k.nazwa_kategorii 
                FROM Narzedzia n
                LEFT JOIN KategorieNarzędzi k ON n.id_kategorii = k.id_kategorii
                WHERE n.id_narzedzia = :id_narzedzia LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_narzedzia', $id, PDO::PARAM_INT);
            $stmt->execute();
            $tool = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tool) {
                return $tool;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function create($nazwa, $opis, $id_kategorii, $cena, $dostepnosc = true, $zdjecie = null) {
        $sql = "INSERT INTO Narzedzia (nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) 
                VALUES (:nazwa_narzedzia, :opis_narzedzia, :id_kategorii, :cena_za_dobe, :dostepnosc, :zdjecie_url)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nazwa_narzedzia', $nazwa);
            $stmt->bindParam(':opis_narzedzia', $opis);
            $stmt->bindParam(':id_kategorii', $id_kategorii, PDO::PARAM_INT);
            $stmt->bindParam(':cena_za_dobe', $cena); 
            $stmt->bindParam(':dostepnosc', $dostepnosc, PDO::PARAM_BOOL);
            $stmt->bindParam(':zdjecie_url', $zdjecie);
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $nazwa, $opis, $id_kategorii, $cena, $dostepnosc, $zdjecie = null) {
        $sql = "UPDATE Narzedzia 
                SET nazwa_narzedzia = :nazwa_narzedzia, 
                    opis_narzedzia = :opis_narzedzia, 
                    id_kategorii = :id_kategorii, 
                    cena_za_dobe = :cena_za_dobe, 
                    dostepnosc = :dostepnosc, 
                    zdjecie_url = :zdjecie_url
                WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nazwa_narzedzia', $nazwa);
            $stmt->bindParam(':opis_narzedzia', $opis);
            $stmt->bindParam(':id_kategorii', $id_kategorii, PDO::PARAM_INT);
            $stmt->bindParam(':cena_za_dobe', $cena);
            $stmt->bindParam(':dostepnosc', $dostepnosc, PDO::PARAM_BOOL);
            $stmt->bindParam(':zdjecie_url', $zdjecie);
            $stmt->bindParam(':id_narzedzia', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM Narzedzia WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_narzedzia', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setAvailability($id_narzedzia, $is_available) {
        $sql = "UPDATE Narzedzia SET dostepnosc = :dostepnosc WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dostepnosc', $is_available, PDO::PARAM_BOOL);
            $stmt->bindParam(':id_narzedzia', $id_narzedzia, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>