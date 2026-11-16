# Prepojenie frontendu s backendom — analýza a návrh postupu

**Dátum:** 16. 11. 2026  
**Východiskový commit:** `1c053b6` na `main`  
**Stav:** po odsúhlasení začatá a dokončená kompatibilná Blade vrstva pre aktuálne podporovaný rozsah. Pôvodná analýza nižšie ostáva východiskovou snímkou; aktuálny výsledok je v nasledujúcom zázname.

## Záznam implementácie — 16. 11. 2026

- Funkčná aplikácia používa existujúce Laravel Blade rozhranie. Jeden trojkrokový registračný formulár posiela backendové polia, zobrazuje ceny/kategórie zo servera a súhrn. Pri existujúcom účte login zachová pevný návrat k registrácii toho istého hostiteľa.
- Účastnícky dashboard obsahuje tri stavy, vlastné konferencie, odkazy na platbu a profil. Nová Action `ListMySubmissions` a route `/account/submissions` poskytujú vlastné príspevky naprieč konferenciami. Nová Action `GetConferenceOverview` poskytuje počty pre `/admin/overview`, bez financií pre výbor.
- Serverové formuláre pre platby, PDF, výbor, exporty a tím zostávajú napojené na pôvodné Actions. Nový `frontend.js` nemá simulované dáta, storage fallback, prepínač ľubovoľnej roly ani úspech pred serverovou odpoveďou. Pri termíne sa skryje upload, pri vypnutom Invoicing sekcia faktúr účastníka.
- Statické makety sa zachovávajú oddelene; ich odstránenie z GitHub Pages ani prepis verejného hostingu sa nerobil. Recenzie/program/CMS/hromadná komunikácia a polia nad backendový kontrakt neboli doprogramované. SK lokalizácia zostáva nedodaná.
- Duplicitné neversionované súbory sa zachovali mimo Laravelu v lokálnej zálohe `zaloha-duplicit-20261116-212833/`; neinštalujú sa do autoloadu. Composer sa nainštaloval podľa existujúceho locku, bez aktualizácie balíkov. Lokálny `.env` a oddelená SQLite databáza slúžia iba na skúšanie, nie sú verzované.
- Overené: 271 testov / 662 assertions (PHP 8.5.4, SQLite), kompilácia Blade a syntax JS; Safari kroky/súhrn registrácie, login, dashboard s dvoma konferenciami a detail platobných údajov/PDF formulára. **Neoverené:** mobilné šírky, MariaDB, Docker, Mailpit/databázová fronta, skutočné subdomény/HTTPS/proxy. Nasadenie ani push sa nerobili.
- Aktualizované hlavné príručky a `backend.md`. Aktuálne používanie je v [používateľskej príručke](pouzivatelska-prirucka.md); nasledujúce kapitoly dokumentujú pôvodný rozbor a plán.

## 1. Záver a odporúčanie

Frontend a backend dnes tvoria dve samostatné aplikácie v jednom repozitári:

- Koreň obsahuje 36 položiek katalógu obrazoviek, samostatné HTML stránky a JavaScript so simulovanými údajmi a operáciami.
- `backend/` obsahuje Laravel s databázovým modelom, Actions, oprávneniami, HTTP routami a vlastným základným rozhraním v Blade. Registrácia, platby, upload PDF či rozhodnutie výboru už majú implementovanú serverovú cestu.

**Návrh N1: existujúce frontendové rozhranie preniesť do Blade a prepojiť cez existujúce controllery a Actions.** Server bude dodávať údaje aj vyhodnocovať operácie. JavaScript zostane na kroky formulára, záložky, dialógy a ďalšie ovládanie rozhrania.

Nie je potrebné najprv vytvoriť kompletné REST API ani zaviesť ďalší frontendový framework. Súčasné routy väčšinou vracajú HTML a presmerovania, nie JSON kontrakt pre samostatnú klientsku aplikáciu. Výnimkou je napríklad kontrola existujúceho e-mailu.

Výsledkom bude jedna serverová aplikácia pre funkčnú registráciu, účastnícku zónu a administráciu. Verejná prezentačná stránka konferencie môže podľa existujúceho rozhodnutia zostať statická od IT; jej prechod do Laravelu nie je automatickou súčasťou návrhu.

