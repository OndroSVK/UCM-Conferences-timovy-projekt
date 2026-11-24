# Používateľská príručka UCM Conferences

Aktualizované 16. 11. 2026. Funkčné rozhranie používa Laravel; starý HTML prototyp je samostatná návrhová ukážka.

## Funkčná aplikácia — aktuálne postupy

Správca poskytne adresu servera aplikácie. Lokálne spustenie je v [README backendu](../backend/README.md). Live Server ani GitHub Pages nie sú funkčnou aplikáciou. Údaje vo funkčnom rozhraní pochádzajú z databázy; skúšobné seed účty existujú len vo vývojovej databáze.

Hlavná adresa aplikácie otvorí návštevníkovi verejný úvod. Odkaz **Log in** vedie na prihlásenie. Informácie o konkrétnej konferencii a jej registráciu otvoríte na adrese danej konferencie.

1. **Registrácia:** na hostiteľovi svojej konferencie otvor `/register`. S JavaScriptom má formulár tri kroky: účet/osobné údaje, účasť/fakturácia a záverečné zhrnutie so súhlasom. Pred dokončením sa dá vrátiť späť. Bez JS sa zobrazia všetky polia naraz. Cena a kategórie sú podľa konferencie; registráciu vytvorí až finálne odoslanie. Chyba servera otvorí príslušný krok; heslo sa znova vyplní.
2. **Existujúci účet:** upozornenie pri e-maile ponúkne prihlásenie a návrat k registrácii tej istej konferencie. Prihlásený používateľ pridá konferenciu k svojmu účtu. Opakovaná registrácia rovnakej konferencie nevznikne.
3. **Prihlásenie:** `/login` overí účet; účastník dostane svoje konferencie, pracovník administráciu. Používateľ s oboma rolami má výber pracovného rozhrania a odkaz na jeho prepnutie. Odhlásenie ukončí session.
4. **Môj prehľad:** `/account` ukazuje len vlastné konferencie, najnovšiu prvú. Karty rozlišujú stav registrácie, platby a príspevku. Detail registrácie obsahuje skutočné platobné údaje, zostatok, faktúry, príspevok a históriu. Tlačidlo Copy kopíruje vlastný VS, ak to prehliadač podporuje.
5. **Profil:** uprav meno, tituly, organizáciu a e-mail. Zmena e-mailu vyžaduje opätovné overenie. Existujúce fakturačné údaje registrácií zostávajú zachované.
6. **Moje príspevky:** `/account/submissions` zobrazuje vlastné abstrakty/postery naprieč konferenciami, aj tie čakajúce na prvé odovzdanie. Na registráciu je jeden príspevok podľa kategórie; pasívny účastník ho nepotrebuje. Detail umožňuje názov a PDF, verzie a stiahnutie. Nová verzia je možná iba pred termínom a pred rozhodnutím výboru. Po termíne zostávajú staršie verzie dostupné na stiahnutie.
7. **Organizátor:** Conference overview ukazuje počty za aktívnu konferenciu, dôležité termíny a odkazy na zoznamy. Prepínač ponúkne iba pridelené konferencie. Vyhľadávanie, filtre a stránkovanie pracujú nad databázou.
8. **Úhrady:** poverený organizátor v detaile registrácie pridáva jednotlivé prijaté platby, dátum a poznámku. Platby sa sčítavajú. Pri prerušení odpovede najprv skontroluj históriu úhrad; opätovné odoslanie môže založiť ďalšiu úhradu. Toto nie je bankový prevod ani bankové API.
9. **Výbor:** zoznam/detail príspevkov umožňuje jednokolové rozhodnutie s odôvodnením. Výbor nevidí financie a organizátor bez práva rozhodovania nemá rozhodovací formulár. Plné recenzné konanie zatiaľ nie je implementované.
10. **Export a tím:** export stiahne skutočný XLSX/CSV aktuálnej konferencie, s filtrami stavov a stĺpcami podľa oprávnení. Superadmin spravuje Team aktívnej konferencie. Recenzent a ekonóm nie sú dostupné samostatné pracovné rozhrania.

Automatické e-maily a faktúry sa spracujú podľa serverovej fronty. Úspešné uloženie operácie neznamená úspešné doručenie e-mailu. Faktúry sú testovacie, modul môže správca vypnúť. Hromadné správy, tvorba/schvaľovanie programu, plný audit a serverový editor webu zatiaľ nie sú súčasťou funkčnej navigácie. Jazyk funkčného rozhrania je EN; texty sú pripravené v prekladových súboroch, SK preklad ešte nie je dodaný.

