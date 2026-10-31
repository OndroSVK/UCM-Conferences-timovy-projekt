# Používateľská príručka UCM Conferences

Verzia príručky: frontendový prototyp, 31. 10. 2026. Určené účastníkom, organizátorom, recenzentom, programovému výboru, ekonomickému oddeleniu a správcovi systému.

## 1. Čo práve používate

Toto je ukážka pripravovaného systému pre ANS 2027. Údaje a termíny sú ilustračné. Registrácia nevytvorí skutočný účet, výber súboru ho neodošle, potvrdenie platby nezabezpečí bankový prevod a komunikácia neposiela e-maily. Na skúšanie používajte vymyslené údaje a heslo určené len na túto ukážku.

Niektoré formuláre si pamätajú zmeny v jednej karte prehliadača. Iné tlačidlá iba ukážu potvrdenie. Zmena sa preto nemusí objaviť vo všetkých prehľadoch. Editor verejného webu má vlastné lokálne ukladanie konceptov a obrázkov; jeho možnosti sú v kapitole 10.

## 2. Otvorenie a orientácia

Ak máte od správcu adresu prototypu, otvorte ju v aktuálnom prehliadači. Pre lokálny náhľad spustite v priečinku klonu aplikácie:

```sh
python3 -m http.server 8766 --bind 127.0.0.1
```

Potom otvorte `http://localhost:8766/`. Podrobnosti pre vývojára sú v [README](../README.md). HTML neotvárajte dvojklikom ako lokálny súbor; interakcie vyžadujú HTTP server.

Horný prepínač rozhrania otvorí pohľad zvolenej roly. **Mapa obrazoviek** ponúkne všetky hlavné obrazovky a pomocné zoznamy. Prepínač je súčasť skúšania prototypu, nepotrebujete pridelené oprávnenie ani administrátorské heslo.

Na menšom displeji pracovného priestoru otvorte menu tlačidlom navigácie. Široké tabuľky a záložky posúvajte vodorovne vo vnútri ich plochy. Dialóg zatvorte jeho krížikom alebo klávesom Escape. Stránky možno otvoriť priamo a používať tlačidlá späť/vpred prehliadača.

## 3. Verejná konferencia

1. Na úvode si prezrite informácie o konferencii a ukážkové termíny.
2. V **Programe** prepínajte jednotlivé dni. Ide o vzorový trojdňový program.
3. V **Praktických informáciách** nájdete organizačné informácie a odkazy. Ubytovanie sa v systéme nerezervuje.
4. **Registrácia** otvorí formulár nového účastníka; **Prihlásenie** ukážku účastníckeho účtu.

Text ochrany údajov je súčasť návrhu, nie finálny dokument konferencie. Anglická verzia rozhrania zatiaľ nie je hotová.

## 4. Registrácia a prihlásenie

### Nový účastník

1. Otvorte **Registrácia** a vyplňte prvý krok.
2. Pri hesle a jeho potvrdení použite rovnakú hodnotu. Pri nezhode formulár pokračovanie zastaví.
3. Pokračujte cez tri kroky a vyplňte zobrazené údaje a kategóriu účasti. Tlačidlo návratu umožňuje údaje opraviť.
4. Skontrolujte rekapituláciu a dokončite ukážku.
5. Otvorí sa stránka **Moja účasť a platba** s ilustračnými údajmi.

Nevzniká skutočná registrácia ani záväzok zaplatiť. Zobrazené ceny a platobné inštrukcie nepoužívajte na reálnu platbu.

### Existujúci účet a obnova hesla

Formulár **Prihlásenie** otvorí vzorový účet Martina Nováka. Neoveruje skutočnú identitu. **Zabudnuté heslo** zobrazí ukážku potvrdenia a umožní prejsť na obrazovku obnovy. E-mail sa neposiela a nové heslo sa neukladá.

## 5. Účastnícky účet a príspevky

**Môj prehľad** zobrazuje ukážkovú účasť, oznámenia a príspevok. V **Profile** môžete skúšať úpravy údajov; potvrdenie sa týka lokálnej ukážky. V **Mojej účasti a platbe** sa nachádza predpis a informácie o platbe. Pri kopírovaní variabilného symbolu môže prehliadač zobraziť obmedzenie schránky; vtedy sa symbol otvorí v dialógu.

### Nový príspevok

1. Otvorte **Moje príspevky** a **Nový príspevok**.
2. Vyplňte názov, abstrakt a ďalšie zobrazené polia.
3. Tlačidlom pre pridanie spoluautora pridajte ďalší riadok.
4. Vyberte súbor, ak chcete vyskúšať ovládanie. Zobrazí sa jeho názov; skutočný upload neprebieha.
5. Uloženie konceptu potvrdí rozpracovanie v ukážke. Odovzdanie otvorí detail a prenesie názov a abstrakt do lokálneho vzorového stavu.
6. V detaile prepínajte záložky s obsahom, recenziami a históriou podľa zobrazenej navigácie.