**Prepojenie všetkých makiet nie je iba výmena dát.** Recenzie, program, hromadná komunikácia, editor webu a ďalšie časti nemajú hotový backend. Ich implementácia by rozšírila rozsah dnešného backendového prototypu. Treba odsúhlasiť, ktoré budú v prvej etape dostupné a ktoré odložíme.

## 2. Čo bolo preskúmané a čo ešte nie je overené

Analýza vychádza z aktuálneho lokálneho kódu, najmä:

- [Pokyny](../AGENTS.md), [AI príručka](ai-prirucka.md), [backendové rozhodnutia](backend.md), [frontendová analýza](frontend-analyza.md), [pripojenie databázy](prepojenie-databazy.md).
- [Katalóg obrazoviek](../routes.js), [simulované operácie](../app.js), [vzorové dáta a UI pomôcky](../ui.js), HTML formuláre a editor v `website-*.js`.
- [HTTP routy](../backend/routes/web.php), controllery, middleware, Blade šablóny, Actions, kontext konferencie, matica oprávnení a existujúce HTTP testy.

**Rozlíšenie statusov v tomto dokumente:** fakt = čítaním zistený stav kódu; návrh = plánované riešenie; otvorené rozhodnutie = vyžaduje dohodu. Prítomnosť kódu neznamená potvrdenú prevádzku.

Lokálne je PHP 8.5.4 a verzovaný `composer.lock`. V analyzovanom checkoute nie je `backend/vendor/` ani `backend/.env`; Composer nebol dostupný v PATH. Závislosti som neinštaloval, databázu nepripájal a testy ani server nespúšťal. Údaj o 260 úspešných testoch na SQLite je zo zápisu predchádzajúceho overenia v `backend.md`, nie z tejto analýzy. MariaDB, databázová fronta a reálne subdomény zostávajú podľa dokumentácie neoverené.

Pri inventúre sa objavilo **130 neversionovaných súborov s príponou názvu ` 2`**, prevažne v backende. Ich pôvod nie je potvrdený; po predchádzajúcom pulli bol pracovný strom čistý. V tejto úlohe sa nemenili ani nemazali. Pred spustením/autoloadom ich treba porovnať s originálmi a bezpečne oddeliť od aplikácie. Duplicitné deklarácie tried môžu komplikovať Composer autoload.

## 3. Ako je backend postavený

### 3.1 Tok požiadavky

`prehliadač → Laravel route → middleware identity a konferencie → controller → Action → databáza / privátne súbory → Blade alebo presmerovanie`

- Action vykonáva validáciu, kontrolu oprávnení, obchodné pravidlá a príslušnú transakciu. Nové UI nesmie vytvoriť paralelnú implementáciu cien, platieb alebo stavov.
- Moduly sú v `backend/app/Modules`: Conferences, Auth, Registration, ParticipantPortal, Submissions, Workflow, Payments, Invoicing, Notifications a Reporting. Deadlines má iba prípravnú dokumentáciu.
- Zmeny stavov riadi `Workflow/Actions/ChangeStatus`. Registrácia, platba a príspevok majú samostatný stav.
- Peniaze sa ukladajú v centoch s menou; cena registrácie je snapshot z kategórie konferencie.
- PDF súbory sú na privátnom disku. Prístup ide cez kontrolované download routy.
- E-maily a faktúry používajú udalosti a listenery; pre databázovú frontu je potrebný worker. Uložená úhrada a doručený e-mail sú dva odlišné výsledky.

### 3.2 Identita a konferenčný kontext

| Oblasť | Aktuálne riešenie | Dôsledok pre frontend |
|---|---|---|
| Prihlásenie | Session autentifikácia; po login regenerácia session; logout cez POST | Použiť serverovú identitu a CSRF, odstrániť verejný prepínač rolí |
| Registrácia | Konferencia podľa prvého segmentu hostiteľa, napr. `ans2027.localhost` | Konferenciu neurčí HTML ani ľubovoľný parameter z klienta |
| Účastnícka zóna | Kontext vlastníka a explicitné kontroly vlastníctva v Actions | Účastník má jeden účet a môže vidieť svoje registrácie na viacerých konferenciách |
| Administrácia | Aktívna konferencia v session, priradenie sa kontroluje pri každej požiadavke | Prepínač zobrazuje iba dovolené konferencie; odkazy a filtre rešpektujú aktuálny kontext |
| Globálne roly | `participant`, `superadmin` | Nevytvárať ich výberom v prehliadači |
| Roly konferencie | `organizer`, `programme_committee`; ďalšie iba pripravené | Recenzent ani ekonóm zatiaľ nemajú dokončený funkčný tok |
| Poverenie na platby | Osobitné oprávnenia organizátora v konkrétnej konferencii | Organizátor bez poverenia nesmie zaevidovať platbu |

