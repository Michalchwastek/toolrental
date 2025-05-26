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
            // echo "Błąd przy pobieraniu narzędzi: " . $e->getMessage();
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
                $this->id_narzedzia = $tool['id_narzedzia'];
                $this->nazwa_narzedzia = $tool['nazwa_narzedzia'];
                $this->opis_narzedzia = $tool['opis_narzedzia'];
                $this->id_kategorii = $tool['id_kategorii'];
                $this->nazwa_kategorii = $tool['nazwa_kategorii'];
                $this->cena_za_dobe = $tool['cena_za_dobe'];
                $this->dostepnosc = $tool['dostepnosc'];
                $this->zdjecie_url = $tool['zdjecie_url'];
                return $tool;
            }
            return false;
        } catch (PDOException $e) {
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
            $stmt->bindParam(':cena_za_dobe', $cena); // PDO samo wykryje typ dla decimal
            $stmt->bindParam(':dostepnosc', $dostepnosc, PDO::PARAM_BOOL);
            $stmt->bindParam(':zdjecie_url', $zdjecie);

            if ($stmt->execute()) {
                $this->id_narzedzia = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // echo "Błąd przy tworzeniu narzędzia: " . $e->getMessage(); // Debug
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
            // echo "Błąd przy aktualizacji narzędzia: " . $e->getMessage(); // Debug
            return false;
        }
    }

    // Usuń narzędzie
    public function delete($id) {
        // UWAGA: Przed usunięciem narzędzia warto sprawdzić, czy nie ma aktywnych wypożyczeń!
        $sql = "DELETE FROM Narzedzia WHERE id_narzedzia = :id_narzedzia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_narzedzia', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Możliwy błąd, jeśli istnieją wypożyczenia tego narzędzia (jeśli FK ma ograniczenie RESTRICT)
            return false;
        }
    }
}
?>