Výber súboru sa po načítaní stránky neobnovuje. Náhľad súboru je ilustračný text, nie obsah vybraného PDF. Odovzdanie nevytvorí plnohodnotný serverový záznam a nezmení celý proces všetkých rolí.

## 6. Organizátor

V prepínači zvoľte **Organizátor**. Bočná navigácia otvorí prehľad ročníka, účastníkov, príspevky, recenzie, program, komunikáciu a exporty.

- **Účastníci:** zadajte text vyhľadávania, použite dostupný filter a otvorte detail. Pri prázdnom výsledku vymažte hľadaný text alebo upravte filter.
- **Príspevky:** otvorte detail na formálnu kontrolu. Pridelenie recenzenta otvorí ukážkový formulár recenzenta a termínu; nezaloží reálne pridelenie.
- **Priebeh recenzií:** prezrite vzorový prehľad stavov. Nesleduje skutočné odovzdávanie iných používateľov.
- **Editor programu:** prepínajte dni, otvorte úpravu bloku alebo pridanie nového. Predloženie programu výboru len simuluje potvrdenie a nemení verejný program.
- **Komunikácia:** vyberte príjemcov, vyplňte predmet a správu, otvorte náhľad a prípadne simulujte odoslanie. Správa sa nikomu neodošle.
- **Exporty a listiny:** vyberte rozsah, filtre a aspoň jeden stĺpec tam, kde sa vyberajú. Po náhľade stiahnite ukážkové CSV. Súbor obsahuje vzorové údaje; nejde o finálny Excel ani hotovú prezenčnú listinu.

## 7. Recenzent a programový výbor

**Recenzent:** otvorte **Moje recenzie**, zvoľte príspevok a vyplňte formulár hodnotenia. Koncept možno skúšobne uložiť. Odovzdanie zobrazí odporúčanie a komentár v dialógu, údaje sa neposielajú autorovi ani výboru.

**Programový výbor:** otvorte zoznam príspevkov na rozhodnutie, detail a formulár odborného rozhodnutia. Potvrdenie pripraví iba ukážkový výsledok. Na stránke programu vyskúšajte schválenie alebo vrátenie na úpravu. Verejný program zostáva vzorovou stránkou.

Recenzent navrhuje odporúčanie, výbor prijíma odborné rozhodnutie. Súčasný prototyp tento celý proces medzi rolami neukladá a nevynucuje.

## 8. Ekonomické oddelenie

1. Zvoľte **Ekonomické oddelenie** a otvorte **Platby**.
2. Vyhľadajte vzorového účastníka a otvorte detail úhrady.
3. Do formulára zadajte kladnú prijatú sumu a potvrďte ju.
4. Detail ukáže prijatú sumu, zostávajúci rozdiel a čiastočne/plne zaplatené podľa vzorového predpisu 180 €.
5. V **Ekonomickom exporte** pripravte náhľad a stiahnite CSV podľa zvolených polí.

Zadaná suma v ukážke nahrádza miestny údaj pre daný detail; nejde o pripočítanie ďalšej bankovej transakcie. Výsledok sa nemusí premietnuť do iných zoznamov. Systém neposiela peniaze ani potvrdzovací e-mail.

## 9. Správa systému

**Konferencie a ročníky** ukazujú výber ročníkov a formulár vytvorenia. Archív je ilustračný panel, historické dáta nie sú pripojené. **Nastavenia ročníka** obsahujú záložky s ukážkovými údajmi; pri prepnutí sa vyplnené hodnoty v podporovaných formulároch zachovajú lokálne.

V **Pracovníkoch a oprávneniach** možno otvoriť pridanie alebo zmenu roly pracovníka, ale nevzniká účet ani oprávnenie. **Záznam operácií** je vzorový zoznam, neslúži ako audit vykonaných kliknutí. **Web konferencie** otvorí editor opísaný nižšie.

## 10. Editor verejného webu

Editor otvorte cez **Web konferencie** v navigácii alebo priamo stránkou `superadmin/website.html`.

### Úprava obsahu

1. V **Obsahu a vzhľade** upravte názov, ročník, nadpis, text, dátum, miesto, kontakt a farbu.
2. Vyberte logo, úvodný obrázok alebo pozadie z knižnice.
3. Sledujte priebežný náhľad. Prepínače desktop/tablet/mobil používajú šírky 1024, 768 a 390 px.
4. Na menšom displeji prepínajte medzi editorom a náhľadom.
5. Stlačte **Uložiť koncept** a skontrolujte potvrdený stav uloženia.

### Vlastné HTML a CSS