`ConferenceScope` pri režime vlastníka obmedzuje dáta na konferencie, kde má používateľ registráciu; **nenahrádza kontrolu vlastníka konkrétneho záznamu**. Nové zoznamy a detaily musia naďalej explicitne filtrovať vlastného používateľa, prípadne použiť existujúcu Action.

Programový výbor nesmie dostať finančné údaje. Pri serverovom renderovaní treba strážiť aj zdroj HTML a prípadné vložené JSON dáta; nestačí skryť stĺpec cez CSS. Ak vznikne JSON endpoint, musí mať výslovne obmedzený výstup podľa oprávnení.

## 4. Pokrytie frontendových obrazoviek

„Priamo“ znamená existujúcu backendovú cestu, na ktorú možno naviazať nové rozhranie. Neznamená identický layout ani automatickú podporu všetkých prvkov makety.

| Obrazovky z `routes.js` | Dnešný backend | Návrh prvej etapy |
|---|---|---|
| V01 — úvod | Základná Blade konferencia; názov, dátumy, miesto, poplatky, termíny z DB | Rozhodnúť statický úvod vs. Laravel. Banner, rečníci, partneri nemajú CMS model |
| V02 — program | Bez modelu, editora a publikovania programu | Statická schválená informácia alebo pravdivý prázdny stav; editor vyžaduje novú etapu |
| V03 — praktické informácie | Miesto a externý odkaz na ubytovanie; bez kompletného obsahového modulu | Dodaný statický obsah, údaje dostupné v konfigurácii konferencie |
| V04 — ochrana údajov | Konferencia má `privacy_url`; text nespravuje backend | Použiť dodaný text/odkaz; nevydávať vzorový text za schválený |
| A01 — login | GET/POST `/login`, POST `/logout`, výber `/choose` | Priamo; reálne overenie a presmerovanie podľa oprávnení |
| A02 — registrácia | GET/POST `/register`, POST `/register/existing`, GET `/register/check-email` | Priamo po zosúladení polí; doplniť kroky a záverečné zhrnutie |
| A03–A04 — obnova hesla | `/forgot-password`, `/reset-password/{token}`, POST `/reset-password` | Priamo; skutočné tokeny a odkazy z e-mailu |
| U01 — prehľad | `/account`: zoznam vlastných registrácií, najnovšia konferencia prvá | Priamo; dashboard postaviť nad vlastnými registráciami, doplniť čítaciu Action podľa potreby |
| U02 — profil | GET/POST `/account/profile`, opätovné overenie zmeneného e-mailu | Priamo; iba podporované polia, fakturačný snapshot sa neprepisuje |
| U03 — účasť a platba | `/account/registrations/{registration}`: stavy, zostatok, inštrukcie, faktúry, história | Priamo; konkrétna registrácia musí byť jasne zvolená |
| U04 — moje príspevky | Príspevok v detaile vlastnej registrácie; samostatný zoznam nemá route | Nový čítací pohľad/Action cez vlastné registrácie; bez zmeny počtu príspevkov |
| U05 — nový príspevok | POST `/account/submissions/{submission}/submit` | UI odovzdania už založeného záznamu: názov + PDF, nie vytváranie neobmedzených príspevkov |
| U06 — detail príspevku | Verzie, rozhodnutia a upload sú v detaile registrácie; download route existuje | Samostatný pohľad alebo sekcia detailu nad rovnakými Actions |
| O01 — prehľad ročníka | Bez agregovaného dashboardu; `/admin` presmeruje na registrácie | Doplniť read-only súhrnnú Action s kontrolou oprávnení; žiadne pevné počty |
| O02–O03 — účastníci a detail | `/admin/registrations`, detail, vyhľadanie VS, platby, oslobodenie, stav, faktúry | Priamo; serverové filtre a stránkovanie |
| O04–O05 — príspevky a kontrola | `/admin/submissions`, detail, PDF, rozhodnutie | Priamo pre existujúce práva; organizátor nesmie dostať rozhodovanie výboru |
| O06 — priebeh recenzií | Tabuľka `reviews` je pripravená; proces chýba | Odložiť funkciu alebo osobitne schváliť modul recenzií |
| O07 — editor programu | Chýba | Samostatné rozšírenie backendu |
| O08 — komunikácia | Automatické e-maily a log existujú; hromadný editor/odosielanie chýba | Odložiť hromadnú komunikáciu; jej UI nesmie simulovať úspešné odoslanie |
| O09 — exporty | `/admin/exports`: XLSX/CSV registrácií podľa roly a stavov | Priamo v podporovanom rozsahu; vlastný výber stĺpcov, ďalšie typy listín sú rozšírenie |
| R01–R02 — recenzent | Rola pripravená, bez workflow a UI | Samostatná etapa |
| C01 + zoznam príspevkov výboru | Existujúci zoznam/detail a POST `/admin/submissions/{submission}/decision` | Priamo; jednokolové rozhodnutie, bez predstierania recenzií |
| C02 — schválenie programu | Chýba | Spolu s modulom programu |
| E01–E02 + ekonomický export | Platby/export existujú, samostatná rola ekonóma nie je zapojená | V prvej etape platby cez povereného organizátora; samostatnú rolu osobitne schváliť |
| S01 — konferencie a ročníky | Zoznam dostupných konferencií/prepínač; vytvorenie konferencie cez CLI a Action | Zobraziť reálne konferencie; vytvárací formulár a archív sú rozšírenie |
| S02 — nastavenia ročníka | Vytvorenie z JSON cez CLI; editácia nastavení nemá Action ani HTTP route | Samostatne schváliť aktualizačné Actions a formuláre |
| S03 — pracovníci | `/admin/team`: vytvorenie účtu, priradenie/odobratie povolených rolí a poverení | Priamo v aktuálnej konferencii; nie ľubovoľná globálna správa všetkých rolí |
| S04 — záznam operácií | História stavov a log e-mailov; bez kompletného auditu a auditnej stránky | Zobraziť len skutočné histórie; plný audit samostatne |
| S05 — editor webu | IndexedDB, lokálne verzie a obrázky; serverová časť chýba | Zachovať iba ako oddelený návrhový prototyp alebo osobitne navrhnúť CMS |

