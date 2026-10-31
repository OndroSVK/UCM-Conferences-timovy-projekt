# AI príručka pre úpravy UCM Conferences

Aktualizované 31. 10. 2026. Účel: umožniť AI aj novému vývojárovi začať konkrétnu zmenu bez opätovného skúmania celého projektu. Cesty sú relatívne ku Git koreňu aplikácie. Návod treba overiť proti aktuálnemu kódu a novším pokynom zákazníka.

## 1. Kontext na prvé načítanie

- Je to statický frontendový prototyp konferenčného systému ANS 2027, čisté HTML/CSS/ES moduly. Žiadny framework, npm build, PHP backend, API, serverová databáza ani autentifikácia tu zatiaľ nie sú.
- Git koreň je lokálne `aplikacia/`, v samostatnom klone priamo koreň repozitára `OndroSVK/UCM-Conferences-timovy-projekt`.
- Nadradený školský prezentačný web je samostatný projekt mimo tohto Git repozitára. Jeho `build.py`, `dist/` a školský upload nie sú build ani deploy aplikácie.
- Dokumentácia a používateľské texty sú po slovensky; názvy súborov kódu, premenných a kontraktov po anglicky.
- Aktuálny prototyp a jeho vzorové pravidlá nie sú schváleným backendovým kontraktom. Požiadavky/návrhy/otázky rozlišuje [analýza](frontend-analyza.md) značkami Z/N/O.
- Cieľové prostredie podľa analýzy: LXC, Debian 13, PHP 8.5+, MariaDB. Laravel/Filament bol návrh; nezavádzaj ho len preto, že je spomenutý.
- Používateľ chce tri živé dokumenty v Gite: [projektovú dokumentáciu](projektova-dokumentacia.md), [používateľskú príručku](pouzivatelska-prirucka.md) a tento návod. Zmenu správania dokumentuj v tom istom commite.

## 2. Rýchly začiatok

Najprv prečítaj `AGENTS.md`, stav pracovného stromu a oblasť požadovanej zmeny. Existujúce necommitnuté úpravy zachovaj. Bežná textová zmena nepotrebuje preštudovať celú analýzu.

```sh
git status --short
rg --files
python3 -m http.server 8766 --bind 127.0.0.1
```

Server spusti z Git koreňa; vstup je `http://localhost:8766/`. V nadradenom pracovnom priestore je vstup `/aplikacia/`. ES moduly a `fetch` šablón vyžadujú HTTP. Aplikácia funguje aj pod podcestou repozitára.

Pre orientáciu v udalostiach a DOM kontraktoch:

```sh
rg -n 'data-screen|data-form|data-action|data-tab|data-day|<template' participant organizer auth superadmin
rg -n 'case |data-form|sessionStorage|function ' app.js
rg -n 'data-web-|data-asset-|data-preview-' superadmin/website.html website-editor.js
```

Nevolaj `npm install`, nevytváraj bundler ani generátor HTML pre rutinnú úpravu. Aktuálne nie je `package.json`, projektový test runner ani CI. Neopisuj skoršie jednorazové testy ako dostupné príkazy v repozitári.

## 3. Mapa zmien

| Požiadavka zákazníka | Začni tu | Súvisiace súbory / kontrola |
| --- | --- | --- |
| Text alebo pole obrazovky | Príslušné HTML podľa `routes.js` | Predvolený obsah aj zodpovedajúci `<template>` |
| Verejný úvod prototypu | `index.html` | `styles.css`; nie `templates/conference-index.html` |
| Predvolený web v editore | `templates/conference-index.html`, `templates/conference.css` | `website-store.js`, značky v `website-document.js` |
| Správanie formulára/dialógu | `app.js` | `data-form` / `data-action` v konkrétnom HTML |
| Spoločné ikony, generované prvky, vzorové dáta | `ui.js` | Časť vzorového obsahu je aj priamo v HTML |
| Nová stránka alebo navigácia | `routes.js` a príslušné HTML | `screens`, `pagePaths`, prípadne `roles`, opakovaná navigácia |
| Responzivita a vizuál | `styles.css` | Editor má navyše `website-editor.css` |
| Editor obsahu, história, obrázky | `website-editor.js`, `superadmin/website.html` | `website-store.js`, `website-document.js` |
| Perzistencia editora | `website-store.js` | Kompatibilita IndexedDB a starých snapshotov |
| Dosadenie značiek a bezpečnosť náhľadu | `website-document.js` | Oba iframe náhľady, CSP a sandbox |
| Samostatný náhľad | `website-preview.js`, `public/website-preview.html` | `?mode=draft` / `?mode=published` |
| Požiadavky a schválenosť pravidiel | `docs/frontend-analyza.md` | F01–F14, Q01–Q20, Z/N/O |

