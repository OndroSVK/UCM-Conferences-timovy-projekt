# backend.md — ANS backend (Laravel, priečinok `/backend`)

> **Stav k 11. 11. 2026.** Tento súbor je pre Claude Code **jediný zdroj informácií o backende**: obsahuje výťah z hlavnej projektovej dokumentácie (rozhodnutia R…, otázky O…, moduly M…) a rozhodnutia a otvorené otázky z diskusie o backende (BR…, BO…), ktoré inde zapísané nie sú.
> Fáza: **prototyp**, odovzdanie **5. 12. 2026** (R15). Funkcie plnej verzie sa nerobia, kým o ne tím nepožiada (R19).
> Ak narazíš na rozpor s iným súborom v repozitári alebo s požiadavkou tímu, nerieš ho potichu: nahlás ho a opýtaj sa.

---

## 0. Kontext projektu

- Štvorčlenný študentský tím stavia systém na správu vedeckých konferencií podľa vzoru [ans2025.ucm.sk](https://ans2025.ucm.sk/). Repozitár `OndroSVK/UCM-Conferences-timovy-projekt`, jedna vetva `main` (R36).
- Kód píše Claude. Členovia tímu A, B, C, D vlastnia moduly: zadávajú user stories, kontrolujú, testujú a obhajujú ich.
- Jeden backend, jeden admin panel a jedna databáza obslúžia všetky konferencie. Každá konferencia má vlastnú statickú main page na subdoméne (napr. `ans2027.ucm.sk`), ktorú nahráva IT univerzity (R22, R23); backend ju neobsluhuje.
- Účastník má účet (e-mail + heslo) a user page (R8). Jedno prihlasovacie okno pre účastníkov aj adminov, presmerovanie podľa roly (R11); kto má obe roly, vyberie si (R17).
- Všetko v angličtine: kód, databáza, commity, web, e-maily, admin (R10; jazyk e-mailov a faktúr ešte dolaďuje O33). Dokumentácia tímu je v slovenčine.

**Prototyp musí ukázať:** dve konferencie súčasne; registráciu s vytvorením účtu; prihlásenie s presmerovaním podľa roly; user page s personalizovanými platobnými inštrukciami a nahratím príspevku; ručnú evidenciu úhrad vrátane čiastočnej s e-mailom a testovacou faktúrou; jednokolové rozhodnutie výboru o príspevku; potvrdzovacie e-maily; prepínanie konferencií; export XLSX/CSV podľa roly.

---

## 1. Rozhodnutia pre backend (z diskusie 11. 11. 2026)

Tieto rozhodnutia potvrdil tím a zatiaľ nie sú v hlavnom dokumente (prenos je úloha BO10).

| # | Rozhodnutie | Dôsledok |
|---|---|---|
| BR1 | Backend je **modulárny** (požiadavka objednávateľa na modularitu systému) | Modul = priečinok `app/Modules/<Modul>`; zapisuje iba do vlastných tabuliek; s inými modulmi komunikuje cez ich Actions alebo udalosti (sekcia 3) |
| BR2 | **Každá feature má samostatný PHP súbor**: jedna Action trieda (validácia, oprávnenie, logika, udalosti) + samostatný test | Modely, enumy, migrácie a udalosti sú zdieľané v rámci modulu; zmena funkcie = zmena jedného Action súboru a jeho testu |
| BR3 | Celý PHP stack je v priečinku `/backend` (zhodné s R36) | Statický frontend v koreni a GitHub Pages sa nemenia |
| BR4 | Admin okrem superadmina vidí **iba konferencie, ktoré mu priradil superadmin**; platí **už v prototype** (R21) | Pivot `conference_user`, prepínač aj každý dopyt filtrovaný podľa priradenia; superadmin vidí všetky |
| BR5 | Variabilný symbol zatiaľ v tvare `RRNNNNNN` (sekcia 10) | **Dočasné** — tvar treba overiť (BO1) |
| BR6 | Frontend (routy, Blade, Filament, API) a pripojenie k databáze sa doplnia **po pushi tohto súboru**; Claude Code potom prečíta zvyšok repozitára | V tejto fáze iba doménová logika, migrácie modelov a testy; UI volá hotové Actions a nemá vlastnú obchodnú logiku |
| BR7 | Platí aktualizovaný technický rámec: PHP 8.5+, MariaDB, šesť stavov platby, odborný proces podľa R31 (aktualizované aj v inštrukciách projektu) | Staršie stavy `unpaid → paid → invoiced` a `not submitted → approved` sa nepoužívajú |
| BR8 | **Roly pracovníkov aj poverenie na platby sú viazané na konkrétnu konferenciu** (rozhodnutie 11. 11. 2026) | Rola a doplnkové oprávnenia (`payments.record`, `payments.waive`, `invoices.retry`) sú riadok v `conference_user`; globálne sú iba `participant` a `superadmin` (`user_roles`) |
| BR9 | Kód sa píše teraz; Docker, PHP a MariaDB prostredie dodá tím neskôr (11. 11. 2026) | Overenie prebehlo na PHP 8.5 so SQLite (sekcia 15); beh na MariaDB a v Dockeri je otvorený (BO14) |
| BR10 | Úloha zahŕňa aj prepojenie s UI (fáza 5) (11. 11. 2026) | Blade obrazovky v `/backend` volajú iba Actions (sekcia 15.3) |
| BR11 | Obmedzenie prihlásenia zatiaľ **3 neúspešné pokusy** (11. 11. 2026) | Pracovná hodnota, potvrdenie s tímom je otvorené (BO2) |

---

## 2. Technológie

| Časť | Voľba | Istota |
|---|---|---|
| Jazyk | PHP 8.5+ (R35) | Overené 11. 11. 2026: inštalácia balíkov aj testy prešli na PHP 8.5.12 (Windows, SQLite) |
| Framework | Laravel 13 (kostra `laravel/laravel` v13.11, `laravel/framework` ^13.42) | Overené pri inštalácii |
| Databáza | MariaDB, jedna pre všetky konferencie (R35) | Rozhodnuté |
| Cieľové prostredie | LXC, GNU/Debian 13 (R35); vývoj v Dockeri na Debiane 13 s MariaDB a Mailpitom | Rozhodnuté |
| Oprávnenia | Vlastná matica `Shared/Auth` (enum `Role`, `Permission`, služba `AccessControl`) nad tabuľkami `user_roles` a `conference_user` | Implementované 11. 11. 2026 namiesto `spatie/laravel-permission`, lebo roly sú viazané na konferenciu (BR8); potvrdiť s tímom (BO15) |
| UI | Blade šablóny, vzhľad prevzatý zo statického prototypu (`styles.css`) | Implementované 11. 11. 2026 namiesto Filamentu; potvrdiť s tímom (BO15) |
| PDF | `barryvdh/laravel-dompdf` (bez Chromu, jednoduchšie v Dockeri) | Implementované |
| Export | `maatwebsite/excel` (XLSX, CSV) | Implementované |
| E-maily | Laravel Mail cez frontu (queue), lokálne Mailpit | Rozhodnuté |
| Plánovač | Laravel Scheduler | Až plná verzia (M10) |
| Testy | Pest (nad PHPUnit) | Návrh; tím môže zvoliť čisté PHPUnit |

Texty pre používateľa idú od začiatku cez prekladové súbory `lang/en/*.php`, aby sa dal doplniť ďalší jazyk (O33).

---

## 3. Modulárna architektúra (BR1, BR2)

### 3.1 Feature = jeden súbor

- **Feature = jedna Action trieda** v `app/Modules/<Modul>/Actions/<Feature>.php`. Obsahuje validačné pravidlá vstupu, kontrolu oprávnenia, obchodnú logiku, transakciu a vyvolanie udalostí.
- **Zdieľané stavebné kamene modulu** (modely, enumy stavov, migrácie, udalosti, listenery) sú samostatné súbory v module, lebo ich používa viac features.
- **Test feature** je samostatný súbor s rovnakým názvom: `tests/Modules/<Modul>/<Feature>Test.php`.

### 3.2 Adresárová štruktúra

```
backend/
├── app/
│   ├── Modules/
│   │   ├── Conferences/            # M0
│   │   │   ├── Actions/            # 1 súbor = 1 feature
│   │   │   ├── Models/
│   │   │   ├── Enums/
│   │   │   ├── Events/
│   │   │   ├── Listeners/
│   │   │   ├── Policies/
│   │   │   ├── Database/Migrations/
│   │   │   └── ConferencesServiceProvider.php
│   │   ├── Auth/                   # M2
│   │   ├── Registration/           # M3
│   │   ├── ParticipantPortal/      # M4 (backendová časť user page)
│   │   ├── Submissions/            # M5
│   │   ├── Workflow/               # M6 stavy
│   │   ├── Payments/               # M7
│   │   ├── Invoicing/              # M8
│   │   ├── Notifications/          # M9
│   │   ├── Deadlines/              # M10 — v prototype prázdny, plná verzia
│   │   └── Reporting/              # M11 — exporty (UI adminu tu nie je)
│   └── Shared/                     # spoločné jadro, žiadna doménová logika modulov
│       ├── Conference/             # ConferenceContext, BelongsToConference, ConferenceScope
│       ├── Money/                  # Money value object
│       ├── Actions/                # základná trieda Action
│       └── Auth/                   # Role enum, Permission enum
├── config/modules.php              # zoznam zapnutých modulov
├── lang/en/
└── tests/
    ├── Modules/<Modul>/            # testy features
    └── CrossCutting/               # izolácia konferencií, matica oprávnení, E2E cesta
```

M1 (main page) a M12 (infraštruktúra, Docker) nie sú moduly backendu v tomto zmysle.

### 3.3 Pravidlá medzi modulmi

1. Modul **zapisuje iba do vlastných tabuliek**. Do cudzích dát zasahuje iba volaním Action iného modulu alebo cez udalosť.
2. Vedľajšie efekty idú cez **udalosti** (napr. `PaymentSettled` → Invoicing vystaví faktúru, Notifications pošle e-mail). Platobný modul nevie nič o faktúrach ani e-mailoch.
3. Čítanie cudzích modelov je povolené (Eloquent vzťahy), zápis nie.
4. Každý modul má vlastný `ServiceProvider` (migrácie, policies, listenery). Moduly sa zapínajú v `config/modules.php`; vypnutý modul nesmie rozbiť ostatné (povinne testované pre `Invoicing`, kvôli O32).
5. `Shared/` neobsahuje logiku žiadneho modulu, iba spoločné mechanizmy.
6. Závislosti smerujú k jadru: `Conferences` a `Auth` závisia iba od `Shared`; ostatné moduly môžu závisieť od nich.

### 3.4 Kostra Action súboru

```php
<?php

namespace App\Modules\Payments\Actions;

final class RecordPayment extends Action
{
    public static function rules(): array { /* validácia vstupu */ }

    public function handle(User $actor, Registration $registration, array $input): Payment
    {
        $this->authorize($actor, 'payments.record', $registration); // rola + konferencia
        return DB::transaction(function () { /* logika, ChangeStatus, event */ });
    }
}
```

Každá Action: overí oprávnenie aj príslušnosť ku konferencii, beží v transakcii, mení stav **iba** cez `Workflow\Actions\ChangeStatus`, udalosti vyvolá až po commite (`afterCommit`).

---

## 4. Prierezové pravidlá (platia pre každý modul)

### 4.1 Oddelenie konferencií (R6, R21, BR4)
- Každá doménová tabuľka má `conference_id` (priamo alebo cez `registration_id`).
- Modely s `conference_id` používajú trait `BelongsToConference` s globálnym scope podľa `ConferenceContext` (aktívna konferencia: zo subdomény pre verejnú časť, z prepínača pre admin).
- Dopyt bez nastaveného kontextu **zlyhá výnimkou**, nevráti dáta všetkých konferencií. Výnimkou je iba superadmin pri explicitnom `ConferenceContext::all()`.
- Admin smie aktivovať iba konferenciu, ktorú mu priradil superadmin (BR4); superadmin všetky.
- Každý modul má test, že dáta inej konferencie nie sú viditeľné ani zmeniteľné.

### 4.2 Oprávnenia (R12, R30)
- Roly (`Shared/Auth/Role`): `participant`, `organizer`, `programme_committee`, `superadmin`; pripravené, no v prototype nepoužívané: `economist`, `reviewer`.
- Oprávnenia sú jemné (`payments.record`, `submissions.decide`, `exports.registrations` …), roly sú ich zväzky. „Poverený organizátor“ (platby) = organizátor s pridaným oprávnením `payments.record`.
- Matica je v sekcii 9. Test oprávnení je povinný pre každú Action.
- Rola pracovníka a jeho poverenia platia iba v konferencii, ku ktorej ich superadmin priradil (BR8).

### 4.3 Stavy (R5, M6)
- Stav sa mení **iba** cez `ChangeStatus`; stĺpec stavu sa nikde inde nezapisuje (vynútiť architektúrnym testom).
- `ChangeStatus` overí povolený prechod (mapa v enume), oprávnenie aktéra, zapíše `status_history` (kategória, z, na, kto, kedy, poznámka) a vyvolá `StatusChanged` + špecifickú udalosť prechodu.

### 4.4 Peniaze
- Sumy ako celé číslo v centoch (`amount_cents`, `price_cents`) + `currency` (ISO 4217). Žiadne `float`.
- Cena sa pri registrácii **uloží ako snapshot**; neskoršia zmena cenníka ju nemení.
- Sumy nikdy natvrdo v kóde (R32); testovacie dáta 180 / 120 / 90 EUR sú len vzorové (O38).

### 4.5 Súbory
- Príspevky a faktúry na súkromnom disku (`storage/app/private`), nikdy cez verejnú adresu. Sťahovanie iba cez Action s kontrolou oprávnenia.
- Príspevok iba PDF (R14): kontrola MIME typu aj obsahu (hlavička `%PDF-`), limit veľkosti 10 MB (BO5).
- Vystavená faktúra sa nemení; oprava = dobropis (plná verzia).

### 4.6 E-maily
- Odosielanie cez frontu; zlyhanie e-mailu **nikdy nevracia** obchodnú operáciu (napr. úhradu). Každý pokus sa zapíše do `email_log`.

### 4.7 Osobné údaje
- Testovacie a seed dáta iba vymyslené (Faker, domény `example.com` / `.test`), žiadne reálne osoby.
- Neevidujeme vek ani vekovú kategóriu (R32).
- Programový výbor nesmie vidieť finančné ani fakturačné údaje; ekonóm (plná verzia) nesmie vidieť stravovacie údaje.
- Zrušenie účtu (plná verzia) = anonymizácia; faktúry ostávajú archivované (lehotu overiť, O17). GDPR a archiváciu treba overiť u odborníka; tento súbor nie je právna rada.

---

## 5. Dátový model (konceptuálny)

Finálne migrácie vzniknú pri prepojení na databázu (BR6). Každý modul vlastní svoje tabuľky.

| Tabuľka | Kľúčové polia | Väzby | Modul |
|---|---|---|---|
| `conferences` | názov, subdoména, miesto, dátumy, termíny (registrácia, platba, príspevok), mena, IBAN, fakturačné údaje vystavovateľa (FPV UCM), odkaz na ubytovanie (R34) | má všetko ostatné | Conferences |
| `participation_categories` | názov, cena, vyžaduje abstrakt / poster / nič | N:1 conferences | Conferences |
| `form_fields` | zoznamy formulára (napr. odvetvia) | N:1 conferences | Conferences |
| `conference_user` | priradenie adminov ku konferenciám (BR4) | N:M | Conferences |
| `users` | e-mail (unikátny), heslo, roly | 1:1 participant_profiles | Auth |
| `participant_profiles` | meno, priezvisko, tituly pred a za menom, organizácia | 1:N registrations | Registration |
| `registrations` | kategória účasti, odvetvie, fakturačné údaje, cena (snapshot v centoch), fee waiver, VS, stav registrácie a stav platby, súhlas so spracovaním | N:1 conferences, N:1 users | Registration |
| `submissions` | typ (talk / poster), názov, stav príspevku (jediné miesto tohto stavu; záznam vzniká už pri registrácii, pasívny účastník `not_required`) | N:1 registrations | Submissions |
| `submission_versions` | súbor PDF, číslo verzie, dátum odovzdania | N:1 submissions | Submissions |
| `reviews` | recenzent, hodnotená verzia, stav, odporúčanie, pripomienky autorovi, termín — **iba pripraviť, plná verzia** | N:1 submission_versions, N:1 users | Submissions |
| `decisions` | typ rozhodnutia, odôvodnenie, kto, kedy, oznámené autorovi | N:1 submissions | Submissions |
| `payments` | suma, dátum, VS, kto zaevidoval; viac úhrad na registráciu (R33) | N:1 registrations | Payments |
| `invoices` | typ (invoice / credit_note), číslo, suma, PDF, stav vystavenia | N:1 registrations | Invoicing |
| `status_history` | kategória, starý a nový stav, kto, kedy, poznámka | N:1 registrations | Workflow |
| `email_log` | príjemca, typ, výsledok | N:1 registrations | Notifications |

---

## 6. Moduly a feature súbory (prototyp)

Vlastník = člen tímu, ktorý feature zadáva, kontroluje a obhajuje.

### M0 `Conferences` — vlastník A

| Feature (súbor) | Čo robí |
|---|---|
| `ResolveConferenceFromHost.php` | Z hostiteľa (`ans2027.ucm.sk`, lokálne `ans2027.localhost`) určí konferenciu a nastaví `ConferenceContext`; neznáma subdoména → 404 |
| `SwitchActiveConference.php` | Prepínač v admine; iba priradené konferencie (BR4), superadmin všetky |
| `ListAccessibleConferences.php` | Zoznam konferencií pre prepínač podľa aktéra |
| `AssignAdminToConference.php` | Superadmin priradí / odoberie admina konferencii (BR4) |
| `GetRegistrationFormOptions.php` | Kategórie účasti a zoznamy formulára pre konferenciu (read-only) |

Plná verzia: `CreateConference` superadminom s nastavením formulára, predvyplnenie z minulého ročníka. V prototype vznikajú dve konferencie seederom.

### M2 `Auth` — vlastník B

| Feature | Čo robí |
|---|---|
| `AuthenticateUser.php` | Overí e-mail + heslo, vráti cieľ: user page / admin / výber (R11, R17) |
| `ResolvePostLoginDestination.php` | Pravidlo cieľa podľa rolí; používateľ s oboma rolami dostane výber |
| `CheckEmailRegistered.php` | Či e-mail už má účet (US-04); odpoveď iba áno/nie |
| `SendPasswordResetLink.php` | Zabudnuté heslo (Laravel password broker) |
| `ResetPassword.php` | Nové heslo z tokenu |
| `VerifyEmail.php` | Overenie e-mailu |

Obmedzenie pokusov o prihlásenie: pracovne **3 neúspešné pokusy na e-mail a IP, potom blokovanie na 300 s** (BR11, `config/conferences.php`); potvrdenie je otvorené (BO2). 2FA až plná verzia (O8).

### M3 `Registration` — vlastník B

| Feature | Čo robí |
|---|---|
| `RegisterNewParticipant.php` | V jednej transakcii: účet + profil + registrácia; počiatočné stavy podľa kategórie (7.4); snapshot ceny; VS; súhlas so spracovaním; event `RegistrationCreated` |
| `RegisterExistingUserForConference.php` | Prihlásený účastník sa prihlási na ďalšiu konferenciu bez nového účtu (US-04); zabráni duplicitnej registrácii na tú istú konferenciu |
| `CalculateRegistrationPrice.php` | Cena z kategórie danej konferencie; fee waiver → 0 |
| `GenerateVariableSymbol.php` | Jedinečný VS (sekcia 10) |

Polia: meno, priezvisko, tituly pred a za menom, organizácia, e-mail, heslo, kategória účasti (`speaker`, `poster_author`, `passive_participant`), odvetvie, fakturačné údaje (ako účastník / univerzita / pracovisko), súhlas so spracovaním. Súhlas so zverejnením (O16) až plná verzia.

### M4 `ParticipantPortal` — vlastník B
Iba čítanie a úprava vlastných dát; žiadne vlastné tabuľky.

| Feature | Čo robí |
|---|---|
| `ListMyConferences.php` | Konferencie účastníka, najnovšia hore (US-08); vždy len vlastné registrácie |
| `GetMyRegistrationDetail.php` | Tri stavy, kategória, cena, uhradené, zostatok, faktúry, príspevok |
| `GetPaymentInstructions.php` | Personalizované inštrukcie: IBAN a príjemca z konferencie, suma (zostatok), VS, splatnosť; pri fee waiver alebo nule bez výzvy na platbu (R1) |
| `UpdateMyProfile.php` | Úprava profilu; zmena e-mailu iba s novým overením |

Plná verzia: `CancelMyParticipation` (O7), `DeleteMyAccount` s anonymizáciou (O12, O17).

### M5 `Submissions` — vlastník B (rozhodnutie výboru v spolupráci s C)

| Feature | Čo robí |
|---|---|
| `SubmitSubmission.php` | Nahratie PDF (abstrakt pre `speaker`, poster pre `poster_author`), verzia 1, `draft → submitted`, event |
| `ReplaceSubmissionFile.php` | Nová verzia do termínu konferencie; stará ostáva; stav späť na `submitted` (návrh O31) |
| `DownloadSubmissionFile.php` | Stiahnutie: autor, organizátor, výbor, superadmin |
| `RecordCommitteeDecision.php` | Jednokolové rozhodnutie výboru (O37): `accepted` / `conditionally_accepted` / `rejected`, odôvodnenie, záznam v `decisions`, event |

Termín: iba jednoduchá kontrola dátumu `submission_deadline` (bez pripomienok). Plná verzia: administratívna kontrola, recenzent, posudky, revízie, viac kôl.

### M6 `Workflow` — vlastník A

| Feature | Čo robí |
|---|---|
| `ChangeStatus.php` | Jediný spôsob zmeny stavu (4.3) |
| `ConfirmRegistrationWhenComplete.php` | `received → confirmed`, keď platba `paid` alebo fee waiver **a** príspevok `accepted` alebo `not_required`; či stačí aj `conditionally_accepted`, rieši BO3 |
| `GetStatusHistory.php` | História filtrovaná podľa oprávnenia (platobné stavy iba pre platobné roly atď.) |

Enumy s mapou prechodov: `RegistrationStatus`, `PaymentStatus`, `SubmissionStatus` (sekcia 7).

### M7 `Payments` — vlastník A

| Feature | Čo robí |
|---|---|
| `RecordPayment.php` | Ručné zaevidovanie úhrady podľa VS (suma, dátum, kto); prepočíta stav na `partially_paid` alebo `paid`; pri `paid` event `PaymentSettled` |
| `FindRegistrationByVariableSymbol.php` | Vyhľadanie registrácie podľa VS v aktívnej konferencii |
| `GrantFeeWaiver.php` | Individuálne oslobodenie (vlastnosť, nie stav; R32); nastavuje organizátor (návrh O38) |
| `GetPaymentBalance.php` | Cena, súčet úhrad, zostatok |

Preplatok sa zaeviduje a adminovi sa zobrazí upozornenie (BO6). Úhrada po `paid` nevytvorí druhú faktúru. Plná verzia: `overdue`, `cancelled`, `refunded`, import výpisu.

### M8 `Invoicing` — vlastník A — **O32 nie je doladené**

| Feature | Čo robí |
|---|---|
| `IssueInvoice.php` | Listener na `PaymentSettled`: faktúra **práve raz** na registráciu (unikátny index + kontrola v transakcii), vystavovateľ FPV UCM z údajov konferencie, číslo z číselného radu, event `InvoiceIssued` |
| `RenderInvoicePdf.php` | HTML šablóna → PDF (dompdf), cez celú stranu červený diagonálny kríž a nápis **TEST INVOICE** (R20, R26) |
| `RetryInvoiceIssue.php` | Pri zlyhaní ostane záznam `failed`, admin spustí znova (R24); úhrada ostáva platná |
| `DownloadInvoice.php` | Stiahnutie: vlastník registrácie, organizátor, superadmin |

Zásady: faktúra nie je platobný stav (R33); žiadne proformy; ručné nahrávanie faktúr zrušené (R24); dobropis až plná verzia. Kým O32 nie je uzavreté, na faktúry **nestavať ďalšie funkcie** a modul musí ísť vypnúť bez dopadu na platby. Číslovanie, náležitosti a DPH (O29) **nie sú overené** — pred ostrou prevádzkou ich musí potvrdiť ekonomické oddelenie fakulty.

### M9 `Notifications` — vlastník C

| Feature (listener) | Spúšťač | Obsah |
|---|---|---|
| `SendRegistrationConfirmation.php` | `RegistrationCreated` | potvrdenie; pri platenej kategórii aj platobné údaje |
| `SendPaymentReceived.php` | `InvoiceIssued` (pri vypnutom Invoicing `PaymentSettled`) | potvrdenie úhrady, faktúra v prílohe |
| `SendSubmissionReceived.php` | prechod na `submitted` | potvrdenie predloženia / novej verzie |
| `SendDecision.php` | rozhodnutie výboru | výsledok a odôvodnenie určené autorovi |
| `SendParticipationConfirmed.php` | `received → confirmed` | potvrdenie účasti |
| `LogEmail.php` | každé odoslanie | príjemca, typ, výsledok |

Plná verzia: hromadné e-maily s filtrami, pripomienky.

### M10 `Deadlines` — vlastník C
V prototype sa **nerobí** (plná verzia: pripomienky 7 a 1 deň vopred, blokovanie po termíne — O24). Iba prázdny priečinok s README.

### M11 `Reporting` — vlastník C

| Feature | Čo robí |
|---|---|
| `ExportRegistrations.php` | XLSX / CSV aktuálnej konferencie podľa filtra stavov; **stĺpce podľa roly** (US-15: výbor bez fakturačných údajov) |

Admin panel (Filament) sa pripojí po pushi (BR6) a bude volať tieto Actions.

---

## 7. Stavový model (kódy v angličtine)

Hodnoty stavov platby a odborný proces určil objednávateľ (R31, R33); kódové názvy sú náš pracovný návrh.

### 7.1 Registrácia — `RegistrationStatus`
| Z | Na | Kto | Fáza |
|---|---|---|---|
| — | `received` | systém pri registrácii | prototyp |
| `received` | `confirmed` | systém (`ConfirmRegistrationWhenComplete`) alebo organizátor | prototyp |
| `received`, `confirmed` | `cancelled` | organizátor, superadmin | prototyp (admin); storno účastníkom plná verzia |

### 7.2 Platba — `PaymentStatus` (+ vlastnosť `fee_waived`)
| Z | Na | Kto | Fáza |
|---|---|---|---|
| — | `pending` | systém pri registrácii (cena > 0, bez waiver) | prototyp |
| `pending` | `partially_paid` | `RecordPayment` | prototyp |
| `pending`, `partially_paid` | `paid` | `RecordPayment` | prototyp |
| `pending`, `partially_paid` | `overdue` | systém po splatnosti | plná verzia |
| `pending`, `partially_paid`, `overdue` | `cancelled` | admin / storno | plná verzia |
| `paid` | `refunded` | admin eviduje vrátenie mimo systému | plná verzia |

Pri fee waiver alebo nulovej cene nevzniká výzva na platbu; pracovný návrh `payment_status = null`, `fee_waived = true` (BO4).

### 7.3 Príspevok — `SubmissionStatus`
| Z | Na | Kto | Fáza |
|---|---|---|---|
| — | `draft` / `not_required` | systém podľa kategórie | prototyp |
| `draft` | `submitted` | účastník | prototyp |
| `submitted` | `submitted` (nová verzia) | účastník do termínu | prototyp (O31) |
| `submitted` | `accepted` / `conditionally_accepted` / `rejected` | programový výbor | prototyp (jednokolovo, O37) |
| `submitted` | `administrative_check` → `under_review` | organizátor | plná verzia |
| `under_review` | `accepted` / `conditionally_accepted` / `revision_requested` / `rejected` | programový výbor | plná verzia |
| `revision_requested` | `submitted` | autor | plná verzia |

Enum obsahuje všetky hodnoty od začiatku, prototyp povoľuje iba prechody označené „prototyp“. Odporúčanie recenzenta nie je rozhodnutie a stav recenzie sa vedie oddelene (R31).

### 7.4 Počiatočné stavy podľa kategórie
| Kategória | Registrácia | Platba | Príspevok |
|---|---|---|---|
| `speaker` | `received` | `pending` | `draft` (abstrakt) |
| `poster_author` | `received` | `pending` | `draft` (poster; R29) |
| `passive_participant` | `received` | `pending` alebo bez platby (O38) | `not_required` |

---

## 8. Udalosti a vedľajšie efekty

| Udalosť | Vyvoláva | Reagujú |
|---|---|---|
| `RegistrationCreated` | Registration | Notifications |
| `StatusChanged` | Workflow | Workflow (`ConfirmRegistrationWhenComplete`), Notifications |
| `PaymentRecorded` | Payments | — (história) |
| `PaymentSettled` | Payments (prechod na `paid`) | Invoicing, Notifications (fallback) |
| `InvoiceIssued` / `InvoiceIssueFailed` | Invoicing | Notifications / upozornenie v admine |
| `SubmissionSubmitted` | Submissions | Notifications |
| `CommitteeDecisionRecorded` | Submissions | Workflow, Notifications |

Listenery s vedľajším efektom (PDF, e-mail) bežia vo fronte a po commite transakcie.

---

## 9. Matica oprávnení (prototyp)

| Oprávnenie | participant | organizer | programme_committee | superadmin |
|---|---|---|---|---|
| vlastný profil, registrácie, inštrukcie, faktúry | áno (iba svoje) | — | — | — |
| nahrať / vymeniť vlastný príspevok | áno | — | — | — |
| čítať registrácie a osobné údaje konferencie | — | áno | iba základné (bez financií) | áno |
| čítať platby a faktúry | iba svoje | áno | **nie** | áno |
| `payments.record`, `payments.waive` | — | iba pri poverení | — | áno |
| `invoices.retry` | — | iba pri poverení | — | áno |
| zmena stavu registrácie | — | áno | — | áno |
| `submissions.download` | iba svoje | áno | áno | áno |
| `submissions.decide` | — | **nie** (R30) | áno | áno |
| export registrácií | — | áno (všetky stĺpce) | áno (bez financií) | áno |
| prepínač konferencií | — | priradené (BR4) | priradené (BR4) | všetky |
| priradenie adminov ku konferencii | — | — | — | áno |

Základné údaje = meno, tituly, organizácia, e-mail, kategória. Ekonóm a recenzent v plnej verzii.

---

## 10. Variabilný symbol (BR5 — dočasné, overiť v BO1)

- Iba číslice, max. 10 znakov.
- Tvar `RR` (posledné dve číslice roka konferencie) + `NNNNNN` poradové číslo v rámci konferencie, napr. `27000042`.
- Unikátny index naprieč všetkými konferenciami; generovanie v transakcii so zámkom, aby dve súbežné registrácie nedostali rovnaký VS.
- Generovanie je v jednom súbore (`GenerateVariableSymbol.php`), aby sa tvar dal po overení zmeniť bez zásahu inde.

---

## 11. Testy (povinné)

Testy sú súčasťou každej feature (rovnaký commit).

1. **Izolácia konferencií** (`tests/CrossCutting/ConferenceIsolationTest.php`): dáta konferencie B nie sú viditeľné ani zmeniteľné v kontexte A; dopyt bez kontextu zlyhá; admin neaktivuje nepriradenú konferenciu (BR4).
2. **Oprávnenia** (`tests/CrossCutting/PermissionMatrixTest.php`): matica zo sekcie 9 — povolené aj zakázané kombinácie.
3. **Stavy**: povolené prechody prejdú, nepovolené zlyhajú, každá zmena má záznam v histórii; stav sa nedá zapísať mimo `ChangeStatus`.
4. **Ceny a platby**: snapshot ceny, čiastočná úhrada a zostatok, plná úhrada → `paid`, fee waiver bez výzvy, preplatok.
5. **Faktúry**: vznikne práve raz aj pri opakovanom uložení a súbehu; zlyhanie nevráti úhradu; PDF obsahuje TEST INVOICE; vypnutý modul nerozbije platby.
6. **VS**: jedinečnosť aj pri súbehu.
7. **Súbory**: iba PDF (aj podvrhnutý súbor s príponou .pdf), cudzí súbor sa nedá stiahnuť.
8. **E2E cesta** registrácia → úhrada → faktúra → príspevok → rozhodnutie → potvrdenie účasti, na úrovni Actions.

Testovacie dáta: dve konferencie (`ans2027`, `ans2028`), admin účty pre každú rolu s priradením ku konferenciám, vymyslení účastníci.

**Akceptačné kritériá z dokumentácie (prototyp):**
- **US-02:** účastník po prihlásení na `ans2027` skončí v user page, organizátor v admine; nesprávne heslo → chyba a ponuka obnovy hesla.
- **US-03:** nový účastník s kategóriou rečník → vznikne účet a registrácia pri ANS 2027 so stavmi `received` / `pending` / `draft`, uloží sa cena, jedinečný VS, do 2 minút e-mail s potvrdením a platobnými údajmi.
- **US-04:** existujúci e-mail nevytvorí druhý účet, ponúkne prihlásenie a pridanie konferencie k účtu.
- **US-08:** účastník prihlásený na ANS 2027 aj 2028 vidí ANS 2028 hore; po kliknutí tri stavy, kategóriu a inštrukcie s vlastným VS.
- **US-12:** plná suma → `paid`, časť → `partially_paid` so zostatkom; pri `paid` testovacia faktúra TEST INVOICE, e-mail, viditeľná v user page aj admine; druhé uloženie novú faktúru nevytvorí.
- **US-15:** export výboru obsahuje iba základné údaje a stav príspevku aktuálnej konferencie, bez fakturačných údajov.

---

## 12. Mimo rozsahu prototypu (nerobiť bez požiadavky tímu)

Roly ekonóm a recenzent; posudky, revízie, viac kôl; platobné stavy `overdue`, `cancelled`, `refunded`; vytvorenie konferencie superadminom; dobropis; storno účasti a zrušenie účtu; hromadné e-maily; termíny a pripomienky; súhlas so zverejnením; 2FA.
Nikdy: platobná brána, bankové API, proformy, hotelové rezervácie, evidencia veku (R1, R34, R32), mobilná aplikácia.

---

## 13. Postup pre Claude Code

1. Pred kódovaním si prečítaj `AGENTS.md` a tento súbor. `AGENTS.md` zatiaľ opisuje iba statický frontend — pred prvým commitom do `/backend` ho treba doplniť o odkaz na tento súbor (BO9).
2. Pracuj iba v `/backend` (BR3).
3. Jedna vetva `main`; commity v angličtine, malé: jedna feature = jeden commit (Action + test, prípadne migrácia).
4. Každá zmena vychádza z user story vlastníka modulu (Ako *[rola]* chcem *[akcia]*, aby som *[prínos]*; kritériá Daný stav / Keď / Potom). K zmene pripoj vysvetlenie „čo a prečo“, aby ho vlastník vedel obhájiť.
5. Ak požiadavka nie je rozhodnutá, použi pracovný návrh zo sekcie 14, označ ho v kóde `// PROPOSAL: <číslo otázky>` a uveď ho v popise commitu.
6. Pred commitom musia prejsť všetky testy. Nové rozhodnutia zapisuj do tohto súboru (sekcie 1 a 14).

Vlastníctvo: **A** — M0, M6, M7, M8, M12 (dáta a peniaze, infraštruktúra); **B** — M2, M3, M4, M5 (účastník); **C** — M9, M10, M11 (organizátor, admin, e-maily, exporty); **D** — M1, testy celku, dokumentácia, komunikácia s objednávateľom a IT.

Odporúčané poradie: `Shared` → `Conferences` → `Auth` → `Workflow` → `Registration` → `ParticipantPortal` → `Payments` → `Invoicing` → `Submissions` → `Notifications` → `Reporting`.

---

## 14. Otvorené úlohy a otázky

### 14.1 Z diskusie o backende (11. 11. 2026)

| # | Úloha / otázka | Pracovný návrh | Kto | Potrebné do |
|---|---|---|---|---|
| BO1 | **Overiť tvar variabilného symbolu**: má fakulta / ekonomické oddelenie predpísaný tvar a dĺžku? | Zatiaľ `RRNNNNNN` (BR5) | D s objednávateľom, A | Pred odovzdaním prototypu |
| BO2 | **Potvrdiť s tímom limit prihlásenia**: 3 neúspešné pokusy na e-mail a IP, potom 300 s blokovanie (BR11). Admin je dostupný z internetu (R4) | Implementované s hodnotou 3 / 300 s, meniteľné v `.env` (`LOGIN_MAX_ATTEMPTS`, `LOGIN_DECAY_SECONDS`) | Tím, prípadne s objednávateľom | Pred odovzdaním prototypu |
| BO3 | Potvrdí sa účasť aj pri `conditionally_accepted`? | Nie automaticky, iba `accepted` / `not_required`; organizátor môže potvrdiť ručne | Tím / objednávateľ | Pred M6 `ConfirmRegistrationWhenComplete` |
| BO4 | Platobný stav pri fee waiver alebo nulovej cene | `payment_status = null`, `fee_waived = true` | A | Pred M7 |
| BO5 | Maximálna veľkosť PDF príspevku | 10 MB | B s objednávateľom | Pred M5 |
| BO6 | Ako riešiť preplatok | Zaevidovať, zobraziť upozornenie adminovi, vrátenie mimo systému | A | Pred M7 |
| BO7 | Platí konferenčný kontext aj pre user page (účastník vidí všetky svoje konferencie)? | Áno, user page ignoruje subdoménu, ale filtruje na vlastné registrácie | B | Pred M4 |
| BO8 | Kto môže prideľovať adminov ku konferenciám | Iba superadmin (BR4) | Potvrdiť s objednávateľom | Pred M0 |
| BO9 | ~~Doplniť `AGENTS.md` o Laravel v `/backend` a odkaz na `backend.md` (O36)~~ | **Hotové 11. 11. 2026** v prvom commite do `/backend` | — | — |
| BO10 | Preniesť rozhodnutia BR1–BR11 a otázky BO1–BO19 do hlavnej projektovej dokumentácie | Zapísať ako R37+ a O40+ | D | Do najbližšieho syncu tímu |
| BO11 | **Adresa backendu**: main page na `ans2027.ucm.sk` je statická od IT (R22, R23), ale registrácia a `ResolveConferenceFromHost` potrebujú backend na tej istej subdoméne | Implementované podľa subdomény (`/register` na `ansRRRR.<doména>`); s IT dohodnúť proxy alebo samostatnú adresu a spoločnú doménu session cookie (`SESSION_DOMAIN`) | Tím s IT | Pred nasadením |
| BO12 | **Jazyk a polia formulára**: backend.md R10 chce web v angličtine, `AGENTS.md` slovenské texty a objednávateľ SK/EN; frontendový prototyp má navyše stravovanie a krajinu, nemá tituly ani odvetvie | Backend: texty v `lang/en`, polia podľa sekcie M3; stravovanie nezavedené (O39) | Tím | Pred odovzdaním |
| BO13 | VS pri dvoch konferenciách v jednom roku: `RR` + poradie v rámci konferencie by dalo rovnaké symboly | Implementovaný spoločný číselný rad pre rovnaké `RR` (`variable_symbol_sequences`) | A s BO1 | S BO1 |
| BO14 | Overiť na MariaDB a v Dockeri: migrácie, zámky (`ConcurrencyTest` sa na SQLite preskočí), front `queue:work`, Mailpit | Spustiť `vendor/bin/pest` s `DB_CONNECTION=mariadb`, keď tím dodá prostredie (BR9) | A, M12 | Po dodaní prostredia |
| BO15 | Potvrdiť náhradu `spatie/laravel-permission` vlastnou maticou a Filamentu Blade obrazovkami (sekcia 2) | Implementované; dôvod: roly viazané na konferenciu (BR8) a jednotný vzhľad s prototypom | Tím | Pred obhajobou |
| BO16 | Čiastočná úhrada: posielať e-mail? Sekcia 0 hovorí „čiastočnej s e-mailom“, udalosti v sekcii 8 e-mail neuvádzajú | Implementované podľa US-12: e-mail a faktúra až pri `paid` | Tím | Pred odovzdaním |
| BO17 | E-mail o úhrade pri zlyhaní faktúry | Implementované: e-mail sa pošle bez prílohy (`InvoiceIssueFailed`) | A, C | — |
| BO18 | Fee waiver po zaevidovanej úhrade | Implementované: odmietne sa (vrátenie je plná verzia) | A | — |
| BO19 | Admin čítanie zoznamov a detailov nebolo v zozname features | Doplnené read-only Actions v `Reporting` (`ListConferenceRegistrations`, `GetRegistrationForStaff`, `ListConferenceSubmissions`), vlastník C | C | — |

### 14.2 Z hlavnej dokumentácie (dotýkajú sa backendu)

| # | Otázka | Pracovný návrh | Fáza |
|---|---|---|---|
| O32 | Faktúry: robia sa v systéme a v akom rozsahu? Odpoveď objednávateľa ich podľa repozitára nepotvrdzuje | Testovacia faktúra oddelená od platobného stavu, vypínateľný modul; nestavať na nej ďalšie funkcie | Prototyp |
| O33 | Jazyk: objednávateľ chce SK/EN; platí to aj pre e-maily, program a faktúry? | Texty v prekladových súboroch, kód po anglicky | Prototyp |
| O35 | Podpora PHP 8.5 a MariaDB v Laravel/Filament/balíkoch; vzhľad adminu vs. makety | Overiť pri inštalácii; admin vo Filamente | Pred adminom |
| O36 | `AGENTS.md` opisuje iba statický frontend | Pozri BO9 | Pred prvým commitom |
| O37 | Čo z odborného procesu (R30–R33) patrí do prototypu? | Jednokolové rozhodnutie výboru, čiastočná úhrada; dátový model na celý proces | Prototyp |
| O38 | Skutočné poplatky, je pasívny účastník zadarmo, kto udeľuje oslobodenie? | Sumy z nastavenia ročníka, testovacie 180/120/90 EUR; oslobodenie organizátor | Prototyp |
| O39 | Abstrakt pri posteri, kľúčové slová, spoluautori, stravovanie, tvorba programu, audit, archív — prijímame? | Nepokladať za dohodnuté, kým sa neoverí | Prototyp |
| O31 | Poster: formát, termín, zmena po schválení; môže rečník meniť abstrakt do termínu? | PDF, rovnaký termín ako abstrakty, nová verzia vráti stav na `submitted` | Prototyp |
| O7 | Storno: kedy, vrátenie peňazí, dobropis | Do termínu platby; po faktúre dobropis; vrátenie mimo systému | Plná verzia |
| O8 | Zabezpečenie admina z internetu | HTTPS, obmedzenie pokusov (BO2), 2FA pre adminov | Plná verzia |
| O12 | Uchovávanie údajov medzi ročníkmi | Kým má účet; po zrušení anonymizácia | Plná verzia |
| O16 | Verejný zoznam účastníkov a book of abstracts | Výslovný súhlas vo formulári; meno, organizácia, názov abstraktu | Plná verzia |
| O17 | Zrušenie účtu pri existujúcich faktúrach | Anonymizácia, faktúry archivovať (lehotu overiť) | Plná verzia |
| O24 | Pripomienky | 7 a 1 deň pred termínom; po termíne blokovať, admin výnimka | Plná verzia |
| O29 | Ceny s DPH? | V prototype konečné ceny bez DPH; overiť s ekonomickým oddelením | Plná verzia |
| O30 | Pozvaní hostia v systéme? | Len na main page; ak treba, registrácia s oslobodením | Plná verzia |

---

## 15. Stav implementácie (11. 11. 2026)

### 15.1 Čo je hotové

- Všetky features prototypu zo sekcie 6 (M0, M2–M9, M11) ako Actions s testami; M10 je iba README.
- Pomocné Actions, ktoré si vyžiadalo pravidlo „modul zapisuje iba do vlastných tabuliek“: `Auth\CreateUserAccount`, `Auth\ChangeEmail`, `Auth\UpdateAccountName`, `Registration\CreateRegistration`, `Registration\UpdateParticipantProfile`, `Registration\SetFeeWaived`, `Submissions\InitializeSubmission`, `Submissions\MarkDecisionNotified`; read-only admin Actions v `Reporting` (BO19).
- HTTP vrstva (BR10): verejná stránka konferencie a registrácia na subdoméne, prihlásenie s presmerovaním podľa roly a výberom pri dvoch rolách, obnova hesla, overenie e-mailu, user page (konferencie, detail so stavmi, platobnými inštrukciami, faktúrami a príspevkom, profil), admin (prepínač konferencií, registrácie, vyhľadanie podľa VS, úhrady, oslobodenie, stav registrácie, faktúry, príspevky a rozhodnutie výboru, export, tím konferencie pre superadmina).
- Seeder: konferencie `ans2027` a `ans2028`, účty `superadmin@`, `organizer@` (v 2027 poverený na platby, v 2028 bez poverenia), `committee@`, `participant@` (na oboch konferenciách), `poster@`, `both@` (účastník aj výbor), všetky na doméne `example.test`.
- Pracovné návrhy sú v kóde označené `// PROPOSAL: <otázka>`.

### 15.2 Overenie

- Pest: 234 testov prešlo, 1 preskočený (zámky na MariaDB), PHP 8.5.12 so SQLite v pamäti. Spustenie: `vendor/bin/pest` v `/backend`.
- Reálny server (`php artisan serve`, SQLite, synchrónna fronta) prešiel 18 krokov cez HTTP: registrácia, prihlásenia všetkých rolí, US-08, vyhľadanie VS, čiastočná a plná úhrada, stiahnutie faktúry, export, rozhodnutie výboru, automatické potvrdenie účasti a blokovanie po 3 neúspešných prihláseniach. Testovacia faktúra bola skontrolovaná aj vizuálne (kríž cez celú stranu, TEST INVOICE).
- **Neoverené:** MariaDB, Docker na Debiane 13, `queue:work` s frontou v databáze, doručenie cez Mailpit, správanie na skutočných subdoménach a spoločná session cookie (BO11, BO14).

### 15.3 Štruktúra mimo modulov

| Cesta | Obsah |
|---|---|
| `app/Http/Controllers` | Preklad HTTP na Actions, bez obchodnej logiky |
| `app/Http/Middleware` | Kontext konferencie: subdoména (`conference.host`), vlastník (`conference.owner`), admin (`conference.admin`) |
| `resources/views` | Blade obrazovky; `public/styles.css` je kópia štýlov statického prototypu, `public/backend.css` doplnky |
| `lang/en` | Všetky texty pre používateľa (O33) |
| `tests/Modules`, `tests/CrossCutting`, `tests/Http` | Testy features, prierezové testy, HTTP testy |