## 5. Rozdiely v dátach, ktoré treba vyriešiť pred prepojením

### 5.1 Registrácia a profil

| Dnešný frontend | Backendový kontrakt | Navrhovaná úprava UI |
|---|---|---|
| `firstName`, `lastName` | `first_name`, `last_name` | Použiť priamo názvy backendových polí |
| `passwordConfirmation` | `password_confirmation` | Zosúladiť; heslo neposielať medzi stránkami ani neukladať do browser storage |
| Kategória ako slovenský text | `speaker`, `poster_author`, `passive_participant` | Hodnota poľa je kód, viditeľný text je preklad |
| Cena 180/120/90 EUR v JS/HTML | Kategórie a ceny konkrétnej konferencie z DB | Súhrn načíta ceny zo servera; finálnu cenu vždy určí Action |
| Titul/`suffix` v profile | `title_before`, `title_after` | Doplniť aj do registrácie |
| Voľné osobné polia | Backend vyžaduje aj odvetvie, ak konferencia má zoznam | Možnosti z `GetRegistrationFormOptions`, nie z pevného HTML |
| `billingOrganization`, všeobecné mesto/krajina | `billing_type`, `billing_name`, ulica, mesto, PSČ, krajina, voliteľné identifikátory | Samostatná kompletná fakturačná sekcia; nefabrikovať povinné chýbajúce hodnoty |
| Bez kompatibilného serverového súhlasu | `data_processing_consent`, čas záznamu zo servera | Výslovný checkbox a odkaz na `privacy_url` |
| Telefón, oddelenie, osobná krajina, stravovacie obmedzenia | Nemajú zodpovedajúce uloženie v dnešnom kontrakte | Rozhodnúť rozšírenie alebo odklad; neponúkať úspešné uloženie neukladaného poľa |

`billing_type` má hodnoty `participant`, `university`, `workplace`. Profil je spoločný účtu; fakturačné údaje patria registrácii na konkrétnu konferenciu.

**Navrhovaný registračný tok:** (1) účet a osobné údaje → (2) kategória, odvetvie a fakturácia → (3) zhrnutie ceny/údajov a súhlas → jeden finálny POST. Použiť jeden formulár s krokmi ovládanými JS; heslá zostanú iba vo formulári v pamäti stránky. Chyba servera otvorí krok s chybou a zachová bezpečné údaje, heslo sa znova zadá. Bez JS bude použiteľný celý formulár.

