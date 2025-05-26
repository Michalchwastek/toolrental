<?php

// Ten plik powinien być dołączony wcześniej, np. w index.php lub przez autoloader
// require_once CONFIG_PATH . '/database.php'; 
// Na razie załadujemy go bezpośrednio w konstruktorze, ale to nie jest idealne

class Database {
    private static $instance = null;
    private $pdo;

    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;

    private function __construct() {
        // Dołączenie konfiguracji bazy danych, jeśli jeszcze nie załadowana
        if (!defined('DB_HOST')) {
            // Zakładamy, że CONFIG_PATH jest zdefiniowane globalnie lub musimy je tu przekazać
            // Dla uproszczenia, załóżmy, że index.php już zdefiniował CONFIG_PATH
            // i załadował plik database.php. Jeśli nie, to trzeba to zrobić.
            // Lepszym rozwiązaniem byłby autoloader i dependency injection.
            // require_once __DIR__ . '/../../config/database.php'; // Ścieżka względna
            // Poniżej jest bardziej uniwersalne, jeśli CONFIG_PATH jest zdefiniowane:
            if (defined('CONFIG_PATH')) {
                 require_once CONFIG_PATH . '/database.php';
                 // Uaktualnienie wartości, jeśli plik config został załadowany po inicjalizacji właściwości
                 $this->host = DB_HOST;
                 $this->port = DB_PORT;
                 $this->db_name = DB_NAME;
                 $this->username = DB_USER;
                 $this->password = DB_PASS;
            } else {
                // To jest sytuacja awaryjna - konfiguracja nie została załadowana
                die("Błąd krytyczny: Brak konfiguracji bazy danych.");
            }
        }


        $dsn = 'pgsql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name;
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Domyślnie pobieraj jako tablice asocjacyjne
        } catch (PDOException $e) {
            // W prawdziwej aplikacji logowalibyśmy błąd, a nie wyświetlali go użytkownikowi
            die("Błąd połączenia z bazą danych: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Można dodać pomocnicze metody do wykonywania zapytań, np. query(), prepare(), execute()
    // np. public function query($sql, $params = []) { ... }
}
?>