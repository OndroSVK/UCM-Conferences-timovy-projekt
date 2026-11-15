# Prepojenie databázy MariaDB s backendom — manuál pre AI

Stav k 15. 11. 2026. Tento dokument je zadanie pre AI asistenta (a člena tímu, ktorý s ním pracuje), ktorý má pripojiť databázu MariaDB k backendu v priečinku `/backend` a overiť, že na nej funguje. Pred prácou si prečítaj aj [AGENTS.md](../AGENTS.md) a [backend.md](backend.md); pri rozpore platí `backend.md` a treba ho nahlásiť.

## 1. Východiskový stav

- Backend je Laravel 13 na PHP 8.5. Všetkých 260 automatických testov prechádza, ale **iba na SQLite**. Na MariaDB backend ešte nikdy nebežal; to je predmet tejto úlohy (otvorená otázka BO14 v `backend.md`).
- Schému databázy tvorí 21 migrácií. Cieľová databáza je MariaDB na Debiane 13 (R35).
- Pripojenie je pripravené: ovládač `mariadb` v `backend/config/database.php` (utf8mb4, strict, InnoDB), vzorové hodnoty v `backend/.env.example`, skript `backend/database/mariadb/create-database.sql` a kontrolný príkaz `php artisan conferences:db-check`.
- Docker prostredie v repozitári nie je. Ak ho člen tímu má, použi ho; ak nie, stačí lokálna MariaDB.

## 2. Pravidlá, ktoré nesmieš porušiť

1. **Tabuľky vznikajú iba migráciami** (`php artisan migrate`). Nevytváraj ich ručným SQL, importom ani podľa PDF návrhu. Databáza vytvorená inak nebude sedieť s kódom.
2. **Existujúce migrácie needituj** — sú už na GitHube. Zmena schémy je nová migrácia v priečinku `Database/Migrations` modulu, ktorý tabuľku vlastní (sekcia 7), s novším dátumom v názve súboru.
3. Ak niečo na MariaDB zlyhá, **neupravuj obchodnú logiku ani testy, aby prešli**. Zisti príčinu; ak je chyba v migrácii alebo dopyte, oprav ju najmenšou možnou zmenou a pridaj test. Ak nevieš, nahlás to.
4. **Testy nikdy nespúšťaj nad vývojovou alebo ostrou databázou.** Každý beh ju celú zmaže. Použi samostatnú `ucm_conferences_test` (sekcia 5).
5. Do Gitu nepatrí `.env`, heslá, výpisy databázy ani skutočné osobné údaje. Seed obsahuje iba vymyslené účty na doméne `example.test`.
6. Stĺpce stavov (`registrations.status`, `registrations.payment_status`, `submissions.status`) zapisuje iba `Workflow\Actions\ChangeStatus`. Nemeň ich priamo v databáze ani v novom kóde.
7. Každá doménová tabuľka má `conference_id`. Nový model s týmto stĺpcom musí používať trait `BelongsToConference`.

## 3. Čo treba mať pred začiatkom

| Požiadavka | Overenie |
|---|---|
| PHP 8.5 s rozšíreniami `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo` | `php -m` |
| Composer | `composer -V` |
| MariaDB 10.10 alebo novšia (Debian 13 má 11.8) | `mariadb --version` |
| Prístup administrátora k MariaDB na vytvorenie databázy | prihlásenie ako `root` |

Staršia MariaDB než 10.10 nie je podporená: bez nastavenia `explicit_defaults_for_timestamp` by sa pri úprave riadka potichu prepisovali dátumy (napr. dátum rozhodnutia výboru). Kontrolný príkaz to overuje.

## 4. Postup pripojenia

Všetky príkazy sa spúšťajú v priečinku `backend/`.

**Krok 1 — vytvor prázdne databázy a používateľa.** V súbore `database/mariadb/create-database.sql` nahraď `CHANGE_ME` vlastným heslom (zmenu necommituj) a spusti ho ako administrátor:

```sh
mariadb -u root -p < database/mariadb/create-database.sql
```

Vzniknú `ucm_conferences` (vývoj) a `ucm_conferences_test` (testy), obe v `utf8mb4`, a používateľ `ucm`.

**Krok 2 — nastav `.env`.** Ak `.env` neexistuje, skopíruj `.env.example`. Nastav:

```ini
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1        # v Dockeri názov služby, napr. mariadb
DB_PORT=3306
DB_DATABASE=ucm_conferences
DB_USERNAME=ucm
DB_PASSWORD=             # heslo z kroku 1
```

**Krok 3 — nainštaluj a zmigruj.**

```sh
composer install
php artisan key:generate        # iba ak APP_KEY v .env chýba
php artisan migrate
```

Očakávaný výsledok: 21 migrácií so stavom DONE, bez chyby.

**Krok 4 — spusti kontrolu.**

```sh
php artisan conferences:db-check
```

Každý riadok musí byť `OK` a posledný riadok `Database check passed.` (návratový kód 0). Význam kontrol je v sekcii 6.