Existujúci účet dostane prihlásenie a následný návrat do registrácie tej istej konferencie. Treba explicitne preveriť/doplniť bezpečný návratový cieľ; dnešný odkaz na login túto celú cestu sám nezaručuje. Už zaregistrovaný používateľ dostane odkaz na svoju registráciu, nie druhý záznam.

### 5.2 Príspevok a odborný proces

- Backend zakladá **jeden príspevok na registráciu** už pri registrácii. Pasívna účasť má stav `not_required`. Typ príspevku vychádza z kategórie, nie zo slobodného výberu v makete.
- Prvé odovzdanie prijíma `title` a `file` ako multipart formulár. PDF sa overuje podľa obsahu/MIME a hlavičky, limit je konfigurovateľný (aktuálne 10 MiB).
- Text abstraktu, kľúčové slová, spoluautori a tematická sekcia nemajú v dnešnom backende plné dátové pokrytie. PDF abstraktu nenahrádza databázové polia pre tieto funkcie.
- **Skutočný kód `ReplaceSubmissionFile` povoľuje výmenu len v stave `submitted` a pred termínom.** Po rozhodnutí už výmena neprejde. Všeobecnejšie formulácie v dokumentácii o návrate na `submitted` nesmú viesť k tichej zmene tohto pravidla.
- Rozhodnutie posiela `decision` a `justification`; frontend dnes používa `reason`. Jednokolové rozhodnutie nie je recenzný posudok ani celý zákazníkom požadovaný odborný proces.

### 5.3 Platby, exporty a stavy

- Frontend dnes prepisuje jednu simulovanú sumu; backend **pridáva jednotlivé úhrady a sčítava ich**. Formulár preto musí hovoriť o novej prijatej úhrade, nie o prepísaní celkového zaplateného.
- Platba posiela `amount`, `paid_on`, voliteľne `note`. `paymentDate` sa premenuje; pole `reference` nemá automatický ekvivalent. VS dodá registrácia.
- Predpis, súčet úhrad, zostatok, preplatok a oslobodenie dodá `GetPaymentBalance`. Hodnota 180 EUR ani skúšobný VS nesmú zostať v obsluhe UI.
- Registrácia: `received`, `confirmed`, `cancelled`. Platba v aktuálnom toku: `pending`, `partially_paid`, `paid`, prípadne `null` pri nulovej povinnosti. Príspevok: `draft`, `submitted`, `accepted`, `conditionally_accepted`, `rejected`, `not_required`.
- Ďalšie hodnoty enumov pripravené pre plnú verziu neznamenajú dostupný proces. UI nesmie ponúkať nepodporované prechody.
- Faktúra má vlastný stav a môže byť vypnutá. Uloženie úhrady nesmie v UI tvrdiť, že už vzniklo PDF alebo bol doručený e-mail.
- Backend exportuje XLSX/CSV s povolenými stĺpcami podľa oprávnení. Maketa výberu ľubovoľných stĺpcov, platobných filtrov a rôznych typov listín presahuje dnešný kontrakt.

## 6. Čo odstránime z funkčnej aplikácie

Po schválení, pri migrácii príslušnej oblasti:

1. Pevné osoby, príspevky a abstrakty z `ui.js` aj z HTML a `<template>` variantov. Rovnako počty, dátumy, ceny, VS, výsledky recenzií, platby a auditné udalosti vložené napevno.
2. `ucm-prototype-state-v1`, `registration`, `savedForms`, `savedSubmission`, `paymentOverrides` ako zdroj obchodných dát. Stará browser storage sa neimportuje do databázy ani nevyužije ako záloha pri chybe backendu.
3. Simulovaný login, reset hesla, lokálne rozhodnutie, úhradu, odoslanie správy a CSV export v `app.js`. Nová aplikácia nenačíta tento centrálny handler, ktorý dnes zachytáva všetky formuláre.
4. Výber ľubovoľnej roly, verejnú mapu interných obrazoviek a prechody na cudzie účty bez autentifikácie.
5. Fallback na `record-0` pri neznámom ID; server vráti 403/404 alebo pravdivý prázdny stav podľa typu problému.
6. S05 IndexedDB nebude zdrojom serverovej konferencie ani serverovým publikovaním.

