-- Usuwamy tabele w odwrotnej kolejności tworzenia, jeśli istnieją (dla łatwiejszego testowania)
DROP TABLE IF EXISTS Wypozyczenia CASCADE;
DROP TABLE IF EXISTS Narzedzia CASCADE;
DROP TABLE IF EXISTS KategorieNarzędzi CASCADE;
DROP TABLE IF EXISTS Uzytkownicy CASCADE;

-- Tabela Uzytkownicy
CREATE TABLE Uzytkownicy (
    id_uzytkownika SERIAL PRIMARY KEY,
    imie VARCHAR(100) NOT NULL,
    nazwisko VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    haslo_hash VARCHAR(255) NOT NULL,
    rola VARCHAR(50) NOT NULL DEFAULT 'user', -- Domyślnie 'user', może być też 'admin'
    data_rejestracji TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Tabela KategorieNarzędzi
CREATE TABLE KategorieNarzędzi (
    id_kategorii SERIAL PRIMARY KEY,
    nazwa_kategorii VARCHAR(100) NOT NULL UNIQUE,
    opis_kategorii TEXT
);

-- Tabela Narzedzia
CREATE TABLE Narzedzia (
    id_narzedzia SERIAL PRIMARY KEY,
    nazwa_narzedzia VARCHAR(255) NOT NULL,
    opis_narzedzia TEXT,
    id_kategorii INTEGER NOT NULL REFERENCES KategorieNarzędzi(id_kategorii),
    cena_za_dobe DECIMAL(10, 2) NOT NULL,
    dostepnosc BOOLEAN NOT NULL DEFAULT TRUE,
    zdjecie_url VARCHAR(500)
);

-- Tabela Wypozyczenia
CREATE TABLE Wypozyczenia (
    id_wypozyczenia SERIAL PRIMARY KEY,
    id_uzytkownika INTEGER NOT NULL REFERENCES Uzytkownicy(id_uzytkownika),
    id_narzedzia INTEGER NOT NULL REFERENCES Narzedzia(id_narzedzia),
    data_wypozyczenia TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    data_planowanego_zwrotu DATE NOT NULL,
    data_rzeczywistego_zwrotu TIMESTAMP WITH TIME ZONE, -- NULL jeśli jeszcze nie zwrócono
    status_wypozyczenia VARCHAR(50) NOT NULL DEFAULT 'aktywne', -- np. 'aktywne', 'zakończone', 'opóźnione'
    calkowity_koszt DECIMAL(10, 2) -- Może być obliczany później lub przy zwrocie
);

-- Dodatkowe komentarze lub indeksy można dodać tutaj w przyszłości, jeśli będą potrzebne.
-- Np. indeks na email w tabeli Uzytkownicy jest tworzony automatycznie przez UNIQUE,
-- ale można by dodać indeksy na klucze obce dla wydajności, choć przy małej bazie nie jest to krytyczne.

-- Przykładowe dane (opcjonalnie, na razie tworzymy tylko schemat):
/*
INSERT INTO Uzytkownicy (imie, nazwisko, email, haslo_hash, rola) VALUES
('Jan', 'Kowalski', 'jan.kowalski@example.com', 'jakishash1', 'user'),
('Admin', 'Toolsy', 'admin@example.com', 'jakishash2', 'admin');

INSERT INTO KategorieNarzędzi (nazwa_kategorii, opis_kategorii) VALUES
('Wiertarki', 'Różnego rodzaju wiertarki i wkrętarki'),
('Narzędzia ogrodowe', 'Narzędzia do pracy w ogrodzie');
*/