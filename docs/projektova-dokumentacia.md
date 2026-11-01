# Projektová dokumentácia UCM Conferences

Stav dokumentu: dokumentácia aktuálneho frontendového prototypu a hraníc budúceho systému. Aktualizované 1. 11. 2026. Zdrojom implementačných tvrdení je kód v tomto repozitári; požiadavky vychádzajú z lokálnej [analýzy](frontend-analyza.md). Dokument nepredstavuje schválenie otvorených návrhov.

## 1. Účel a rozsah

UCM Conferences pripravuje správu vedeckej konferencie, prioritne ANS 2027: registrácie, účastnícke účty, príspevky, recenzovanie, odborné rozhodnutia, manuálnu evidenciu platieb a program. Aktuálnym výstupom je preklikateľný frontend so vzorovými údajmi. Slúži na posúdenie vzhľadu, navigácie a pracovných postupov.

Dokumentačnú sadu tvoria tri hlavné dokumenty:

- tento dokument: rozsah, architektúra, implementácia, dáta, prevádzka a rozvoj;
- [používateľská príručka](pouzivatelska-prirucka.md): postupy pre jednotlivé roly;
- [AI príručka](ai-prirucka.md): orientácia v kóde a pravidlá ďalších úprav.

Analýza, záznamy overení a detail editora sú doplnkové podklady, prepojené z [indexu dokumentácie](README.md). Hlavné dokumenty sa udržiavajú v Markdown v Gite spolu s kódom.

## 2. Hranice projektov a repozitára

Git koreň tejto aplikácie je lokálne priečinok `aplikacia/`; na GitHube ide o `OndroSVK/UCM-Conferences-timovy-projekt`. Cesty v tomto dokumente sú relatívne ku Git koreňu, pokiaľ nie je uvedené inak. Samostatný klon preto začína súbormi `index.html`, `app.js` a `README.md`, bez nadradeného priečinka `aplikacia`.

V lokálnom nadradenom pracovnom priestore existuje aj školský prezentačný web. Má vlastný `index.html`, `style.css`, `build.py`, plán `plan-projektu.json`, fotografie a tímové dokumenty DOCX/PDF. Zdrojový prezentačný web nie je súčasťou Git repozitára aplikácie; vybrané podklady a zápisnice sú do dokumentácie aplikácie kopírované samostatne. Jeho účelom je predstaviť tím a projekt, nie prevádzkovať konferenčné účty.

Prezentačný build `python3 build.py` generuje sekciu plánu medzi `PLAN:START` a `PLAN:END`, overuje odkazy a projektové PDF vrátane databázového návrhu, pripravuje `dist/` a `output/ucm-conferences-web.zip`. Balí iba prezentačný web, assets a PDF. Aplikácia ani táto Markdown dokumentácia sa týmto buildom nepublikujú. Existujúce nastavenie školského uploadu aplikáciu vylučuje; lokálne konfigurácie editora sa neverzionujú. Pri odovzdaní samotného klonu aplikácie nemožno predpokladať existenciu nadradených súborov.

## 3. Stav funkcií

| Oblasť | Aktuálne funguje v prototype | Budúci systém / chýbajúca integrácia |
| --- | --- | --- |
| Verejný web | Úvod, tri dni programu, praktické informácie, text ochrany údajov | Schválený obsah, SK/EN a serverom publikované údaje |
| Účet | Ukážka prihlásenia, trojkrokovej registrácie a obnovy hesla | Identita, autentifikácia, overenie e-mailu, bezpečné relácie |
| Účasť | Formuláre a vzorové platobné informácie | Skutočná registrácia a autoritatívny predpis |
| Príspevky | Formulár, spoluautor, výber súboru, detail a záložky | Serverový upload, verzovanie súborov a oprávnenia |
| Recenzie a výbor | Formulár recenzie a ukážka rozhodnutia | Pridelenia, lehoty, história, zverejnenie autorovi |
| Platby | Lokálna ukážka prijatej sumy a čiastočnej úhrady | Trvalá manuálna evidencia, audit a e-mail |
| Program | Prepínanie dní, dialógy úprav a potvrdenia | Ukladanie, schválenie a publikácia programu |
| Komunikácia | Náhľad správy a simulácia odoslania | Doručovanie, šablóny a stav odoslania |
| Exporty | Náhľad a stiahnutie vzorového CSV | Požadovaný Excel, finálne stĺpce a prezenčné listiny |
| Administrácia | Obrazovky ročníkov, pracovníkov, nastavení a auditu | Skutočné účty, oprávnenia, konfigurácia a audit |
| Editor webu | HTML/CSS, obsah, obrázky, IndexedDB, koncept a verzie | Serverové úložisko, zdieľanie a verejné publikovanie |