Dve odlišné zmeny sa často zamieňajú: úprava súboru `index.html` mení statický úvod prototypu; úprava v administrátorskom editore mení len lokálny koncept IndexedDB. Súčasné publikovanie tieto svety neprepája.

## 4. Kontrakty stránok a navigácie

`routes.js` je katalóg, nie SPA router ani generátor stránok. Má 36 hlavných položiek vrátane pomocných zoznamov a S05. `public/website-preview.html` a šablónový HTML súbor sa do tohto počtu nepočítajú.

Každá bežná obrazovka používa `body[data-screen]` s kľúčom z `pagePaths`, statický `#screen-content`, príslušné spoločné štýly a import `app.js`. Zachovaj aj ID používané hlavičkou, dialógmi a mapou podľa existujúcej stránky. Pri novej stránke vychádzaj zo stránky rovnakého prostredia a odstráň nesúvisiace šablóny a aktívne navigačné stavy.

`routeHref(route)` odvodzuje adresu od adresy modulu. Používaj ho pre JS navigáciu. V HTML zachovaj relatívne odkazy; root cesty začínajúce `/` pokazia hosting pod cestou repozitára. Staré hash odkazy `#/…` sa presmerujú v `initialize()`.

Varianty DOM:

| Kontrakt | Význam |
| --- | --- |
| `register-step-0` až `register-step-2` | Kroky registrácie, riadené `registerStep` |
| `tab-0` až `tab-3`, `data-tab` | Záložky nastavení a detailu príspevku |
| `schedule-0` až `schedule-2`, `data-day` | Dni programu, výmena `#schedule-container` |
| `record-N`, query `id` | Vzorové detailové záznamy; fallback 0 |
| `data-form` | Typ formulára zachytený centrálnym submit handlerom |
| `data-action` | Akcia delegovaná cez click handler |
| `data-search`, `data-filter` | Lokálne filtrovanie tabuliek |
| `data-web-*`, `data-asset-*` | Samostatné kontrakty editora webu |

Výmena šablóny vytvorí nové DOM uzly. Zachovaj delegované udalosti alebo znovu pripoj priamu obsluhu, ak ju pridáš. Pri zmene predvoleného obsahu uprav aj jeho duplicitnú šablónu. Navigácia je opakovaná v mnohých HTML súboroch; zmena jedného sidebaru sa neprenesie automaticky na ostatné.

## 5. Stav formulárov a vzorové dáta

`app.js` používa `registration`, `savedForms`, `savedSubmission`, `paymentOverrides`. `remember()` ich serializuje do `sessionStorage['ucm-prototype-state-v1']`; flash správa je `ucm-prototype-flash`. `savedForms` má kľúč `${route}-${tab}`. Prenos je v relácii jednej karty a originu, nie cez server.

`formValues()` vynecháva heslá a súbory. Registrácia tiež filtruje heslá. Túto vlastnosť zachovaj. Nikdy neukladaj nové prihlasovacie tajomstvá do browser storage. Pri zmene schémy zvládni starý alebo neplatný JSON bez rozbitia stránky.

`ui.js` obsahuje vzorové osoby/príspevky pre niektoré akcie a exporty; zoznamy a detailové varianty sú aj v HTML. Zmena jedného zdroja preto automaticky nesynchronizuje ostatné. Lokálne odovzdanie príspevku prenáša názov a abstrakt, nezakladá celý objekt. Platobná ukážka používa predpis 180 € a prepíše sumu, nesčítava transakcie. Tieto obmedzenia nevyhlasuj za finálne pravidlá zákazníka.

Pri renderovaní vstupu používaj `textContent` alebo `esc()` z `ui.js`. HTML reťazce v dialógoch musia mať správne escapované hodnoty. Šablónový obsah dôveryhodného HTML neznamená, že možno vložiť neescapovaný vstup používateľa.

## 6. Editor webu: tok a dátový kontrakt

Tok: `superadmin/website.html` + `website-editor.js` → `website-store.js` → IndexedDB; pre náhľad `website-document.js` → sandboxovaný iframe. Samostatná stránka používa `website-preview.js` a rovnaký renderer.

Databáza `ucm-website-prototype-v1`, schéma 1, stores `editions` a `assets`, pevný kľúč ročníka `ans-2027`.

```text
edition state
  draft: { content, media, html, css, assetMetadata? }
  savedAt: ISO timestamp
  publishedId: version id alebo prázdna hodnota
  versions: [{ id, number, createdAt, snapshot }]

asset
  id, name, alt, width, height, blob
  runtime navyše url; predvolené assets majú builtIn
```