**Krok 5 — naplň vývojové dáta a vyskúšaj aplikáciu** (iba vývojová databáza, nikdy ostrá):

```sh
php artisan migrate:fresh --seed
php artisan serve
```

Otvor `http://localhost:8000/login` a prihlás sa ako `organizer@example.test` (heslo je hodnota `SEED_USER_PASSWORD`, predvolene `password`). Mali by sa zobraziť registrácie. Stránka konferencie je na `http://ans2027.localhost:8000`.

**Krok 6 — fronta.** Ak je v `.env` `QUEUE_CONNECTION=database`, e-maily a faktúry sa spracujú až po spustení `php artisan queue:work`. Zaeviduj plnú úhradu v admine a over, že po spracovaní fronty vznikla faktúra a pribudol záznam v tabuľke `email_log`.

## 5. Automatické testy na MariaDB

Testy štandardne bežia na SQLite v pamäti a súbor `.env` ich neovplyvní. Na MariaDB ich prepneš premennými prostredia; databáza musí byť testovacia:

```sh
# Linux / macOS / Git Bash
DB_CONNECTION=mariadb DB_HOST=127.0.0.1 DB_DATABASE=ucm_conferences_test DB_USERNAME=ucm DB_PASSWORD=... vendor/bin/pest
```

```powershell
# Windows PowerShell
$env:DB_CONNECTION='mariadb'; $env:DB_HOST='127.0.0.1'; $env:DB_DATABASE='ucm_conferences_test'
$env:DB_USERNAME='ucm'; $env:DB_PASSWORD='...'; vendor\bin\pest
```

Očakávaný výsledok: 260 testov prejde. Ak niektorý zlyhá iba na MariaDB, zapíš jeho názov a chybovú hlášku; postupuj podľa pravidla 3.

## 6. Čo kontroluje `conferences:db-check`

| Kontrola | Čo znamená zlyhanie |
|---|---|
| `connection` | Zlé prihlasovacie údaje, host, port alebo databáza neexistuje |
| `driver` | V `.env` nie je `DB_CONNECTION=mariadb` |
| `server` | Server nie je MariaDB |
| `charset` | Databáza nebola vytvorená v `utf8mb4` |
| `strict mode` | Niekto vypol `strict` v `config/database.php` |
| `timestamps` | MariaDB staršia než 10.10 alebo vypnuté `explicit_defaults_for_timestamp` |
| `migrations` | Neboli spustené všetky migrácie |
| `tables` | Chýba niektorá z 22 očakávaných tabuliek |
| `engine` | Tabuľka nie je InnoDB (bez transakcií, zámkov a cudzích kľúčov) |
| `unique indexes` | Chýba jedinečnosť VS, registrácie na konferenciu, faktúry na registráciu, verzie alebo e-mailu |
| `foreign keys` | Tabuľky nevznikli migráciami |
| `write + rollback` | Používateľ nemá právo zápisu alebo tabuľka nie je transakčná |
| `row locks` | `SELECT … FOR UPDATE` neblokuje druhú transakciu; variabilné symboly a čísla faktúr by sa pri súbehu mohli zopakovať |

Kontrola zámkov otvorí druhé spojenie, zamkne v ňom jeden pomocný riadok a overí, že prvé spojenie naň musí čakať. Pomocný riadok po sebe vždy zmaže. Táto kontrola ešte na skutočnej MariaDB nebežala; ak zlyhá, môže byť chyba aj v nej samotnej, nie v databáze — nahlás presnú hlášku.

Prepínač `--allow-sqlite` slúži iba na lokálny vývoj bez MariaDB a `--skip-locks` preskočí kontrolu zámkov.

## 7. Tabuľky a ich vlastníci

Modul je priečinok `backend/app/Modules/<Modul>`; migrácie sú v jeho `Database/Migrations`. Tabuľky `cache` a `jobs` sú v `backend/database/migrations`.

| Modul | Tabuľky |
|---|---|
| Auth | `users`, `password_reset_tokens`, `sessions`, `user_roles` |
| Conferences | `conferences`, `participation_categories`, `form_fields`, `conference_user` |
| Registration | `participant_profiles`, `registrations`, `variable_symbol_sequences` |
| Submissions | `submissions`, `submission_versions`, `decisions`, `reviews` (iba pripravená) |
| Workflow | `status_history` |
| Payments | `payments` |
| Invoicing | `invoices`, `invoice_number_sequences` |
| Notifications | `email_log` |

Sumy sú celé čísla v centoch (`price_cents`, `amount_cents`) so stĺpcom `currency`; žiadne desatinné typy. Súbory (PDF príspevkov a faktúr) nie sú v databáze, iba ich cesta; ležia v `backend/storage/app/private` a patria do zálohy spolu s databázou.

## 8. Vzťah ku konceptuálnemu návrhu (databaza.pdf)