Názvy, osoby, ceny, súčty, miesta a dátumy ANS 2027 sú vzorové. Úspešné potvrdenie formulára nemusí meniť súvisiaci zoznam alebo inú rolu. Prototyp netvorí jeden úplný doménový stavový proces. Prepínač rolí je navigačná pomôcka, nie bezpečnostná hranica. EN preklad zatiaľ nie je implementovaný.

## 4. Požiadavky a otvorené rozhodnutia

Lokálna analýza označuje **Z** ako zákaznícku požiadavku, **N** ako návrh a **O** ako otvorenú otázku. Túto záväznosť zachováva aj táto dokumentácia. Zákaznícke požiadavky tu sumarizujeme podľa lokálneho záznamu; pôvodný externý dokument nebol pri tejto dokumentačnej úprave znovu načítaný.

Potvrdený cieľ zahŕňa konfigurovateľné kategórie účasti a poplatky, individuálne oslobodenie, účty a registrácie na ročníky, odborné príspevky s recenziami a revíziami, bankový prevod s manuálnou evidenciou, automatické oznámenia, program, exporty, SK/EN a záznam dôležitých operácií. Ubytovanie je len informácia s externým odkazom. Nepridávať hotelové rezervácie, platobnú bránu, bankové API ani evidenciu veku na základe domnienky.

Potvrdené cieľové prostredie podľa analýzy je LXC, GNU/Debian 13, PHP 8.5+ a MariaDB. Laravel/Filament je návrh tímu. V repozitári nie je PHP aplikácia, fyzická databázová schéma, API ani frameworkový základ. Od 1. 11. je doložený [konceptuálny databázový model](databaza.md) so zdrojovým PDF, 16 entitami a 21 vzťahmi; nejde o implementáciu MariaDB.

Pred backendom treba uzavrieť najmä identitu/SSO, rozsah rolí podľa ročníka, počty a pravidlá príspevkov, limity súborov, anonymitu a kolá recenzií, rozhodovacie právomoci, platobné a fakturačné pravidlá, publikáciu programu, rozsah prekladov a uchovávanie údajov. Úplný zoznam Q01–Q20 a pokrytie F01–F14 sú v [analýze](frontend-analyza.md). Neodvodzovať schválené pravidlá z makiet formulárov alebo z pevnej sumy v JavaScripte.

## 5. Roly a hlavné pracovné postupy

Tri základné prostredia sú verejná konferencia, účastnícky účet a pracovný priestor odborných/administrátorských rolí.

| Rola | Vstup | Zamýšľaný účel |
| --- | --- | --- |
| Verejnosť | `index.html` | Informácie, program, registrácia |
| Účastník | `participant/account.html` | Vlastný profil, účasť, platba a príspevky |
| Organizátor | `organizer/organizer.html` | Registrácie, formálna kontrola, recenzie, program, komunikácia |
| Recenzent | `reviewer/reviews.html` | Vlastné pridelené recenzie |
| Programový výbor | `committee/committee-submissions.html` | Odborné rozhodovanie a schválenie programu |
| Ekonomické oddelenie | `finance/payments.html` | Manuálna evidencia a ekonomické exporty |
| Superadministrátor | `superadmin/conferences.html` | Ročníky, konfigurácia, pracovníci, audit a editor webu |

Primárna cesta návrhu: konferencia → registrácia → účastnícky prehľad → nový príspevok → detail príspevku. Budúci odborný proces: odovzdanie → administratívna kontrola → pridelenie recenzenta → posudok → rozhodnutie výboru → prípadná revízia → konečné rozhodnutie. Recenzentovo odporúčanie sa musí odlišovať od rozhodnutia výboru.

Platobný stav, stav registrácie a stav príspevku sú samostatné údaje. Zaznamenanie platby nesmie byť zamieňané s bankovou operáciou. Návrh programu, schválenie a zverejnenie sú samostatné kroky; aktuálne dialógy nemenia verejný program.

## 6. Technická architektúra

Aplikácia používa čisté HTML, CSS a JavaScript ES moduly. Nemá balíčkovací nástroj, aplikačné závislosti ani build krok. HTTP server obsluhuje statické súbory; aplikačná logika beží v prehliadači. Editor navyše načítava predvolené šablóny cez `fetch` a pracuje s IndexedDB.