- `draft` v pamäti je oddelený od uloženého `state.draft`; `isDirty()` ich porovnáva.
- `saveDraft()` uloží kópiu a aktualizuje stav až po úspešnej transakcii.
- `publish()` validuje obsah a neznáme značky, vytvorí snapshot a nastaví `publishedId`.
- Obnova histórie klonuje snapshot do konceptu; na publikovanie treba ďalší krok.
- Samostatný konceptový náhľad sa otvára po uložení; publikovaný náhľad číta posledný `publishedId`.
- `assetMetadata` drží metadáta verzie. Zachovaj historické alternatívne texty, nielen spoločné aktuálne metadáta.
- Blob URL sú dočasné. Do HTML ukladaj `{{media:ID}}`, pri renderovaní mapuj na runtime URL a pri uvoľnení volaj `URL.revokeObjectURL`.
- Nahratie je JPG/PNG/WebP do 10 MiB, zmenšenie na 2400 px, `canvas.toBlob` žiada WebP. Nemeň obrazové dáta len kvôli zmene názvu.
- Mazanie obrázka rešpektuje použitie v koncepte aj všetkých publikovaných verziách; built-in asset sa nemaže.
- Chyba IndexedDB nesmie predstierať úspešné uloženie. Zachovaj rozpracovaný obsah a hlásenie.

Pri zmene schémy nevytváraj nové prázdne úložisko ako náhradu migrácie. Najprv urči kompatibilitu, priprav migráciu a over staré koncepty/snapshoty. Nevynucuj zmazanie lokálnych dát používateľa ako bežný spôsob aktualizácie. Aktuálne nie je vyriešená synchronizácia viacerých editorov ani serverová záloha.

## 7. Šablóny a hranice bezpečnosti

`templateValues()` mapuje `conference.*` z `draft.content`, `media.*` z pozícií, `media.heroAlt`, `media:ID` a URL registrácie/programu. Nové pole potrebuje úpravu predvolených dát, formulára a jeho hydratácie, prípadne šablóny a dokumentácie. Pre HTML sa hodnoty escapujú; CSS má samostatné dosadenie.

`unknownTokens()` kontroluje HTML aj CSS. `buildWebsiteDocument()` používa DOMParser, odstraňuje aktívny obsah/atribúty, povoľuje iba známe obrázky, nastavuje CSP a vkladá CSS. Zachovaj ošetrenie sekvencie `</style` pri serializácii CSS. Zachovaj sandbox oboch iframe bez skriptov a bez same-origin oprávnenia. Nepridávaj `allow-scripts`, `allow-same-origin` alebo voľné sieťové zdroje len na odstránenie chyby náhľadu.

Tento renderer nie je produkčný serverový sanitizér. Serverové publikovanie musí mať vlastný návrh bezpečnosti a oprávnení. Administrátorské URL prototypu sú verejné. Nesmú obsahovať skutočné údaje ani tajomstvá.

## 8. Recepty bežných zmien

### Úprava poľa alebo textu

Nájdi stránku v `pagePaths`, vyhľadaj text aj vo variantoch `<template>`, uprav popis a príslušné `name`/validácie. Ak meníš `name`, skontroluj čítanie v `app.js` a obnovu v `savedForms`. Over predvolený stav, interaktívny variant a refresh. Pri zmene správania aktualizuj používateľskú príručku.

### Nová obrazovka

Pridaj HTML rovnakého prostredia, unikátny `data-screen`, záznam v `screens` a `pagePaths`, odkazy v relevantnej navigácii. Základný obsah musí existovať aj bez JS. Over mapu, priamy vstup, relatívne assets, back/forward, jeden hlavný nadpis a unikátne ID. Počet stránok v dokumentácii uprav podľa skutočného katalógu.

### Úprava spoločného vzhľadu

Použi existujúce promenné a kompozície v `styles.css`. Editor rozlišuj cez `website-editor.css`. Over verejnú stránku, účet, pracovnú tabuľku a relevantné varianty na mobile. Neskrývaj dokumentový presah ako náhradu opravy šírky.

### Zmena predvolenej šablóny editora

Uprav `templates/` a prípadne `defaultWebsite()`. Existujúci koncept v IndexedDB sa tým neprepíše. Over čistý profil prehliadača alebo explicitnú obnovu predvolenej šablóny s vedomím používateľa; nemaž jeho aktuálne dáta. Porovnaj koncept a publikovanú verziu a over neznáme značky.

### Pripojenie backendu

Ide o väčšiu zmenu architektúry. Najprv uzavri relevantné Q otázky, identity a oprávnenia, kontrakty dát/stavov a nasadenie. Zachovaj rozdiel účtu, registrácie a príspevku, odporúčania a rozhodnutia, platby a e-mailu, návrhu a publikovaného programu. Server musí autoritatívne kontrolovať cenu, termín, práva a súbory. Nenahraď celý recenzný proces jednoduchým schválením abstraktu.

## 9. Overovanie zmien

Príkazy spúšťaj z Git koreňa. V prostredí s Node možno skontrolovať syntax modulov bez inštalácie balíkov:

```sh
for file in app.js routes.js ui.js website-store.js website-document.js website-editor.js website-preview.js; do
  node --input-type=module --check < "$file" || exit 1
done
git diff --check
```

Syntax sama nepotvrdzuje funkčnosť. Pri zmene interakcie použi reálny prehliadač. Rozsah overenia prispôsob zmene:

| Oblasť | Minimálny zmysluplný scenár |
| --- | --- |
| Navigácia | Priamy vstup, mapa, prepnutie roly, back/forward, podcesta hostingu |
| Formulár | Platný aj neplatný vstup, uloženie/návrat, refresh, heslo a súbor mimo uloženého stavu |
| Zoznam | Textový filter, kategória, žiadny výsledok, detail |
| Registrácia | Tri kroky, nezhodné heslá, späť a dokončenie |
| Platba | Odmietnutá nulová/záporná suma, čiastočná a plná úhrada |
| Export | Filtre, aspoň jeden stĺpec, náhľad a CSV |
| Editor | Úprava → uloženie → refresh → publikovanie → zmena konceptu bez zmeny publikácie → obnova histórie |
| Obrázky | Povolený aj odmietnutý súbor, zmenšenie, priradenie, reload, ochrana použitého obrázka |
| Náhľad | Koncept/publikovaný režim, neznáma značka, skript bez vykonania, zachovaný sandbox |
| Mobil | 320/360/390/430/768/1280 px, menu, otvorený dialóg, dlhý text, tabuľka a záložky |

Pri meraní presahu porovnaj `scrollWidth` a `clientWidth` dokumentu aj viditeľných detí. Vnútorný scroll tabuľky je povolený; odrezaný obsah mimo viewportu nie. Historické počty testov v `frontend-prototyp.md` neprepisuj novým tvrdením bez vlastného vykonania. Uveď, čo bolo otestované a čo zostalo neoverené; fyzické zariadenie nezamieňaj s úzkym desktopovým oknom.

## 10. Git, dokumentácia a odovzdanie zákazníkovi

Do Gitu patrí kód, lokálne assets/licencie, tri hlavné Markdown dokumenty, doplnkové podklady a `AGENTS.md`. `.gitignore` vylučuje konfiguráciu editora, `.env`, kľúče, cache, závislosti a generované balíky. IndexedDB ani sessionStorage sa pushom nezálohujú.

Pri zmene uprav podľa dopadu:

- projektovú dokumentáciu pri architektúre, dátach, rozsahu, nasadení alebo obmedzeniach;
- používateľskú príručku pri tlačidlách, postupe, ukladaní alebo chybových stavoch;
- AI príručku pri presune kódu, novom kontrakte, príkaze alebo testovacom postupe;
- README/index a doplnkový návod, ak by zostali v rozpore.

Záznam o rozhodnutí má obsahovať dátum, zdroj, stav (implementované/návrh/otvorené) a dopad. Nevymýšľaj zákaznícke schválenie. Pri odovzdaní zhrň zmenené správanie, overenie, obmedzenia a stav commitu/pushu. GitHub Pages nasadenie nepotvrdzuj len na základe pushu; v repozitári aktuálne nie je deploy workflow a nastavenie hostingu je externé.

## 11. Orientácia pri probléme

| Symptóm | Najprv skontroluj |
| --- | --- |
| Stránka má obsah, ale bez interakcií | HTTP namiesto file URL, načítanie ES modulov, konzola, `data-screen` |
| Nová stránka chýba v mape | `screens` a `pagePaths` |
| Po kliknutí sa vráti starý obsah | Duplicitný predvolený obsah a `<template>` |
| Pod GitHub podcestou chýbajú assets | Absolútne root cesty a nesprávne relatívne úrovne |
| Zmenená šablóna sa v editore neukáže | Existujúci `draft` v IndexedDB prekrýva predvolené templates |
| Publikácia vyzerá inak než koncept | `publishedId`, snapshot a `assetMetadata`; môže ísť o správne oddelenie verzií |
| Náhľad inde nič neukáže | Iný origin alebo prehliadač; lokálne dáta sa neprenášajú URL |
| Uloženie hlási chybu | IndexedDB, quota, blokované spojenie; nevymazávaj dáta bez zálohy |
| Nový údaj chýba v inom zozname | Statické HTML, `ui.js` a session stav nie sú jeden dátový model |

Na detailnejší kontext pokračuj [projektovou dokumentáciou](projektova-dokumentacia.md), [editorom](editor-webu.md) a podľa oblasti [analýzou](frontend-analyza.md). Týmto dokumentom sa riaď ako mapou súčasnej implementácie; pri rozpore má prednosť aktuálny kód pre opis správania a novšie výslovné zadanie používateľa pre požadovanú zmenu.