Pomôcky pre ikony, bezpečné zobrazenie textu a ovládanie dialógov možno prevziať po oddelení od simulácií. Browser môže držať stav otvorenej záložky či menu; autoritatívne údaje zostanú na serveri.

**Seed nie je frontendový mock.** Vývojové/testovacie seed dáta môžu zostať v oddelenom vývojovom prostredí a testoch. Bežná inštalácia bude používať skutočne založené konferencie/účty cez pripravené CLI; prázdna DB musí ukázať prázdny stav. Nebudeme automaticky prepisovať ani mazať existujúcu DB.

## 7. Technický spôsob prepojenia

### 7.1 Blade, HTTP a JavaScript

- Preniesť schválené rozloženie HTML do `backend/resources/views`; opakované prvky vytvoriť ako spoločné layouty/partials alebo Blade komponenty.
- Použiť existujúce pomenované routy, `@csrf`, serverové validačné chyby, bezpečné `old()` a vzor POST → presmerovanie → GET.
- GET filtre budú spracované na serveri a zachované pri stránkovaní. Súčasné filtrovanie iba riadkov v DOM nesmie predstierať hľadanie v celej DB.
- Nové čítacie Actions vzniknú len tam, kde chýba konkrétny zoznam/súhrn (napr. dashboard). Nebudú obchádzať `ConferenceContext` ani používať počty z aktuálnej strany tabuľky ako celkové štatistiky.
- Nahrávanie cez `multipart/form-data`, s vysvetlením limitu a chybového výsledku; privátne PDF cez existujúce download routy.
- Nový UI JavaScript nebude ukladať platby, rozhodnutia ani registrácie do storage. Obchodná operácia má úspech až po úspešnej serverovej odpovedi.
- Ošetriť vypršanú session/CSRF, chybné polia, zakázaný prístup, chýbajúci záznam aj chybu servera. Pri chybe nezobrazovať falošné potvrdenie.
- Počas odosielania zablokovať opakované kliknutie. Pri platbe po prerušení odpovede najprv overiť zoznam úhrad; rovnaký POST dnes môže vytvoriť ďalšiu platbu. Prípadné idempotency pravidlo je osobitná backendová zmena s testom.

### 7.2 Súbory a štýly

- Funkčný vstup aplikácie bude `backend/public/`; webserver nesmie sprístupniť koreň Laravel projektu s konfiguráciou a storage.
- Funkčné štýly a JS budú mať jeden udržiavaný zdroj v backende. Dnešné `styles.css` a `backend/public/styles.css` sú kópie; pri prenose treba odstrániť potrebu ich ručného súbežného udržiavania.
- Pôvodný statický prototyp sa zatiaľ zachová pre porovnanie a existujúce GitHub Pages. Nebude sa z neho otvárať funkčná administrácia ani zadávať skutočné údaje. Odstránenie zo zdrojov/Pages je samostatný schvaľovaný krok po akceptácii náhrady.
- Existujúce HTML odkazy a `routes.js` nie sú cieľový serverový router. Staré interné odkazy sa v novej aplikácii nahradia pomenovanými routami; verejné publikované odkazy dostanú dohodnutý prechod.

### 7.3 Hosting — otvorené BO11

Odporúčaný variant zachová statickú konferenčnú prezentáciu od IT a cez reverse proxy na rovnakom hostiteľovi pripojí `/register`, `/login`, `/account`, `/admin`, obnovu/overenie e-mailu a príslušné assets k Laravelu. Assets je vhodné oddeliť URL prefixom, aby nekolidovali so statickými štýlmi. Konkrétna konfigurácia patrí do implementácie až po poznaní prostredia.

Tým registrácia na `ans2027.…` zachová dnešné určovanie konferencie z hostiteľa. Pri proxy treba overiť odovzdanie hostiteľa, HTTPS, dôveryhodné proxy a absolútne odkazy v e-mailoch. Cross-subdomain session cookie nie je potrebná pre jednu konferenčnú doménu; ak má účet plynulo fungovať medzi subdoménami alebo centrálnou adresou, treba výslovne rozhodnúť doménu cookie a model dôvery medzi hostiteľmi.

Alternatíva: centrálna doména aplikácie s konferenciou v URL. Tá vyžaduje nový resolver konferencie a úpravu verejného registračného kontraktu. Nie je to iba prelinkovanie existujúceho `/register`. Samostatný statický frontend cez cross-origin API by navyše vyžadoval API kontrakty a riešenie cookies/CORS; pre prvú etapu ho neodporúčam.

