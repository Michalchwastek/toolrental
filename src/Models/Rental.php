<?php

class Rental {
    private $db; // Obiekt połączenia PDO

    public $id_wypozyczenia;
    public $id_uzytkownika;
    public $id_narzedzia;
    public $data_wypozyczenia;
    public $data_planowanego_zwrotu;
    public $data_rzeczywistego_zwrotu;
    public $status_wypozyczenia;
    public $calkowity_koszt;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Stwórz nowe wypożyczenie
    public function create($id_uzytkownika, $id_narzedzia, $data_planowanego_zwrotu) {
        $sql = "INSERT INTO Wypozyczenia (id_uzytkownika, id_narzedzia, data_planowanego_zwrotu, status_wypozyczenia) 
                VALUES (:id_uzytkownika, :id_narzedzia, :data_planowanego_zwrotu, :status_wypozyczenia)";
        
        $status_wypozyczenia = 'aktywne'; 
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_uzytkownika', $id_uzytkownika, PDO::PARAM_INT);
            $stmt->bindParam(':id_narzedzia', $id_narzedzia, PDO::PARAM_INT);
            $stmt->bindParam(':data_planowanego_zwrotu', $data_planowanego_zwrotu);
            $stmt->bindParam(':status_wypozyczenia', $status_wypozyczenia);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId(); 
            }
            return false;
        } catch (PDOException $e) {
            // error_log("Błąd przy tworzeniu wypożyczenia: " . $e->getMessage());
            return false;
        }
    }

    // Pobierz wypożyczenia danego użytkownika
    public function getByUserId($id_uzytkownika) {
        $sql = "SELECT w.*, n.nazwa_narzedzia, n.zdjecie_url, n.cena_za_dobe 
                FROM Wypozyczenia w
                JOIN Narzedzia n ON w.id_narzedzia = n.id_narzedzia
                WHERE w.id_uzytkownika = :id_uzytkownika
                ORDER BY w.data_wypozyczenia DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_uzytkownika', $id_uzytkownika, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Błąd przy pobieraniu wypożyczeń użytkownika: " . $e->getMessage());
            return [];
        }
    }
    
    // Pobierz jedno wypożyczenie po ID (aby uzyskać np. id_narzedzia przed aktualizacją)
    public function getById($id_wypozyczenia) {
        $sql = "SELECT * FROM Wypozyczenia WHERE id_wypozyczenia = :id_wypozyczenia LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_wypozyczenia', $id_wypozyczenia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Błąd przy pobieraniu wypożyczenia by ID: " . $e->getMessage());
            return false;
        }
    }

    // NOWA METODA: Oznacz narzędzie jako zwrócone i oblicz koszt
    public function returnTool($id_wypozyczenia, $cena_za_dobe) {
        // Pobierz dane wypożyczenia, aby obliczyć liczbę dni
        $rental_data = $this->getById($id_wypozyczenia);
        if (!$rental_data) {
            return false;
        }

        $data_wypozyczenia = new DateTime($rental_data['data_wypozyczenia']);
        $data_rzeczywistego_zwrotu_dt = new DateTime(); // Aktualna data i godzina jako data zwrotu
        $data_rzeczywistego_zwrotu_sql = $data_rzeczywistego_zwrotu_dt->format('Y-m-d H:i:s');

        // Oblicz liczbę dni wypożyczenia (zaokrąglamy w górę do pełnych dni)
        $interwal = $data_wypozyczenia->diff($data_rzeczywistego_zwrotu_dt);
        $dni_wypozyczenia = $interwal->days;
        if ($interwal->h > 0 || $interwal->i > 0 || $interwal->s > 0) { // Jeśli jest jakakolwiek część dnia, liczymy jako cały dzień
            $dni_wypozyczenia++;
        }
        if ($dni_wypozyczenia == 0) $dni_wypozyczenia = 1; // Minimum 1 dzień

        $obliczony_koszt = $dni_wypozyczenia * floatval($cena_za_dobe);
        $status_wypozyczenia = 'zakończone';

        $sql = "UPDATE Wypozyczenia 
                SET data_rzeczywistego_zwrotu = :data_rzeczywistego_zwrotu, 
                    status_wypozyczenia = :status_wypozyczenia,
                    calkowity_koszt = :calkowity_koszt
                WHERE id_wypozyczenia = :id_wypozyczenia";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':data_rzeczywistego_zwrotu', $data_rzeczywistego_zwrotu_sql);
            $stmt->bindParam(':status_wypozyczenia', $status_wypozyczenia);
            $stmt->bindParam(':calkowity_koszt', $obliczony_koszt);
            $stmt->bindParam(':id_wypozyczenia', $id_wypozyczenia, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log("Błąd przy aktualizacji wypożyczenia (zwrot): " . $e->getMessage());
            return false;
        }
    }

    public function getActiveRentalByToolId($id_narzedzia) {
        $sql = "SELECT * FROM Wypozyczenia 
                WHERE id_narzedzia = :id_narzedzia AND status_wypozyczenia = 'aktywne' 
                LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_narzedzia', $id_narzedzia, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Błąd przy pobieraniu aktywnego wypożyczenia: " . $e->getMessage());
            return false;
        }
    }
}
?>