| Súbor / adresár | Zodpovednosť |
| --- | --- |
| `index.html`, `public/` | Verejné stránky; `public/website-preview.html` je pomocný výstup editora |
| `auth/`, `participant/` | Účet, registrácia, profil a príspevky |
| `organizer/`, `reviewer/`, `committee/`, `finance/`, `superadmin/` | Obrazovky pracovných rolí |
| `routes.js` | `screens`, `roles`, `pagePaths`, výpočet adries cez `routeHref` |
| `app.js` | Udalosti, formuláre, dialógy, filtre a vzorový stav |
| `ui.js` | Ikony, HTML pomocníky, escapovanie, vzorové osoby a príspevky |
| `styles.css` | Spoločný vizuál a responzívne rozloženie |
| `website-editor.js`, `website-editor.css` | Samostatné ovládanie a vzhľad editora webu |
| `website-store.js` | IndexedDB, ročník a predvolený obsah/obrázky |
| `website-document.js` | Značky, príprava a obmedzenie HTML/CSS náhľadu |
| `website-preview.js` | Samostatný koncept/publikovaný náhľad |
| `templates/` | Predvolené HTML a CSS verejného webu editora |
| `assets/` | Lokálne písmo, licencia a vektorové ilustrácie |
| `docs/`, `AGENTS.md` | Dokumentácia a vstupné pokyny pre AI |

Katalóg `routes.js` má 36 hlavných obrazoviek/zoznamov vrátane S05. Okrem nich existuje pomocná HTML stránka náhľadu a HTML šablóna v `templates/`. Nie každý HTML súbor je hlavnou obrazovkou.

Každá obrazovka má samostatnú `.html` URL a obsah v `#screen-content`. `body[data-screen]` určuje obsluhu v `app.js`. Obsah je čitateľný aj bez JavaScriptu, interakcie ho potrebujú. Spoločná navigácia je opakovaná v dokumentoch. Nie je generovaná z jedného layout súboru.

Varianty sú lokálne `<template>`: `register-step-0` až `register-step-2`, `tab-0` až `tab-3`, `schedule-0` až `schedule-2` a `record-N`. Parameter `?id=` volí vzorový záznam, nie serverový objekt; neplatná hodnota padá na variant 0. Predvolený viditeľný obsah a jeho šablónový variant treba meniť spolu. Staré `#/…` odkazy sa kompatibilne presmerujú na `.html`.

`routeHref()` odvodzuje koreň od `import.meta.url`, preto fungujú odkazy aj pod cestou repozitára. Absolútna cesta `/participant/...` by toto správanie porušila.

## 7. Dáta a ich životný cyklus

### Bežné formuláre

`app.js` uchováva `registration`, `savedForms`, `savedSubmission` a `paymentOverrides`. Pri navigácii a udalosti `pagehide` ich zapisuje ako JSON do `sessionStorage` pod `ucm-prototype-state-v1`. Jednorazové oznámenie používa `ucm-prototype-flash`.

Dáta sú viazané na reláciu karty a origin. Obnovenie stránky ich zvyčajne zachová; nejde o trvalý účet alebo zálohu a prehliadač môže obnovovať relácie podľa vlastných pravidiel. Heslá a vybrané súbory príspevkov sa do tohto stavu nezapisujú. `savedSubmission` nesie názov a abstrakt; nevytvára kompletnú databázovú entitu. Pri nedostupnom úložisku zostávajú interakcie na aktuálnej stránke, prenos medzi stránkami sa stratí.

Platobná ukážka používa pevný predpis 180 € a lokálne prepísanie prijatej sumy podľa vzorového ID. Nie je účtovným modelom ani univerzálnou konfiguráciou kategórií. Ukážkový CSV export vzniká z dát v `ui.js`, nie zo servera.

### Editor webu

IndexedDB `ucm-website-prototype-v1`, verzia schémy 1, má úložiská `editions` a `assets`. Ročník je pevný kľúč `ans-2027`. API úložiska čaká na dokončenie transakcie, nie iba na úspech jednotlivého requestu.

Záznam ročníka obsahuje koncept `draft`, čas `savedAt`, `publishedId` a pole `versions`. Publikovaná verzia má `id`, poradové `number`, `createdAt` a samostatný `snapshot`. Koncept obsahuje `content`, `media`, `html`, `css` a podľa úprav `assetMetadata`. Obrázok má `id`, `name`, `alt`, `width`, `height` a `blob`; predvolené ilustrácie používajú zdrojové URL a `builtIn`.

