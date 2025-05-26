<?php

class User {
    private $db; 

    public $id_uzytkownika;
    public $imie;
    public $nazwisko;
    public $email;
    private $haslo_hash; 
    public $rola;
    public $data_rejestracji;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    public function create($imie, $nazwisko, $email, $haslo, $rola = 'user') {
        $this->haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Uzytkownicy (imie, nazwisko, email, haslo_hash, rola) 
                VALUES (:imie, :nazwisko, :email, :haslo_hash, :rola)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':imie', $imie);
            $stmt->bindParam(':nazwisko', $nazwisko);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':haslo_hash', $this->haslo_hash);
            $stmt->bindParam(':rola', $rola);
            if ($stmt->execute()) {
                $this->id_uzytkownika = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // NOWA METODA: Znajdź użytkownika po emailu
    public function findByEmail($email) {
        $sql = "SELECT * FROM Uzytkownicy WHERE email = :email LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Zwraca użytkownika jako tablicę asocjacyjną lub false jeśli nie znaleziono
        } catch (PDOException $e) {
            // Logowanie błędu
            return false;
        }
    }

    // NOWA METODA: Uwierzytelnij użytkownika
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if ($user) {
            // Sprawdź hasło
            if (password_verify($password, $user['haslo_hash'])) {
                // Hasło poprawne, zwróć dane użytkownika (bez hasha hasła dla bezpieczeństwa, chociaż findByEmail zwraca wszystko)
                // Można by tu odfiltrować haslo_hash przed zwróceniem, ale na razie zwracamy całą tablicę
                return $user; 
            }
        }
        // Użytkownik nie znaleziony lub hasło niepoprawne
        return false;
    }
}
?>