Záložky **HTML** a **CSS** umožňujú úpravu šablóny. Vkladacie značky ako `{{conference.title}}` alebo `{{media.hero}}` dosadia aktuálne údaje. Používajte ponúkané značky; neznáma značka bráni publikovaniu. HTML šablóna nepodporuje spúšťanie vlastných skriptov. Vzhľad upravujte v CSS záložke.

Obnovenie predvolenej šablóny nahradí HTML a CSS konceptu, zachová textové údaje a obrázky. Pred nahradením vlastnej šablóny si jej kód podľa potreby skopírujte mimo editora. Publikované verzie zostávajú v histórii.

### Obrázky

V **Obrázkoch** vyberte súbory alebo ich pretiahnite do určenej plochy. Podporované sú JPG, PNG a WebP do 10 MB; veľké obrázky sa zmenšia na najviac 2400 px po dlhšej strane. Upravte názov a alternatívny text a priraďte obrázok do požadovanej pozície. Môžete ho vložiť aj do HTML šablóny.

Odstrániť možno iba nepoužívaný nahratý obrázok. Ak je použitý v koncepte alebo publikovanej verzii, odstránenie je zablokované. Predvolené ilustrácie sa neodstraňujú.

### Uloženie, publikovanie a návrat k verzii

1. **Uložiť koncept** zachová rozpracovaný obsah v tomto prehliadači.
2. **Publikovať ukážku** po potvrdení vytvorí novú lokálnu verziu.
3. **Publikovaný náhľad** otvorí poslednú publikovanú verziu. Ďalšie zmeny konceptu ju nemenia.
4. **História verzií** umožní obnoviť staršiu verziu do konceptu. Pri neuložených zmenách môže byť potrebné potvrdiť ich nahradenie.
5. Obnovený koncept skontrolujte a pre zmenu publikovaného náhľadu ho znovu publikujte.

Otvorenie samostatného náhľadu koncept najprv uloží. Pri odchode s neuloženou zmenou môže prehliadač vyžiadať potvrdenie. Po chybe ukladania sa nespoliehajte na uloženie: stav zostáva neuložený, kým ďalšie uloženie neuspeje.

Publikovanie je lokálne: nemení verejný `index.html`, GitHub ani stránku ostatných ľudí. Odkaz na náhľad neprenesie obsah na iné zariadenie. Editor je uložený podľa prehliadača a adresy webu; zmena portu alebo domény otvorí iné úložisko. Neexistuje používateľské tlačidlo na export celej zálohy editora. Vymazanie údajov webu zmaže aj koncepty, históriu a nahraté obrázky.

## 11. Riešenie problémov

| Problém | Postup |
| --- | --- |
| Stránka sa otvorí, tlačidlá nefungujú | Overte HTTP adresu, zapnutý JavaScript a načítanie celej aplikácie; pri lokálnom náhľade spustite server. |
| Formulár nepokračuje | Skontrolujte povinné polia, formát e-mailu a zhodu hesiel; prehliadač označí chybu. |
| Zoznam je prázdny | Vymažte vyhľadávanie a zrušte filtre. |
| Po zmene stránky chýbajú údaje | Pracujte v rovnakej karte a na rovnakej adrese; úložisko môže byť zablokované. Súbory a heslá sa neobnovujú. |
| Zmena sa neukazuje inej role | Mnohé potvrdenia sú samostatné ukážky bez spoločného backendu. |
| Náhľad webu na druhom zariadení chýba | Dáta editora sú len v pôvodnom prehliadači; odkaz ich nezdieľa. |
| Publikovaný náhľad je prázdny | Najprv v tom istom prehliadači publikujte lokálnu verziu. |
| Náhľad sa neotvorí | Povoľte nové okno pre túto stránku a skúste znova. |
| Uloženie editora zlyhalo | Skontrolujte povolenie a voľné miesto úložiska; zachovajte otvorený editor, skopírujte dôležitý text a skúste uložiť znova. Nemažte dáta webu ako prvý krok. |
| Publikovanie odmietne šablónu | Doplňte povinný obsah, neprázdne HTML a opravte neznáme značky. |
| Obrázok sa nenahrá | Overte JPG/PNG/WebP a veľkosť do 10 MB. |

Pri hlásení chyby uveďte adresu stránky, zvolenú rolu, prehliadač, kroky, očakávaný a skutočný výsledok. Pripojte snímku bez osobných údajov, ak pomôže. Konkrétna servisná adresa zatiaľ nie je stanovená; kontaktujte správcu alebo tím, ktorý vám prototyp odovzdal.

## 12. Ďalšie dokumenty

Technické súvislosti sú v [projektovej dokumentácii](projektova-dokumentacia.md), detail editora v [doplnkovom návode](editor-webu.md). Zoznam plánovaných funkcií nie je návodom na funkcie, ktoré už možno používať. Táto príručka sa aktualizuje spolu s aplikáciou v Gite.
