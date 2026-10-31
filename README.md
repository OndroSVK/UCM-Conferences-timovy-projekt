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

- [Verejná konferencia](http://localhost:8766/#/home)
- [Účastnícky účet](http://localhost:8766/#/account)
- [Organizátor](http://localhost:8766/#/organizer)
- [Recenzent](http://localhost:8766/#/reviews)
- [Programový výbor](http://localhost:8766/#/committee-submissions)
- [Ekonomické oddelenie](http://localhost:8766/#/payments)
- [Správa systému](http://localhost:8766/#/conferences)

Horný prepínač mení rozhranie. **Mapa obrazoviek** otvorí všetkých 33 obrazoviek z analýzy a dva pomocné zoznamy. Každá obrazovka má vlastnú hash URL, funguje späť/vpred aj obnovenie stránky bez konfigurácie servera.

## Obsah a interakcie

- Verejný úvod, trojdňový program, praktické informácie a textová maketa ochrany údajov.
- Prihlásenie, tri registračné kroky a obrazovky obnovy hesla.
- Účastnícky prehľad, profil, účasť a platba, príspevky, editor a detail so záložkami.
- Organizátorský prehľad, účastníci a detaily, príspevky a kontrola, recenzie, editor programu, komunikácia a exporty.
- Recenzentský formulár, rozhodnutie výboru, schvaľovanie programu.
- Platby a detail úhrady; konferencie/ročníky, nastavenia, pracovníci a záznam operácií.
- Vyhľadávanie a filtre tabuliek, prepínanie dní, dialógy, pridanie spoluautora, výber lokálneho súboru, náhľad správy a stiahnutie ukážkového CSV.

Všetky mená, počty, ceny, termíny a program ročníka 2027 sú vzorové. Prototyp nie je potvrdením finálneho rozsahu ani pravidiel konferencie. Nie je zapojený backend, autentifikácia, databáza, e-mail, bankové služby ani upload. Roly sú navigácia, nie zabezpečenie. Niektoré akcie iba zobrazia dialóg alebo potvrdenie; netvoria úplný stavový proces. Vybrané formuláre si pamätajú zmeny len v pamäti otvorenej stránky; po obnovení sa resetujú. Heslá sa neukladajú.

CSV je iba lokálna ukážka; finálny Excel export zatiaľ nie je implementovaný. PDF náhľad zobrazuje ilustračný text, nie skutočný dokument.

## Súbory

- `index.html` – vstup a dialógy.
- `styles.css` – vizuál, layouty a responzívne pravidlá.
- `app.js` – routy, navigácia, mapa obrazoviek a lokálne interakcie.
- `ui.js` – spoločné prvky, ikony a vzorové dáta.
- `public-pages.js`, `account-pages.js`, `workspace-pages.js` – jednotlivé obrazovky.
- `docs/frontend-prototyp.md` – stav, dizajn a overenie prototypu.

Aplikácia je samostatná: písmo a jeho licencia sú v `assets/`, favicon v `favicon.ico`. Repozitár obsahuje iba aplikáciu a jej dokumentáciu. Prezentačný web, tímové dokumenty a konfigurácia školského hostingu nie sú jeho súčasťou.

## Budúca implementácia

Prototyp nevyberá backendový stack. Zadávateľom potvrdené prostredie: LXC, GNU/Debian 13, PHP 8.5+ a MariaDB. Laravel/Filament boli návrhom tímu. [Analýza obrazoviek](docs/frontend-analyza.md) zostáva podkladom pre ďalšiu revíziu.