## 8. Postup implementácie po odsúhlasení

| Krok | Konkrétny výsledok | Podmienka dokončenia |
|---|---|---|
| 0. Uzavretie rozsahu | Potvrdené rozhodnutia P1–P6 nižšie a zoznam funkcií prvej etapy | Neexistuje nejasnosť, ktoré makety sa pripájajú a ktoré odkladajú |
| 1. Stabilný checkout a prostredie | Záloha lokálnych zmien; vyriešené duplicitné súbory; závislosti podľa locku; izolovaná DB a mail/fronta | Známy výsledok existujúcich testov; MariaDB db-check; žiadne testy nad pracovnou DB |
| 2. Spoločné rozhranie a identita | Blade layouty, navigácia podľa práv, assets, login/logout/reset a výber pracovného rozhrania | Priamy vstup na chránené URL, session a zakázané roly overené |
| 3. Registrácia | Viackrokový formulár, skutočné kategórie/ceny, fakturácia, súhlas, existujúci účet | Odoslanie vytvorí jednu registráciu; chyby vrátia správny krok; návrat po login zachová konferenciu |
| 4. Účastnícka zóna | Dashboard, vlastné konferencie, profil, detail, platobné inštrukcie, PDF/verzie/rozhodnutia | Údaje prežijú refresh a nové prihlásenie; cudzie záznamy nie sú dostupné |
| 5. Organizátor a výbor | Reálne zoznamy/detail, filtre, stránkovanie, platby, oslobodenie, rozhodnutie, export, tím | Oprávnenia a dve konferencie oddelené; výbor bez financií; žiadne simulované akcie |
| 6. Čistenie a dokumentácia | Vo funkčnom UI žiadne mock dáta, nepodporované ovládanie ani storage fallback | Vyhľadané aj HTML/template kópie; dokumentácia opisuje skutočné funkcie a limity |
| 7. Akceptácia a príprava nasadenia | Overený celý tok v prehliadači a na MariaDB, mobil, fronta a odkazy | Záznam výsledkov; záloha DB/súborov a konkrétny postup návratu |

Kroky 2–4 vytvoria najskôr ucelenú funkčnú cestu nového účastníka. Až potom sa prenesie zvyšok administrácie. Zákazníkov feedback dáva vizuálnu prioritu úvodu, registrácii a dashboardu; pri tomto prepojení nebudeme bez dohody robiť celkový redesign všetkých obrazoviek. Rozloženie týchto troch častí treba koordinovať s ďalšou vizuálnou revíziou, aby sa zbytočne neprerábali dvakrát.

Nové HTTP/čítacie features a prípadné nové migrácie budú malé samostatne overiteľné zmeny. Existujúce migrácie sa neprepisujú. Doménové pravidlá sa nemenia len preto, aby sedeli s maketou. Tento návrh nemení staršie rozhodnutia o jednej vetve `main`; commity ani push zatiaľ nevznikajú.

## 9. Akceptačné scenáre

1. **Nový účastník:** kategória a poplatok z DB → registrácia → vlastný dashboard → správny VS/IBAN/termíny. Hodnoty zostanú po odhlásení a prihlásení; browser storage ich neurčuje.
2. **Existujúci účet:** ponuka login → návrat ku konkrétnej konferencii → nová registrácia bez druhého účtu; opakovaná registrácia rovnakej konferencie odmietnutá.
3. **Viac konferencií:** účastník vidí len svoje registrácie; organizátor iba priradené konferencie. Zmena konferencie neukáže staré výbery alebo cudzie detaily.
4. **Profil:** zmena mena/organizácie sa uloží, zmena e-mailu vyžiada overenie, fakturačné údaje existujúcej registrácie zostanú snapshotom.
5. **PDF:** prvé odovzdanie, povolená nová verzia a download; nesprávny formát, veľkosť, cudzí súbor, uplynutý termín a nepovolený stav odmietnuté.
6. **Platba:** dve čiastočné úhrady sa sčítajú; plná úhrada zmení stav; preplatok je upozornenie; bez poverenia POST zlyhá. Faktúra vznikne podľa nastavenia, úhrada zostáva aj pri chybe PDF/e-mailu.
7. **Výbor:** jednokolové rozhodnutie sa uloží a zobrazí autorovi; organizátor bez oprávnenia rozhodnutie neodošle. Výbor nevidí financie v HTML, exporte ani dátach skriptov.
8. **Export:** údaje z DB pre aktívnu konferenciu a zvolený podporovaný filter, povolené stĺpce podľa roly; reálny XLSX/CSV download.
9. **Prázdne/chybové stavy:** prázdna DB bez ukážkových ľudí, 403/404 bez fallbacku, vypršaná session a validačná chyba bez simulovaného úspechu.
10. **Mobil a navigácia:** registrácia, dashboard a tabuľky na telefóne; krok späť, refresh, priame URL, klávesnica a čitateľné chyby. Pri zadaní hesla a pri finančných operáciách žiadny browser-storage zápis.
11. **Prevádzka:** existujúca Pest sada aj doplnené HTTP testy na oddelenej testovacej DB; MariaDB db-check, skutočný worker/Mailpit, odkaz obnovy hesla a overenia e-mailu cez správny hostiteľ/HTTPS.