## Pôvodný statický prototyp — referenčný návod

Nasledujúce kapitoly platia iba pre HTML makety v koreni repozitára a GitHub Pages. Opisy simulácií ani lokálneho publikovania editora neplatia pre funkčnú Laravel aplikáciu.

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

## 13. Kde sledovať aktuálny projekt

[Prototyp na GitHub Pages](https://ondrosvk.github.io/UCM-Conferences-timovy-projekt/) je možné prezerať bez lokálneho servera. [Index dokumentácie na GitHube](https://github.com/OndroSVK/UCM-Conferences-timovy-projekt/blob/main/docs/README.md) obsahuje tri príručky, [zápisnice](zapisnice.md), [databázový návrh](databaza.md) a [report jeho otvorených otázok](report-databaza.md). Odkazy sú aj na prezentačnom webe projektu.

Databázové PDF je analytický návrh; jeho pridanie nemení spôsob ukladania údajov v prototype. Každá ďalšia dodaná časť bude postupne doplnená do dokumentácie so svojím skutočným stavom.

## 14. Serverová verzia (backend, od 11. 11. 2026)

Kapitoly 1–11 opisujú statický prototyp. Serverová verzia v priečinku `backend/` už skutočne ukladá údaje, ale zatiaľ beží iba lokálne u vývojára; verejná adresa ani e-mailový server ešte nie sú (BO11, BO14). Rozhranie je v angličtine.

**Účastník**

1. Na stránke konferencie (napr. `ans2027.<adresa>`) zvoľte *Register for the conference*. Vyplňte účet, osobné údaje, kategóriu, odvetvie a fakturačné údaje a potvrďte oboznámenie sa so spracovaním údajov.
2. Po odoslaní ste prihlásený a vidíte svoju registráciu: tri samostatné stavy (registrácia, platba, príspevok), platobné inštrukcie s vlastným variabilným symbolom a e-mail s potvrdením.
3. Ak už účet máte, systém druhý účet nevytvorí. Prihláste sa a na stránke ďalšej konferencie ju pridajte k účtu.
4. Rečník nahrá abstrakt a autor posteru poster, iba vo formáte PDF do 10 MB a do termínu. Kým výbor nerozhodne, môže nahrať novú verziu; staršie verzie zostávajú.
5. Po úplnej úhrade sa v účte objaví testovacia faktúra (označená TEST INVOICE, nie je daňovým dokladom). Po úhrade a prijatí príspevku sa účasť potvrdí automaticky.

**Prihlásenie**

Jedno okno pre všetkých na `/login`. Účastník skončí vo svojom účte, pracovník v administrácii a kto má obe roly, vyberie si. Po 3 nesprávnych heslách je prihlásenie na 5 minút zablokované (pracovná hodnota, BO2). Zabudnuté heslo obnovíte odkazom *Forgot your password?*.

**Organizátor**

Vidí iba konferencie, ku ktorým ho priradil superadministrátor, a prepína ich v ľavom paneli. V detaile registrácie organizátor s poverením na platby zaeviduje úhradu (suma, dátum, poznámka) alebo udelí oslobodenie od poplatku. Úhradu nájde podľa variabilného symbolu. Čiastočná úhrada ukáže zostatok. Preplatok sa iba zobrazí ako upozornenie; vrátenie peňazí prebieha mimo systému. Organizátor tiež potvrdí alebo zruší registráciu a stiahne export XLSX/CSV.

**Programový výbor**

V časti *Submissions* stiahne PDF a zaznamená jednokolové rozhodnutie (prijatý, podmienečne prijatý, zamietnutý) s odôvodnením pre autora. Finančné ani fakturačné údaje sa výboru nezobrazujú a jeho export ich neobsahuje.

**Superadministrátor**

Vidí všetky konferencie. V časti *Team* priradí účet ako organizátora alebo člena výboru konkrétnej konferencie a organizátorovi môže pridať poverenie na platby. Ak účet s daným e-mailom ešte neexistuje, vyplní aj meno a počiatočné heslo a účet sa vytvorí (od 15. 11. 2026). Prvého superadministrátora a konferenciu zakladá správca servera príkazmi opísanými v `backend/README.md`.
