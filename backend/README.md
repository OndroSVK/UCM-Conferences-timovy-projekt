# UCM Conferences — backend

Laravel 13, PHP 8.5+, MariaDB. Rozhodnutia, moduly, otvorené otázky a stav sú v [docs/backend.md](../docs/backend.md).

## Funkčný frontend

Rozhranie je serverové Blade v `resources/views`; CSS a UI skript sú v `public/`. Registrácia má tri kroky a zhrnutie, účastník vlastný dashboard a zoznam príspevkov, administrácia databázový prehľad `/admin/overview`. Na údaje ani operácie sa nepoužíva starý `app.js` alebo browser storage. Verejné HTML makety v koreni a GitHub Pages sú samostatný dizajnový podklad.

Document root servera musí byť tento priečinok **`public/`**, nie koreň repozitára alebo Laravel projektu. Všetky údaje a oprávnenia poskytujú existujúce Actions. Recenzie, program, hromadné e-maily a serverový editor webu zatiaľ nemajú funkčné rozhranie. Jazyk je EN.

Lokálne overenie 16. 11. 2026: 271 testov / 662 assertions na PHP 8.5.4 a SQLite, Blade cache a JS syntax, kroky registrácie/login/dashboard/detail v Safari. MariaDB, DB worker/Mailpit a mobil tým nie sú overené. [Detail integrácie](../docs/prepojenie-frontendu-backendu.md).

## Spustenie lokálne

Docker prostredie (Debian 13, MariaDB, Mailpit) dodá tím (BR9). Dovtedy stačí PHP 8.5 s rozšíreniami `pdo_sqlite`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo` a Composer:

```sh
composer install
cp .env.example .env            # pre SQLite: DB_CONNECTION=sqlite, ostatné DB_* zmazať
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve               # http://localhost:8000/login
php artisan queue:work          # e-maily a faktúry, ak QUEUE_CONNECTION=database
```

Konferencie: `http://ans2027.localhost:8000` a `http://ans2028.localhost:8000` (registrácia na `/register`). Účty zo seedu: `superadmin@`, `organizer@`, `committee@`, `participant@`, `poster@`, `both@example.test`, heslo `SEED_USER_PASSWORD` (predvolene `password`, iba lokálne).

## MariaDB

Postup pripojenia, kontroly a testov na MariaDB je v [docs/prepojenie-databazy.md](../docs/prepojenie-databazy.md). Skrátene: `database/mariadb/create-database.sql`, hodnoty `DB_*` v `.env`, `php artisan migrate`, `php artisan conferences:db-check`.

## Skutočná inštalácia (bez seedu)

```sh
php artisan conferences:create-user admin@example.test "Meno Priezvisko" --superadmin   # na heslo sa opýta
php artisan conferences:create database/conference.example.json                       # vzor; skutočné údaje mimo Gitu
```

Ďalších pracovníkov pridá superadmin v obrazovke Team.

## Testy

```sh
vendor/bin/pest                                   # SQLite v pamäti
DB_CONNECTION=mariadb DB_DATABASE=ucm_conferences_test DB_USERNAME=ucm DB_PASSWORD=... vendor/bin/pest   # iba testovacia DB, testy ju mažú
```

## Mapa

| Cesta | Obsah |
|---|---|
| `app/Modules/<Modul>/Actions` | Jedna feature = jeden súbor (BR2) |
| `app/Modules/<Modul>/{Models,Enums,Events,Listeners,Database/Migrations}` | Zdieľané časti modulu |
| `app/Shared` | Kontext konferencie, matica oprávnení, Money, základ Action |
| `app/Http` | Controllery a middleware; volajú iba Actions |
| `config/modules.php` | Zapnuté moduly (Invoicing sa dá vypnúť, O32) |
| `config/conferences.php` | Limit prihlásenia (BO2), limit PDF (BO5) |
| `tests/` | `Modules` (features), `CrossCutting` (izolácia, oprávnenia, architektúra, E2E), `Http` |