Editor drží rozpracované zmeny v pamäti a do konceptu ich zapisuje explicitným uložením; otvorenie samostatného konceptového náhľadu tiež najprv uloží koncept. Publikovanie uloží koncept aj snapshot a prepne `publishedId`. Obnova histórie mení rozpracovaný koncept, nezverejní ho automaticky. Verzie majú vlastné metadáta obrázkov. Dáta sa nezdieľajú medzi zariadeniami, prehliadačmi ani originmi a nezapisujú sa do Gitu. Aktuálne neexistuje používateľský export/import zálohy editora.

Obrázky: JPG/PNG/WebP, najviac 10 MiB vstup, zmenšenie na 2400 px po dlhšej strane, požiadavka na WebP pri konverzii cez canvas. Trvalou referenciou je `{{media:ID}}`, runtime náhľad používa dočasnú blob URL. Nepoužívaný nahratý obrázok možno odstrániť; použitý v koncepte alebo publikovanej verzii a predvolený obrázok sú chránené pred odstránením.

## 8. HTML/CSS šablóny a bezpečnosť náhľadu

`website-document.js` dosadzuje `conference.*`, `media.*`, `media:ID`, `registration.url` a `program.url`. Neznáma značka blokuje publikovanie. Úplný zoznam a príklady obsahuje [dokument editora](editor-webu.md).

Dokument náhľadu sa parsuje cez `DOMParser`, odstránia sa aktívne elementy a nebezpečné atribúty, obrázky sa obmedzia na knižnicu a doplní sa CSP. Iframe je sandboxovaný bez skriptov a bez prístupu k administrácii. Odkazy mimo kotiev sa otvárajú v novej karte s `noopener noreferrer`. Štýly sa vkladajú z CSS editora; vložené `<style>` z HTML sa odstraňuje. Ochrana je určená lokálnemu náhľadu. Budúce serverové publikovanie potrebuje vlastnú validáciu a ochranu.

Celá statická aplikácia je verejne čitateľná vrátane administrátorských stránok. Nesmú sa do nej vkladať skutočné osobné údaje, tajomstvá alebo heslá. Budúci backend musí kontrolovať prístup podľa identity, ročníka a konkrétneho objektu. Skryté tlačidlo alebo prepnutá rola také kontroly nenahrádza.

## 9. Vizuál a prístupnosť

Spoločný vizuál používa krémový podklad, atramentovú čiernu, modrú, limetku, levanduľovú a žltú, výrazné rámy a odsadené tiene. Písmo Space Grotesk je lokálne s licenciou v `assets/Space-Grotesk-LICENSE.txt`. Typografická značka je návrh, nie oficiálne logo univerzity.

Responzívne pravidlá sú najmä pri 1180, 800 a 450 px. Tabuľky a záložky majú vlastný priestor na vodorovný posun. Opravy rozloženia majú riešiť príčinu presahu; neskrývať celý dokument pomocou `overflow-x: hidden`. Zachovať popisy polí, viditeľný fokus, klávesnicové ovládanie dialógov a záložiek a oznámenia stavov aj textom.

## 10. Spustenie, nasadenie a obnova

V Git koreni aplikácie:

```sh
python3 -m http.server 8766 --bind 127.0.0.1
```

Otvoriť `http://localhost:8766/`. V nadradenom pracovnom priestore je aplikácia na `http://localhost:8766/aplikacia/`. Priamy `file://` režim nestačí. Vývojový Python server je len lokálny náhľad, nie cieľová produkčná služba.

