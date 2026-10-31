# UCM Conferences – frontend prototyp

Preklikateľný dizajnový prototyp konferenčného systému pre ANS 2027. Čisté HTML, CSS a JavaScript moduly, bez build kroku a bez aplikačných závislostí.

https://ondrosvk.github.io/UCM-Conferences-timovy-projekt/?utm_source=chatgpt.com

## Spustenie

V priečinku aplikácie (po klonovaní v koreni repozitára) spusti:

```sh
python3 -m http.server 8766 --bind 127.0.0.1
```

Otvor **http://localhost:8766/**. Funguje aj Live Server z VS Code. Použi HTTP server; priamy `file://` náhľad nenačíta JavaScript moduly.

Priame vstupy:

- [Verejná konferencia](http://localhost:8766/index.html)
- [Účastnícky účet](http://localhost:8766/participant/account.html)
- [Organizátor](http://localhost:8766/organizer/organizer.html)
- [Recenzent](http://localhost:8766/reviewer/reviews.html)
- [Programový výbor](http://localhost:8766/committee/committee-submissions.html)
- [Ekonomické oddelenie](http://localhost:8766/finance/payments.html)
- [Správa systému](http://localhost:8766/superadmin/conferences.html)

Horný prepínač mení rozhranie. **Mapa obrazoviek** otvorí všetkých 33 obrazoviek z analýzy a dva pomocné zoznamy. Každá obrazovka je samostatný HTML dokument s vlastnou adresou. Funguje späť/vpred aj priame otvorenie bez serverových rewrite pravidiel. Pôvodné odkazy `#/…` sa presmerujú na nové HTML adresy.

## Obsah a interakcie

- Verejný úvod, trojdňový program, praktické informácie a textová maketa ochrany údajov.
- Prihlásenie, tri registračné kroky a obrazovky obnovy hesla.
- Účastnícky prehľad, profil, účasť a platba, príspevky, editor a detail so záložkami.
- Organizátorský prehľad, účastníci a detaily, príspevky a kontrola, recenzie, editor programu, komunikácia a exporty.
- Recenzentský formulár, rozhodnutie výboru, schvaľovanie programu.
- Platby a detail úhrady; konferencie/ročníky, nastavenia, pracovníci a záznam operácií.
- Vyhľadávanie a filtre tabuliek, prepínanie dní, dialógy, pridanie spoluautora, výber lokálneho súboru, náhľad správy a stiahnutie ukážkového CSV.

Všetky mená, počty, ceny, termíny a program ročníka 2027 sú vzorové. Prototyp nie je potvrdením finálneho rozsahu ani pravidiel konferencie. Nie je zapojený backend, autentifikácia, databáza, e-mail, bankové služby ani upload. Roly sú navigácia, nie zabezpečenie. Niektoré akcie iba zobrazia dialóg alebo potvrdenie; netvoria úplný stavový proces. Vybrané formuláre si pamätajú vzorové údaje počas práce v jednej karte cez `sessionStorage`, aj medzi stránkami a po obnovení. Zavretím karty sa relácia skončí. Heslá a lokálne súbory sa neukladajú. Pri vypnutom úložisku fungujú obrazovky aj interakcie na aktuálnej stránke, údaje sa medzi stránkami neprenesú.

CSV je iba lokálna ukážka; finálny Excel export zatiaľ nie je implementovaný. PDF náhľad zobrazuje ilustračný text, nie skutočný dokument.

## Štruktúra a úprava obrazoviek

- `index.html` – verejný úvod konferencie.
- `public/` – program, praktické informácie a ochrana údajov.
- `auth/` – prihlásenie, registrácia a obnova hesla.
- `participant/` – účastnícky účet, profil, účasť a príspevky.
- `organizer/` – organizátorský prehľad, registrácie, príspevky, recenzie, program, správy a exporty.
- `reviewer/`, `committee/`, `finance/`, `superadmin/` – obrazovky príslušných rolí.
- `styles.css` – spoločný vizuál a responzívne pravidlá.
- `app.js` – udalosti, formuláre, dialógy a uchovanie vzorového stavu.
- `routes.js` – katalóg obrazoviek a cesty pre prepínač rolí a mapu.
- `ui.js` – malé pomocné prvky dialógov, ikony a vzorové dáta.
- `docs/` – analýza a dokumentácia prototypu.

Texty, polia a rozloženie obrazovky upravuj priamo v jej HTML. Obsah je v `#screen-content` a zobrazí sa aj bez JavaScriptu. Spoločná navigácia je súčasťou každého dokumentu; pri zmene spoločnej položky ju uprav na dotknutých stránkach.

Dynamické časti sú obyčajné HTML `<template>` v tom istom súbore:

- `auth/register.html`: `register-step-0` až `register-step-2` – tri kroky registrácie;
- `participant/submission-detail.html` a `superadmin/settings.html`: `tab-0` až `tab-3`;
- stránky programu: `schedule-0` až `schedule-2` – tri dni;
- detailové stránky: `record-0`, `record-1`, … – varianty vzorových záznamov podľa `?id=`.

Prvá zobrazená verzia je v `#screen-content`, interaktívne varianty v pomenovaných šablónach pod obsahom stránky. Pri zmene predvoleného variantu uprav aj jeho šablónu. JavaScript iba prepína varianty a dopĺňa používateľom zadané údaje. HTML sa negeneruje build skriptom a nie je potrebný balíčkovací nástroj.

Príklad: účastnícky profil uprav v `participant/profile.html`, editor programu v `organizer/admin-program.html`. Pri pridaní novej obrazovky doplň aj katalóg v `routes.js` a príslušné odkazy navigácie. Relatívne odkazy fungujú aj na GitHub Pages pod cestou repozitára.

Aplikácia je samostatná: písmo a jeho licencia sú v `assets/`, favicon v `favicon.ico`. Repozitár obsahuje iba aplikáciu a jej dokumentáciu. Prezentačný web, tímové dokumenty a konfigurácia školského hostingu nie sú jeho súčasťou.

## Budúca implementácia

Prototyp nevyberá backendový stack. Zadávateľom potvrdené prostredie: LXC, GNU/Debian 13, PHP 8.5+ a MariaDB. Laravel/Filament boli návrhom tímu. [Analýza obrazoviek](docs/frontend-analyza.md) zostáva podkladom pre ďalšiu revíziu.