Návrh v [databaza.md](databaza.md) má slovenské názvy entít a nepokrýva platby. Fyzická schéma z neho vychádza, ale nie je jeho prepisom. Tieto rozdiely sú zámerné; neopravuj ich bez dohody tímu.

| Entita v návrhu | Tabuľka v backende | Rozdiel |
|---|---|---|
| `KONFERENCIA`, `ROCNIK` | `conferences` | Jeden riadok je jeden ročník (stĺpce `year`, `subdomain`); samostatná entita konferencie nie je |
| `OSOBA` | `participant_profiles` | Osoba existuje iba s účtom; spoluautori bez účtu nie sú v prototype |
| `POUZIVATELSKY_UCET` | `users` | E-mail je jedinečný prihlasovací údaj |
| `ROLA`, `ROLA_POUZIVATELA` | `user_roles`, `conference_user` | Globálne roly a roly viazané na konferenciu (rieši zistenie DB04) |
| `KATEGORIA_REGISTRACIE` | `participation_categories` | Navyše cena a požadovaný typ príspevku |
| `REGISTRACIA` | `registrations` | Navyše cena, VS, fakturačné údaje, stav platby; jedna registrácia na konferenciu a účet |
| `PRISPEVOK` | `submissions` | Jeden príspevok na registráciu; text abstraktu a kľúčové slová sa neukladajú, iba PDF |
| `VERZIA_PRISPEVKU` | `submission_versions` | Navyše veľkosť, odtlačok SHA-256 a kto súbor nahral |
| `RECENZIA_PRISPEVKU` | `reviews` | Viazaná na verziu; v prototype sa nepoužíva |
| `ROZHODNUTIE` | `decisions` | Viazané na verziu, eviduje oznámenie autorovi |
| `TERMIN` | stĺpce `conferences.*_deadline` | Tri pevné termíny namiesto samostatnej tabuľky |
| `AUTOR_PRISPEVKU`, `BOD_PROGRAMU`, `PREDNASAJUCI_PROGRAMU` | — | Mimo rozsahu prototypu |
| — | `payments`, `invoices`, `status_history`, `email_log`, číselné rady | V návrhu chýbali, zákazník ich požaduje |

## 9. Riešenie problémov

| Hláška alebo príznak | Príčina a riešenie |
|---|---|
| `could not find driver` | V PHP chýba rozšírenie `pdo_mysql` |
| `Access denied for user` | Zlé meno alebo heslo v `.env`, alebo používateľ vznikol pre iný host (`'ucm'@'localhost'` oproti `'ucm'@'%'`) |
| `Unknown database` | Nebol spustený krok 1 alebo je preklep v `DB_DATABASE` |
| `Connection refused` | MariaDB nebeží, zlý port, alebo v Dockeri treba ako `DB_HOST` názov služby, nie `127.0.0.1` |
| `Unknown collation` | Server nepozná koláciu z `DB_COLLATION`; ponechaj predvolenú `utf8mb4_unicode_ci` |
| `Specified key was too long` | Databáza nie je `utf8mb4` s InnoDB, alebo ide o veľmi starú verziu servera |
| Migrácia zlyhá v polovici | Oprav príčinu a spusti `php artisan migrate:fresh` (iba vývojová databáza, zmaže dáta) |
| Zmena v `.env` sa neprejaví | `php artisan config:clear` |
| Faktúra ani e-mail nevzniknú | Nebeží `php artisan queue:work`, alebo pozri tabuľku `failed_jobs` a `storage/logs/laravel.log` |

## 10. Založenie skutočných údajov

Seed je iba na vývoj. Na prázdnej databáze sa prvý superadministrátor a konferencia zakladajú príkazmi:

```sh
php artisan conferences:create-user admin@example.test "Meno Priezvisko" --superadmin
php artisan conferences:create cesta/ku/konferencii.json
```

Prvý príkaz sa na heslo opýta a nikde ho nevypíše. Vzor definície konferencie je `database/conference.example.json`; skutočné poplatky, IBAN a údaje vystavovateľa musí dodať fakulta (O38, O29) a súbor so skutočnými údajmi nepatrí do Gitu. Ďalších pracovníkov potom superadministrátor pridá v obrazovke Team.

## 11. Čo odovzdať po dokončení

1. Výstup `php artisan conferences:db-check` (všetko `OK`) a výsledok testov na MariaDB vrátane verzie servera.
2. Zoznam všetkého, čo bolo treba zmeniť, s dôvodom. Každá zmena kódu má test a je v samostatnom commite s anglickou správou.
3. Aktualizovaný [backend.md](backend.md): riadok BO14 v sekcii 14.1 a odsek „Neoverené“ v sekcii 15.2 podľa skutočného výsledku. Neoznačuj za overené nič, čo si nespustil.
4. Ak vzniklo Docker prostredie, jeho súbory a postup spustenia v `backend/README.md`.
5. Otvorené problémy, ktoré si nevyriešil, zapísané ako nové riadky BO v sekcii 14.1.
