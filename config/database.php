<?php
// Konfiguracja połączenia z bazą danych PostgreSQL
define('DB_HOST', 'db'); // Nazwa serwisu Dockerowego dla PostgreSQL
define('DB_PORT', '5432');
define('DB_NAME', 'toolsy_db'); // Nazwa Twojej bazy danych
define('DB_USER', 'toolsy_user'); // Użytkownik bazy danych
define('DB_PASS', 'TwojeSuperSilneHaslo'); // Hasło użytkownika bazy - PAMIĘTAJ ZMIENIĆ NA TO, KTÓRE USTAWIAŁEŚ W docker-compose.yml!

// Opcjonalnie, DSN string dla PDO
// define('DB_DSN', 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);
?>