Statický hosting musí zachovať adresáre a MIME typy JS/CSS/HTML. Netreba serverový rewrite ani kompiláciu. Pri publikovaní je vstupom obsah Git koreňa aplikácie, nie build školského webu. V repozitári aktuálne nie je verzionovaný automatický deploy workflow. Verejný prototyp je na [GitHub Pages](https://ondrosvk.github.io/UCM-Conferences-timovy-projekt/); 1. 11. 2026 bol overený HTTP 200. Ide o statický frontend, nie serverový backend. Konkrétne nastavenie hostingu zostáva externé.

Záloha kódu a Markdown dokumentácie je commit na Gite; vzdialená kópia vznikne až úspešným pushom. Lokálne koncepty a obrázky editora sú mimo Gitu. Vymazanie dát webu ich môže nezvratne odstrániť. Obnova staršieho commitu obnovuje kód, nie IndexedDB alebo reláciu používateľa. Pred návratom k staršiemu kódu posúdiť kompatibilitu uložených dát. Históriu editora používať na obnovu konceptu v tom istom prehliadači.

## 11. Overovanie a známe obmedzenia

Historické výsledky DOM scenárov, Safari meraní a editora sú v [zázname prototypu](frontend-prototyp.md). Ide o výsledky uvedené pri konkrétnej revízii, nie garanciu každého ďalšieho commitu. V repozitári zatiaľ nie je trvalá automatická testovacia sada ani CI; skoršie jednorazové testy boli vykonané mimo zdrojov projektu.

Pri úprave overiť minimálne zmenenú stránku a súvisiaci variant, priame URL aj cestu pod repozitárom, návrat späť/vpred a obnovenie. Pri formulári overiť úspech aj chybu a životnosť údajov. Pri rozložení zmerať dokument aj deti pri 320, 360, 390, 430, 768 a 1280 px; vnútorný posun tabuľky odlíšiť od presahu stránky. Pri editore overiť koncept, publikáciu, históriu, obrázky a chybové stavy úložiska. Presný kontrolný postup je v AI príručke.

Zatiaľ chýba fyzické mobilné overenie všetkých funkcií, produkčné oprávnenia, serverová perzistencia, doručovanie e-mailov, reálne súbory, Excel a úplný preklad. Obsah ochrany údajov je maketa; pred reálnym používaním ho musí dodať zadávateľ.

## 12. Ďalší rozvoj a správa dokumentácie

Najbližší návrhový krok je uzavrieť otvorené otázky analýzy a schváliť backendový stack a rozsah. Následne navrhnúť dátový model a väzby účtu, registrácie, príspevku, verzie, recenzie, rozhodnutia, úhrady a programu. Dodané PDF už navrhuje časť entít týchto oblastí, ale fyzické tabuľky ani finančné rozšírenie nie sú v repozitári implementované.

Každá funkčná zmena má v rovnakom commite upraviť dotknuté hlavné dokumenty. Autor zmeny doplní dátum a rozlíši implementáciu, návrh a otvorené otázky. Používateľská príručka opisuje skutočné správanie; AI príručka aktuálne umiestnenie kódu a kontrakty. Po zavedení backendu treba nahradiť prototypové postupy skutočným nasadením, migráciami, zálohou, oprávneniami a prevádzkovými kontrolami. Dokumenty s historickými overeniami zachovať ako datované záznamy.

## 13. Doplnené moduly a podklady 1. novembra 2026

### Databázový návrh

[Opis databázy](databaza.md) preberá všetky dodané entity, atribúty a vzťahy z [PDF](podklady/databaza.pdf). [Report databázy](report-databaza.md) má 30 otvorených zistení rôzneho typu: chýbajúce časti, otázky, odložený rozsah i detaily ďalšej fázy. Nie je to tvrdenie o 30 chybách bežiacej databázy. Najvyššiu prioritu má posudok a hodnotená verzia, audit, rozsah rolí, integrita ročníkov a proces schvaľovania/publikácie.

Platby a poplatky sú v databázovom návrhu vedome odložené. Naďalej zostávajú požiadavkou systému a maketou vo frontende. Rezervácie ubytovania nie sú požiadavkou; rozsah je informácia a externý odkaz. Databázový katalóg F1–F13 má vlastné číslovanie odlišné od frontend F01–F14.

### Tímový priebeh a nasadenie

[Zápisnice](zapisnice.md) prepájajú zdrojový Google dokument načítaný 1. 11. s doloženými frontendovými commitmi. Dominikove tri prípravné úlohy (prostredie Docker/Debian, nasadenie cez Git a balík na hosting) sú podľa zápisnice hotové. Ich konfigurácia ani PHP/backendové zdroje však v tomto repozitári nie sú; nejde o potvrdenú integráciu backendu. Ondrovo doriešenie hostingu zostáva rozpracované.

[Verzionovaná snímka plánu](plan-projektu.json) označuje 1. týždeň ako dokončený; v aktuálnom 2. týždni rozlišuje doložené hotové výstupy a rozpracované úlohy. Pôvodné Dominikove implementačné úlohy sa zachovali v 3. týždni. Pri ďalšej aktualizácii najprv meniť zdrojový plán prezentačného webu a potom obnoviť túto snímku.

### Priebežné odovzdávanie častí systému

Každá dodaná časť dostane vlastný opis v `docs/`, pôvodný podklad v `docs/podklady/`, stav (návrh / implementácia / overené nasadenie), mapu súborov, dáta, rozhrania, obmedzenia a spôsob overenia. Aktuálne sú zdokumentované frontend, editor webu a databázový návrh; backend/nasadzovacie podklady sa doplnia po skutočnom dodaní. Hlavná dokumentácia a obe príručky sa aktualizujú podľa dopadu.
