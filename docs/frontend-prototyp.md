# Frontend prototyp / 31. 10. 2026

Stav: implementovaný lokálny vizuálny prototyp, bez backendu. Používateľ požiadal o preklikateľné obrazovky so vzorovým obsahom, kombinujúce ANS/UCM a neobrutalizmus.

## Vizuálny smer

Krémový podklad, atramentová čierna, výrazná modrá a limetka; doplnkovo levanduľová a teplá žltá. Ostré rámy, odsadené tiene, veľká typografia Space Grotesk a vlastná vektorová vedecká ilustrácia. Tri zdieľané kompozície: verejná konferencia, účet/formuláre a pracovný priestor s bočnou navigáciou. Značka UCM Conferences je návrhová typografická značka, nie reprodukcia oficiálneho loga UCM.

Tematický referenčný podklad pre názov a vedecké oblasti: [oficiálny zborník ANS 2025](https://ans2025.ucm.sk/ANS%202025%20-%20Book%20of%20Abstracts%20-%20Final.pdf). Nejde o schválený dizajn ani finálne informácie ročníka 2027.

## Pokrytie

Všetkých 33 identifikátorov katalógu v `frontend-analyza.md` má obrazovku alebo variant v prototype: V01–V04, A01–A04, U01–U06, O01–O09, R01–R02, C01–C02, E01–E02, S01–S04. Pomocné zoznamy `committee-submissions` a `finance-export` dopĺňajú navigáciu. Celkom 35 samostatných HTML stránok.

Obsah obrazoviek je priamo v HTML dokumentoch rozdelených podľa rolí. Záložky, kroky registrácie, dni programu a vzorové záznamy detailov sú pomenované HTML šablóny v príslušnej stránke; JavaScript spracúva interakcie.

Detail účastníka, príspevku, recenzie a platby používa query parameter `id` na výber vzorového záznamu. Prepínač rozhrania a mapa sú zámernou súčasťou prototypu; finálna aplikácia ich nemusí obsahovať.

Všetky hlavné obrazovky majú vlastný obsah. Program, cenotvorba, recenzné kritériá aj termíny sú pracovné makety. SK je aktuálne spracovaný jazyk; EN preklad zatiaľ nie je implementovaný. Práva, stavové prechody a formulárové pravidlá nepredstavujú backendový kontrakt.

## Pôvodné overenie pred rozdelením do HTML

- Syntaktická kontrola JavaScript modulov cez `node --check`.
- Jednorazový DOM test v jsdom mimo zdrojového projektu: 35 rout, jeden hlavný nadpis na každej, jedinečné ID a 368 interných odkazov bez neexistujúcej cieľovej routy.
- Overené: vyhľadávanie, kategórie a prázdny výsledok; trojkroková registrácia; pridanie spoluautora a odovzdanie do detailu; záložky príspevku; tri dni programu; uchovanie úprav medzi záložkami nastavení; čiastočná úhrada; náhľad správy; náhľad exportu a navigácia cez mapu. Bez zachytených DOM runtime chýb.
- Vizuálna kontrola v natívnom Safari: verejný úvod, mapa obrazoviek, prechod do organizátorského prehľadu a jeho vykreslenie; tabuľka účastníkov a vyhľadanie konkrétneho mena s výsledkom jedného záznamu.
- Responzívne CSS obsahuje zlomy pri 1180, 800 a 450 px. Samostatná vizuálna kontrola na fyzickom mobile ani meranie overflow všetkých obrazoviek pri mobilných šírkach zatiaľ neprebehli.

Prototyp slúži na výber dizajnu a kontrolu navigácie. Žiadne reálne dáta, maily, registrácie, nahrané súbory alebo platby sa nespracúvajú. Niektoré akcie len ukážu potvrdenie. Presné možnosti sú uvedené v README aplikácie.

## Rozdelenie do HTML / 31. 10. 2026

Každá hlavná obrazovka je samostatný dokument, bez generátora a bez build kroku. Priame odkazy používajú `.html`, staré hash odkazy majú kompatibilné presmerovanie. Vzorový stav formulárov sa prenáša medzi dokumentmi cez `sessionStorage` v jednej karte; heslá a súbory sa v ňom neukladajú. Pôvodné moduly `public-pages.js`, `account-pages.js` a `workspace-pages.js` boli odstránené.

Overenie novej štruktúry:

- 35 HTML dokumentov má obsah a jeden hlavný nadpis aj bez spustenia JavaScriptu.
- Skontrolovaných 525 lokálnych odkazov vrátane variantov šablón, štýlov, písma a ikon.
- 85 DOM scenárov, 499 kontrol: všetky stránky a záznamy detailov, mapa, záložky, dni programu, registrácia vrátane nezhodných hesiel a návratu späť, uchovanie vzorového stavu medzi stránkami, vyhľadávanie, príspevok, čiastočná úhrada, správa a exporty. Kontrola adries aj pod podcestou repozitára GitHub Pages.
- Safari: vizuálne overený úvod, prechod na program a prepnutie dňa; mapa a prechod do účastníkov, vyhľadanie Petry s výsledkom jedného záznamu.
- Mobilné rozloženie bolo následne opravené a zmerané; výsledky sú uvedené nižšie.

## Mobilné rozloženie / 31. 10. 2026

Opravené automatické minimálne šírky grid/flex detí, zalamovanie tlačidiel a panelových hlavičiek, riadky so súbormi, mobilná navigácia, štatistické karty a veľkosť nadpisov. Formulárové polia na mobile používajú 16 px text a ovládacie prvky väčší dotykový cieľ. Tabuľky a záložky sa posúvajú vo vlastnom ohraničenom priestore. Presah celej stránky sa neskrýva cez `overflow-x: hidden`.

V Safari prebehlo 306 kontrol: všetkých 35 stránok, varianty záložiek, dni programu, kroky registrácie, otvorená mapa a mobilné menu, pri šírkach rámca 320, 360, 390, 430, 768 a 1280 px. Po načítaní písma sa merala šírka dokumentu aj viditeľné deti; povolené vnútorné posúvanie tabuliek a záložiek sa vyhodnocovalo oddelene. Výsledok: 0 presahov dokumentu aj obsahu. Rámec v desktopovom Safari odpočítava pri zvislom scrollbare 17 px z dostupnej šírky; kontrola porovnáva prvky so skutočnou šírkou obsahu.

Vizuálne skontrolovaný verejný úvod a organizátorský prehľad pri šírke 390 px. Ide o overenie v Safari s mobilnými rozmermi, nie test na fyzickom iPhone alebo Androide.
