<?php

class Tool {
    private $db; // Obiekt połączenia PDO

    public $id_narzedzia;
    public $nazwa_narzedzia;
    public $opis_narzedzia;
    public $id_kategorii; // Klucz obcy
    public $nazwa_kategorii; // Do wyświetlania nazwy kategorii (z JOIN)
    public $cena_za_dobe;
    public $dostepnosc;
    public $zdjecie_url;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Pobierz wszystkie narzędzia (z informacją o kategorii)
    public function getAll() {
        $sql = "SELECT n.*, k.nazwa_kategorii 
                FROM Narzedzia n
                LEFT JOIN KategorieNarzędzi k ON n.id_kategorii = k.id_kategorii
                ORDER BY n.nazwa_narzedzia ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logowanie błędu
            // error_log("Błąd przy pobieraniu narzędzi: " . $e->getMessage());
            return [];
        }
    }

    // Pobierz jedno narzędzie po ID (z informacją o kategorii)
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
                // Możemy chcieć ustawić właściwości obiektu, jeśli jest taka potrzeba
                // $this->id_narzedzia = $tool['id_narzedzia'];
                // ... itd.
                return $tool; // Zwracamy tablicę asocjacyjną
            }
            return false;
        } catch (PDOException $e) {
            // error_log("Błąd przy pobieraniu narzędzia by ID: " . $e->getMessage());
            return false;
        }
    }

    // Stwórz nowe narzędzie
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
                // $this->id_narzedzia = $this->db->lastInsertId(); // Ustawiamy, jeśli obiekt ma reprezentować nowo utworzone narzędzie
                return $this->db->lastInsertId(); // Lub zwracamy ID
            }
            return false;
        } catch (PDOException $e) {
            // error_log("Błąd przy tworzeniu narzędzia: " . $e->getMessage());
            return false;
        }
    }

    // Zaktualizuj istniejące narzędzie
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
            // error_log("Błąd przy aktualizacji narzędzia: " . $e->getMessage());
            return false;
        }
    }

    // Usuń narzędzie
    public function delete($id) {
        $sql = "DELETE FROM Narzedzia WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_narzedzia', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Błąd przy usuwaniu narzędzia: " . $e->getMessage());
            return false;
        }
    }

    // Ustaw dostępność narzędzia
    public function setAvailability($id_narzedzia, $is_available) {
        $sql = "UPDATE Narzedzia SET dostepnosc = :dostepnosc WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dostepnosc', $is_available, PDO::PARAM_BOOL);
            $stmt->bindParam(':id_narzedzia', $id_narzedzia, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Błąd przy ustawianiu dostępności: " . $e->getMessage());
            return false;
        }
    }
}
?>