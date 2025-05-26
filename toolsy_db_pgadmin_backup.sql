--
-- PostgreSQL database dump
--

-- Dumped from database version 15.13
-- Dumped by pg_dump version 15.12

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA public;


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- Name: logujzmianedostepnoscinarzędzia(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public."logujzmianedostepnoscinarzędzia"() RETURNS trigger
    LANGUAGE plpgsql
    AS '
DECLARE
    kto_zmienil_info TEXT;
BEGIN
    -- Spróbujmy pobrać informację o aktualnie zalogowanym użytkowniku z sesji PostgreSQL
    -- To jest bardziej zaawansowane i wymaga ustawienia zmiennej sesyjnej przez aplikację, np. SET app.current_user_id = ''123'';
    -- Na razie użyjemy prostego tekstu lub CURRENT_USER, jeśli jest to użytkownik bazy danych.
    -- Dla prostoty, na razie zostawmy to jako ''System'' lub pobierzmy nazwę użytkownika bazy.
    BEGIN
        kto_zmienil_info := current_setting(''app.user_id'', true); -- ''true'' oznacza, że nie rzuci błędu, jeśli zmienna nie istnieje
        IF kto_zmienil_info IS NULL THEN
            kto_zmienil_info := CURRENT_USER; -- Użytkownik bazy danych
        END IF;
    EXCEPTION WHEN OTHERS THEN
        kto_zmienil_info := ''Nieznany (błąd pobierania app.user_id) lub '' || CURRENT_USER;
    END;


    -- Logujemy tylko jeśli wartość ''dostepnosc'' faktycznie się zmieniła
    -- OLD odnosi się do wartości wiersza PRZED operacją UPDATE
    -- NEW odnosi się do wartości wiersza PO operacji UPDATE
    IF TG_OP = ''UPDATE'' THEN
        IF OLD.dostepnosc IS DISTINCT FROM NEW.dostepnosc THEN
            INSERT INTO LogiDostepnosciNarzędzi (id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, kto_zmienil)
            VALUES (NEW.id_narzedzia, OLD.dostepnosc, NEW.dostepnosc, kto_zmienil_info);
        END IF;
    ELSIF TG_OP = ''INSERT'' THEN
        -- Przy INSERT, logujemy nową dostępność (poprzedniej nie było)
        INSERT INTO LogiDostepnosciNarzędzi (id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, kto_zmienil)
        VALUES (NEW.id_narzedzia, NULL, NEW.dostepnosc, kto_zmienil_info);
    END IF;
    
    RETURN NEW; -- Dla wyzwalaczy AFTER ROW, wartość zwrotna jest ignorowana, ale dla BEFORE ROW może modyfikować NEW.
                -- Tutaj jest to wyzwalacz AFTER, ale dobra praktyka.
END;
';


--
-- Name: obliczdniwypozyczenia(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.obliczdniwypozyczenia(data_start timestamp with time zone) RETURNS integer
    LANGUAGE plpgsql
    AS '
BEGIN
    RETURN ObliczDniWypozyczenia(data_start, CURRENT_TIMESTAMP);
END;
';


--
-- Name: obliczdniwypozyczenia(timestamp with time zone, timestamp with time zone); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.obliczdniwypozyczenia(data_start timestamp with time zone, data_koniec timestamp with time zone) RETURNS integer
    LANGUAGE plpgsql
    AS '
DECLARE
    dni_wypozyczenia integer;
BEGIN
    IF data_koniec IS NULL OR data_koniec < data_start THEN
        RETURN 0; 
    END IF;
    SELECT CEIL(EXTRACT(EPOCH FROM (data_koniec - data_start)) / 86400.0) INTO dni_wypozyczenia;
    IF dni_wypozyczenia = 0 AND (data_koniec >= data_start) THEN
      IF EXTRACT(EPOCH FROM (data_koniec - data_start)) > 0 THEN
        RETURN 1;
      ELSE
        RETURN 1; 
      END IF;
    END IF;
    IF dni_wypozyczenia < 1 AND (data_koniec >= data_start) THEN
        RETURN 1;
    END IF;
    RETURN dni_wypozyczenia;
END;
';


--
-- Name: narzedzia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.narzedzia (
    id_narzedzia integer NOT NULL,
    nazwa_narzedzia character varying(255) NOT NULL,
    opis_narzedzia text,
    id_kategorii integer NOT NULL,
    cena_za_dobe numeric(10,2) NOT NULL,
    dostepnosc boolean DEFAULT true NOT NULL,
    zdjecie_url character varying(500)
);


--
-- Name: uzytkownicy; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.uzytkownicy (
    id_uzytkownika integer NOT NULL,
    imie character varying(100) NOT NULL,
    nazwisko character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    haslo_hash character varying(255) NOT NULL,
    rola character varying(50) DEFAULT 'user'::character varying NOT NULL,
    data_rejestracji timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: wypozyczenia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wypozyczenia (
    id_wypozyczenia integer NOT NULL,
    id_uzytkownika integer NOT NULL,
    id_narzedzia integer NOT NULL,
    data_wypozyczenia timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    data_planowanego_zwrotu date NOT NULL,
    data_rzeczywistego_zwrotu timestamp with time zone,
    status_wypozyczenia character varying(50) DEFAULT 'aktywne'::character varying NOT NULL,
    calkowity_koszt numeric(10,2)
);


--
-- Name: aktywnewypozyczeniainfo; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.aktywnewypozyczeniainfo AS
 SELECT w.id_wypozyczenia,
    n.nazwa_narzedzia,
    n.id_narzedzia,
    u.imie AS uzytkownik_imie,
    u.nazwisko AS uzytkownik_nazwisko,
    u.email AS uzytkownik_email,
    w.data_wypozyczenia,
    w.data_planowanego_zwrotu,
    w.status_wypozyczenia
   FROM ((public.wypozyczenia w
     JOIN public.narzedzia n ON ((w.id_narzedzia = n.id_narzedzia)))
     JOIN public.uzytkownicy u ON ((w.id_uzytkownika = u.id_uzytkownika)))
  WHERE ((w.status_wypozyczenia)::text = 'aktywne'::text)
  ORDER BY w.data_planowanego_zwrotu;


--
-- Name: kategorienarzędzi; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public."kategorienarzędzi" (
    id_kategorii integer NOT NULL,
    nazwa_kategorii character varying(100) NOT NULL,
    opis_kategorii text
);


--
-- Name: kategorienarzędzi_id_kategorii_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public."kategorienarzędzi_id_kategorii_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: kategorienarzędzi_id_kategorii_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public."kategorienarzędzi_id_kategorii_seq" OWNED BY public."kategorienarzędzi".id_kategorii;


--
-- Name: logidostepnoscinarzędzi; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public."logidostepnoscinarzędzi" (
    id_logu integer NOT NULL,
    id_narzedzia integer NOT NULL,
    poprzednia_dostepnosc boolean,
    nowa_dostepnosc boolean NOT NULL,
    czas_zmiany timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    kto_zmienil character varying(255)
);


--
-- Name: logidostepnoscinarzędzi_id_logu_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public."logidostepnoscinarzędzi_id_logu_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logidostepnoscinarzędzi_id_logu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public."logidostepnoscinarzędzi_id_logu_seq" OWNED BY public."logidostepnoscinarzędzi".id_logu;


--
-- Name: narzedzia_id_narzedzia_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.narzedzia_id_narzedzia_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: narzedzia_id_narzedzia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.narzedzia_id_narzedzia_seq OWNED BY public.narzedzia.id_narzedzia;


--
-- Name: uzytkownicy_id_uzytkownika_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.uzytkownicy_id_uzytkownika_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: uzytkownicy_id_uzytkownika_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.uzytkownicy_id_uzytkownika_seq OWNED BY public.uzytkownicy.id_uzytkownika;


--
-- Name: wypozyczenia_id_wypozyczenia_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.wypozyczenia_id_wypozyczenia_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: wypozyczenia_id_wypozyczenia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.wypozyczenia_id_wypozyczenia_seq OWNED BY public.wypozyczenia.id_wypozyczenia;


--
-- Name: kategorienarzędzi id_kategorii; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."kategorienarzędzi" ALTER COLUMN id_kategorii SET DEFAULT nextval('public."kategorienarzędzi_id_kategorii_seq"'::regclass);


--
-- Name: logidostepnoscinarzędzi id_logu; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."logidostepnoscinarzędzi" ALTER COLUMN id_logu SET DEFAULT nextval('public."logidostepnoscinarzędzi_id_logu_seq"'::regclass);


--
-- Name: narzedzia id_narzedzia; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.narzedzia ALTER COLUMN id_narzedzia SET DEFAULT nextval('public.narzedzia_id_narzedzia_seq'::regclass);


--
-- Name: uzytkownicy id_uzytkownika; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.uzytkownicy ALTER COLUMN id_uzytkownika SET DEFAULT nextval('public.uzytkownicy_id_uzytkownika_seq'::regclass);


--
-- Name: wypozyczenia id_wypozyczenia; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wypozyczenia ALTER COLUMN id_wypozyczenia SET DEFAULT nextval('public.wypozyczenia_id_wypozyczenia_seq'::regclass);


--
-- Data for Name: kategorienarzędzi; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public."kategorienarzędzi" (id_kategorii, nazwa_kategorii, opis_kategorii) VALUES (2, 'Wiertarki', 'Różnego rodzaju wiertarki, wkrętarki i młotowiertarki.');
INSERT INTO public."kategorienarzędzi" (id_kategorii, nazwa_kategorii, opis_kategorii) VALUES (3, 'Narzędzia Ogrodowe', 'Kosiarki, podkaszarki, nożyce do żywopłotu');
INSERT INTO public."kategorienarzędzi" (id_kategorii, nazwa_kategorii, opis_kategorii) VALUES (4, 'Piły', 'Piły tarczowe, ukośnice, wyrzynarki.');
INSERT INTO public."kategorienarzędzi" (id_kategorii, nazwa_kategorii, opis_kategorii) VALUES (5, 'Narzędzia Pomiarowe', 'Miary, poziomice, dalmierze laserowe.');
INSERT INTO public."kategorienarzędzi" (id_kategorii, nazwa_kategorii, opis_kategorii) VALUES (6, 'Szlifierki', 'Szlifierki kątowe, oscylacyjne, taśmowe.');


--
-- Data for Name: logidostepnoscinarzędzi; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (1, 1, NULL, true, '2025-05-26 16:10:20.061537+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (2, 2, NULL, true, '2025-05-26 17:40:32.673022+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (3, 3, NULL, true, '2025-05-26 17:41:27.457545+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (4, 4, NULL, true, '2025-05-26 17:43:02.399758+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (5, 5, NULL, true, '2025-05-26 17:44:22.235845+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (6, 1, true, false, '2025-05-26 17:46:08.086175+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (7, 6, NULL, true, '2025-05-26 17:47:03.718361+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (8, 7, NULL, true, '2025-05-26 17:48:10.065599+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (9, 8, NULL, true, '2025-05-26 17:49:15.670257+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (10, 9, NULL, true, '2025-05-26 17:50:40.771022+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (11, 10, NULL, true, '2025-05-26 17:52:06.581955+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (12, 11, NULL, true, '2025-05-26 17:53:09.334485+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (13, 12, NULL, true, '2025-05-26 17:54:56.264467+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (14, 13, NULL, true, '2025-05-26 17:56:07.007197+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (15, 14, NULL, true, '2025-05-26 17:57:01.869506+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (16, 15, NULL, true, '2025-05-26 17:57:59.942323+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (17, 16, NULL, true, '2025-05-26 17:58:44.950047+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (18, 7, true, false, '2025-05-26 18:21:44.91347+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (19, 5, true, false, '2025-05-26 18:22:57.047389+00', 'toolsy_user');
INSERT INTO public."logidostepnoscinarzędzi" (id_logu, id_narzedzia, poprzednia_dostepnosc, nowa_dostepnosc, czas_zmiany, kto_zmienil) VALUES (20, 5, false, true, '2025-05-26 18:23:00.90143+00', 'toolsy_user');


--
-- Data for Name: narzedzia; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (2, 'Wiertarka udarowa kompaktowa', 'Lekka i poręczna wiertarka udarowa o mocy 600W, idealna do wiercenia w betonie, drewnie i metalu. Posiada regulację prędkości i wygodny uchwyt. Doskonała do domowych remontów i drobnych prac montażowych', 2, 20.00, true, 'https://cdn.doktormlotek.pl/images/0/d086d9ad617a2bf1/25/wiertarka-udarowa-bosch-pbh-2100-re-compact-06033a9320.jpg?hash=-1483792443');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (3, 'Wkrętarka akumulatorowa 18V', 'Profesjonalna młotowiertarka z uchwytem SDS-Plus, przeznaczona do kucia, wiercenia z udarem i bez udaru. Energia udaru 2.7J zapewnia efektywną pracę w twardym betonie. W zestawie dłuto i wiertła', 2, 25.00, true, 'https://jcb-tools.pl/img/product_media/1400001-1401000/64829_1.JPG');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (4, 'Młotowiertarka SDS-Plus', 'Profesjonalna młotowiertarka z uchwytem SDS-Plus, przeznaczona do kucia, wiercenia z udarem i bez udaru. Energia udaru 2.7J zapewnia efektywną pracę w twardym betonie. W zestawie dłuto i wiertła.', 2, 40.00, true, 'https://www.dewalt.com.pl/1328-large_default/d25333k-mlotowiertarka-sds-plus-30mm.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (1, 'Kosiarka spalinowa', '', 3, 100.00, false, 'https://ocdn.eu/pulscms-transforms/1/5nHk9kpTURBXy80NTIxYzFmYjg2ODQwNTI1NjNiZTBiODIzYTIyODI2YS5qcGeSlQPNAhoAzRd6zQ01kwXNAu7NAZDeAAGhMAE');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (6, 'Podkaszarka elektryczna', 'Lekka podkaszarka elektryczna o mocy 350W do precyzyjnego przycinania trawy w trudno dostępnych miejscach, wokół drzew i krawędzi trawnika. Regulowany uchwyt i głowica tnąca', 3, 55.00, true, 'https://www.narzedzia.pl/photo/product/bosch-art-24-2-60771-f-sk7-w780-h554_1.png');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (8, 'Piła tarczowa ręczna', 'Ręczna piła tarczowa o mocy 1200W z tarczą 185mm. Umożliwia precyzyjne cięcie proste i pod kątem do 45 stopni. Głębokość cięcia do 65mm. Idealna do cięcia desek, blatów i płyt', 4, 35.00, true, 'https://market-fachowiec.pl/1304-large_default/p1560-s-97240-pilarka-tarczowa-185-190mm-1500w-css190.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (9, 'Ukośnica z posuwem', 'Precyzyjna ukośnica z posuwem i tarczą 216mm. Pozwala na cięcie szerokich elementów oraz cięcia kątowe i skośne. Wyposażona w laserowy wskaźnik linii cięcia.', 4, 150.00, true, 'https://robo-kop.com.pl/moduly/sklep/UserFiles/44370/zdjecie_big.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (10, 'Wyrzynarka elektryczna', 'Wszechstronna wyrzynarka o mocy 500W z regulacją prędkości i kąta cięcia. Umożliwia precyzyjne wycinanie krzywoliniowe w drewnie, metalu i tworzywach sztucznych. System szybkiej wymiany brzeszczotów', 4, 25.00, true, 'https://phuabc.com.pl/img/imagecache/2001-3000/product-media/wyrzynarka-elektryczna-650W-JV0600K-11113-540x540.webp');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (11, 'Dalmierz laserowy 50m', 'Kompaktowy dalmierz laserowy o zasięgu do 50 metrów. Umożliwia pomiar odległości, powierzchni i objętości. Funkcja Pitagorasa. Wysoka dokładność pomiaru +/- 2mm.', 5, 15.00, true, 'https://wypozyczalnia.delmet.pl/18463-large_default/dalmierz-laserowy-50m-dwo3050.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (12, 'Poziomica laserowa krzyżowa', 'Samopoziomująca poziomica laserowa wyświetlająca precyzyjne linie krzyżowe (pozioma i pionowa). Zasięg do 15m. Idealna do prac montażowych, układania płytek, wieszania obrazów', 5, 25.00, true, 'https://tooles.pl/hpeciai/9f59f9def025fbc562aaad09dd68b630/pol_pl_Poziomica-laserowa-krzyzowa-10m-samopoziomujaca-statyw-JCB-3191_1.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (13, 'Elektroniczna suwmiarka cyfrowa', 'Precyzyjna suwmiarka cyfrowa o zakresie pomiarowym 0-150mm z dokładnością 0.01mm. Wyświetlacz LCD ułatwia odczyt. Możliwość pomiaru wewnętrznego, zewnętrznego i głębokości.', 5, 110.00, true, 'https://www.oberon.com.pl/uploads/modules/productCatalogue/s-cal-evo-proximity-um-efba510e281113265721.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (14, 'Szlifierka kątowa 125mm', 'Uniwersalna szlifierka kątowa o mocy 750W, obsługująca tarcze o średnicy 125mm. Przeznaczona do cięcia i szlifowania metalu, betonu oraz innych materiałów. Boczny uchwyt dla lepszej kontroli.', 6, 105.00, true, 'https://robo-kop.com.pl/moduly/sklep/UserFiles/36033/zdjecie_big.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (15, 'Szlifierka oscylacyjna typu "delta"', 'Kompaktowa szlifierka oscylacyjna typu delta, idealna do precyzyjnego szlifowania narożników, krawędzi i trudno dostępnych miejsc. System mocowania papieru ściernego na rzep.', 6, 20.00, true, 'https://ferm-polska.pl/wp-content/uploads/2021/03/F-DSM1009-00.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (16, 'Szlifierka taśmowa', 'Wydajna szlifierka taśmowa o mocy 900W z taśmą o wymiarach 75x533mm. Przeznaczona do szybkiego usuwania materiału z dużych, płaskich powierzchni drewnianych. Worek na pył w zestawie.', 6, 90.00, true, 'https://www.narzedziak.pl/media/products/4649/images/thumbnail/big_9032.webp?lm=1739126816');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (7, 'Nożyce do żywopłotu akumulatorowe', 'Bezprzewodowe nożyce do żywopłotu zasilane akumulatorem 18V. Długość ostrza 50cm, umożliwiają cięcie gałęzi do 16mm średnicy. Lekkie i ergonomiczne, zapewniają komfortową pracę.', 3, 30.00, false, 'https://www.makita.sklep.pl/images/makita/nozyczki/watermark/wpx_b16c3468d6e087556545ffb6aa3489e9.jpg');
INSERT INTO public.narzedzia (id_narzedzia, nazwa_narzedzia, opis_narzedzia, id_kategorii, cena_za_dobe, dostepnosc, zdjecie_url) VALUES (5, 'Kosiarka spalinowa z napędem', 'Wydajna kosiarka spalinowa z silnikiem o mocy 3.5KM i napędem na tylne koła. Szerokość koszenia 46cm, centralna regulacja wysokości koszenia i duży kosz na trawę (60L). Idealna do trawników o powierzchni do 1200 m²', 3, 130.00, true, 'https://upload.cdn.baselinker.com/products/2008188/35653814d5c0d8bf63a4f1084194fd24.jpg');


--
-- Data for Name: uzytkownicy; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.uzytkownicy (id_uzytkownika, imie, nazwisko, email, haslo_hash, rola, data_rejestracji) VALUES (1, 'Admin', 'Admin', 'admin@admin.pl', '$2y$10$SR76mixMhscN7M/OA.yTrOlWzYmpFkayFNHpqn2A8tKIrnEyMIOOi', 'admin', '2025-05-26 15:46:36.818774+00');
INSERT INTO public.uzytkownicy (id_uzytkownika, imie, nazwisko, email, haslo_hash, rola, data_rejestracji) VALUES (2, 'Stefan', 'Stefanowski', 'stefan@wp.pl', '$2y$10$EERF1UuyNNhw.UpGluiaseoW/PLqOWBN/B5Hs909fauFPpzFtj6cS', 'user', '2025-05-26 18:07:56.904827+00');


--
-- Data for Name: wypozyczenia; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.wypozyczenia (id_wypozyczenia, id_uzytkownika, id_narzedzia, data_wypozyczenia, data_planowanego_zwrotu, data_rzeczywistego_zwrotu, status_wypozyczenia, calkowity_koszt) VALUES (1, 1, 7, '2025-05-26 18:21:44.91347+00', '2025-06-05', NULL, 'aktywne', NULL);
INSERT INTO public.wypozyczenia (id_wypozyczenia, id_uzytkownika, id_narzedzia, data_wypozyczenia, data_planowanego_zwrotu, data_rzeczywistego_zwrotu, status_wypozyczenia, calkowity_koszt) VALUES (2, 2, 5, '2025-05-26 18:22:57.047389+00', '2025-06-01', '2025-05-26 18:23:00+00', 'zakończone', 130.00);


--
-- Name: kategorienarzędzi_id_kategorii_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public."kategorienarzędzi_id_kategorii_seq"', 6, true);


--
-- Name: logidostepnoscinarzędzi_id_logu_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public."logidostepnoscinarzędzi_id_logu_seq"', 20, true);


--
-- Name: narzedzia_id_narzedzia_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.narzedzia_id_narzedzia_seq', 16, true);


--
-- Name: uzytkownicy_id_uzytkownika_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.uzytkownicy_id_uzytkownika_seq', 2, true);


--
-- Name: wypozyczenia_id_wypozyczenia_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.wypozyczenia_id_wypozyczenia_seq', 2, true);


--
-- Name: kategorienarzędzi kategorienarzędzi_nazwa_kategorii_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."kategorienarzędzi"
    ADD CONSTRAINT "kategorienarzędzi_nazwa_kategorii_key" UNIQUE (nazwa_kategorii);


--
-- Name: kategorienarzędzi kategorienarzędzi_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."kategorienarzędzi"
    ADD CONSTRAINT "kategorienarzędzi_pkey" PRIMARY KEY (id_kategorii);


--
-- Name: logidostepnoscinarzędzi logidostepnoscinarzędzi_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."logidostepnoscinarzędzi"
    ADD CONSTRAINT "logidostepnoscinarzędzi_pkey" PRIMARY KEY (id_logu);


--
-- Name: narzedzia narzedzia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.narzedzia
    ADD CONSTRAINT narzedzia_pkey PRIMARY KEY (id_narzedzia);


--
-- Name: uzytkownicy uzytkownicy_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.uzytkownicy
    ADD CONSTRAINT uzytkownicy_email_key UNIQUE (email);


--
-- Name: uzytkownicy uzytkownicy_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.uzytkownicy
    ADD CONSTRAINT uzytkownicy_pkey PRIMARY KEY (id_uzytkownika);


--
-- Name: wypozyczenia wypozyczenia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wypozyczenia
    ADD CONSTRAINT wypozyczenia_pkey PRIMARY KEY (id_wypozyczenia);


--
-- Name: narzedzia trigger_pozmianiedostepnoscinarzędzia; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER "trigger_pozmianiedostepnoscinarzędzia" AFTER INSERT OR UPDATE OF dostepnosc ON public.narzedzia FOR EACH ROW EXECUTE FUNCTION public."logujzmianedostepnoscinarzędzia"();


--
-- Name: logidostepnoscinarzędzi logidostepnoscinarzędzi_id_narzedzia_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."logidostepnoscinarzędzi"
    ADD CONSTRAINT "logidostepnoscinarzędzi_id_narzedzia_fkey" FOREIGN KEY (id_narzedzia) REFERENCES public.narzedzia(id_narzedzia);


--
-- Name: narzedzia narzedzia_id_kategorii_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.narzedzia
    ADD CONSTRAINT narzedzia_id_kategorii_fkey FOREIGN KEY (id_kategorii) REFERENCES public."kategorienarzędzi"(id_kategorii);


--
-- Name: wypozyczenia wypozyczenia_id_narzedzia_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wypozyczenia
    ADD CONSTRAINT wypozyczenia_id_narzedzia_fkey FOREIGN KEY (id_narzedzia) REFERENCES public.narzedzia(id_narzedzia);


--
-- Name: wypozyczenia wypozyczenia_id_uzytkownika_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wypozyczenia
    ADD CONSTRAINT wypozyczenia_id_uzytkownika_fkey FOREIGN KEY (id_uzytkownika) REFERENCES public.uzytkownicy(id_uzytkownika);


--
-- PostgreSQL database dump complete
--