Počet starších testov nie je kritériom úspechu sám osebe. Odovzdáme skutočný výsledok s použitým prostredím a zoznamom neoverených bodov. Automatické HTTP testy doplní overenie v reálnom prehliadači, najmä CSRF/session a viac krokov formulára.

## 10. Rozpory a rozhodnutia na odsúhlasenie

Aktuálne `backend.md` je autoritou backendových pravidiel. Staršie úvodné kapitoly AI príručky hovoria o neprítomnosti backendu a niektoré časti `backend.md` stále opisujú budúci Filament či migrácie, hoci kód už obsahuje Blade a migrácie. V implementácii sa dokumenty zosúladia; toto nie je dôvod prepisovať funkčný backend podľa starého textu.

**Skutočné obsahové rozpory sa nesmú vyriešiť iba skrytím polí:** staršia frontendová analýza označuje stravovacie údaje a SK/EN ako požiadavky zákazníka, kým backend ich uvádza ako otvorené/nezavedené. Podobne celý recenzný proces patrí do širšieho zadania, ale aktuálny backendový prototyp má len jednokolové rozhodnutie. Ich odklad znamená iba etapizáciu, nie zrušenie požiadavky.

| ID | Čo treba rozhodnúť | Odporúčanie na prvú etapu |
|---|---|---|
| P1 | Spôsob prepojenia | Blade nad existujúcim Laravelom, session autentifikácia, Actions ako jediná obchodná vrstva |
| P2 | Rozsah funkcií | Najskôr celý podporovaný tok registrácia → účastník → platby → PDF → výbor → export/tím; nepodporované časti odložiť a vo funkčnej navigácii ich nesimulovať |
| P3 | Polia nad dnešný kontrakt | Potvrdiť odklad alebo pridanie stravovania, spoluautorov, textového abstraktu, kľúčových slov a ďalších polí. Podporované polia neodstraňovať; nové povinné polia z backendu doplniť |
| P4 | Jazyk | Pracovný návrh: prvá funkčná verzia EN podľa backendu, všetky texty cez preklady; SK/EN označiť ako ďalšiu dohodnutú dodávku. Ak má byť SK/EN hneď, zaradiť do tejto etapy |
| P5 | Úvod a hosting | Statická verejná stránka + Laravel na rovnakom konferenčnom hostiteľovi cez proxy; potvrdiť s IT. Ak má byť celý verejný web v Laraveli, výslovne zmeniť doterajšie rozhodnutie |
| P6 | Rozsah vizuálnej úpravy a pôvodný prototyp | Pri prepojení zachovať použiteľné rozloženie; priorita registrácia/dashboard, úvod podľa P5. Pôvodné Pages zachovať oddelene do akceptácie, celkový redesign dohodnúť osobitne |

**Na začiatok prípravy prostredia bude potrebné:** kde aplikácia pobeží, prístup k určenému vývojovému/testovaciemu prostrediu a potvrdenie adresného modelu. Tajomstvá zostanú mimo dokumentácie/Gitu. Bez týchto údajov možno po schválení pripraviť lokálne UI, ale nemožno vyhlásiť hotové reálne nasadenie a MariaDB overenie.

**Pôvodná analýza bola pripravená bez implementácie; následná používateľova požiadavka povolila úpravu kompatibilného frontendu. Výsledok a limity sú v zázname implementácie na začiatku dokumentu.**
