# Analýza frontendu a návrh obrazoviek

Stav: **pracovný návrh na revíziu, bez implementácie**.
Dátum spracovania a načítania zdroja: **31. 10. 2026**.
Projekt: UCM Conferences, prioritne konferencia ANS 2027.
Zameranie: podklad pre Ondra a spoluprácu frontendu s backendom, databázou a testovaním.

## 1. Zdroje a záväznosť návrhu

1. [Aktuálna analýza v Google Docs](https://docs.google.com/document/d/1Viv6vJhFhKZsgyNeoKLImNAMIpUWBhtpL-qs3OFqeQM/edit), načítaná textovým exportom 31. 10. 2026. Najmä časť „Odpoved“ s požiadavkami zákazníka.
2. `../../analyza-podkladov.txt` – historická analýza prezentačného webu z 25. 10. 2026. Nie je aktuálnou špecifikáciou aplikácie.
3. `../../plan-projektu.json` – pôvodný päťtýždňový plán. Neobsahuje celý neskôr spresnený rozsah; neurčuje hranice schváleného prototypu.
4. Aktuálne pokyny používateľa: oddelený priečinok aplikácie, Ondro najmä frontend; dokumentácia po slovensky a kód po anglicky; zatiaľ iba analýza na spoločnú revíziu.

Označenia v tomto dokumente:

- **Z** – požiadavka výslovne opísaná zákazníkom.
- **N** – náš návrh rozhrania alebo spôsobu realizácie požiadavky, zatiaľ neschválený.
- **O** – otvorená otázka; nedopĺňať ju do implementácie ako hotové pravidlo.

Všetky routy, členenie obrazoviek, názvy komponentov a poradie realizácie sú **N**. Zákazník opisuje potrebné funkcie, nie presný počet stránok. Jedna položka katalógu môže byť stránka, záložka alebo variant spoločnej obrazovky.

Priorita dôkazov: novšia odpoveď zákazníka > pôvodné úvahy tímu. Google dokument obsahuje obe vrstvy a nie je ešte zjednotenou špecifikáciou. Dátum načítania nie je dátum schválenia požiadaviek.

## 2. Rozsah a dôležité opravy pôvodného návrhu

- **Z:** cieľom je celý proces ANS 2027: registrácia, príspevky, odborné recenzovanie, platby a program. Ďalšie ročníky sú požadované; širšia platforma pre ďalšie konferencie nesmie ohroziť ANS.
- **Z:** kategórie rečník, autor posteru, pasívny účastník; kategórie a poplatky konfigurovateľné podľa ročníka. Individuálne oslobodenie od poplatku.
- **Z:** ubytovanie iba ako organizačná informácia a externý odkaz. Žiadny výber izieb v registrácii, rezervácie, kapacity ani hotelové platby/API.
- **Z:** neevidovať vek. Dobrovoľné údaje o stravovacích obmedzeniach a primeraná ochrana.
- **Z:** bankový prevod, manuálna evidencia platieb, automatické platobné a potvrdzovacie e-maily. Žiadna platobná brána ani bankové API.
- **Z:** recenzie, rozhodnutia výboru, revidované verzie súborov a história procesu. Nestačí tlačidlo „schváliť abstrakt“.
- **Z:** päť odborných/administrátorských rolí a samostatné rozhranie účastníka; SK/EN; evidencia dôležitých administrátorských operácií.
- **Z:** interná platforma, LXC GNU/Debian 13, PHP 8.5+, MariaDB. Laravel/Filament je zatiaľ návrh tímu; technológia frontendu nie je potvrdená.
- **O:** rozdelenie na tento a ďalší semester zákazník výslovne nepotvrdil. Tento dokument mapuje celkový rozsah, nie záväzok dodať ho za päť týždňov.

## 3. Požiadavky a ich pokrytie

Lokálne identifikátory slúžia na odkazovanie pri revízii, nie sú oficiálnym číslovaním zadávateľa.

| ID | Zdroj v odpovedi zákazníka | Požiadavka | Obrazovky |
| --- | --- | --- | --- |
| F01 | bod 1 | Kategórie, registrácia a výnimka z poplatku | A02, U02–U03, O02–O03, S02 |
| F02 | bod 2 | Organizačné informácie a odkazy na hotely | V03, S02 |
| F03 | bod 3 | Platobné inštrukcie, ručná evidencia a stavy | U03, E01–E02, O03, S02 |
| F04 | bod 4 | Päť rolí a oddelené oprávnenia | O01–O09, R01–R02, C01–C02, E01–E02, S01–S04 |
| F05 | bod 5 | Profil a účastnícky účet | A01–A02, U01–U03 |
| F06 | bod 5 | Príspevky, spoluautori, súbory a verzie | U04–U06, O04–O05 |
| F07 | body 4–5 | Recenzné konanie, hodnotenie a rozhodnutia | O05–O06, R01–R02, C01 |
| F08 | body 5–6 | Termíny a lehoty | V01, U01, U05–U06, O06, R01–R02, S02 |
| F09 | body 3, 5–6 | Systémové a hromadné e-maily | U01, U03, U06, O08 |
| F10 | bod 6 | Excel exporty a prezenčné listiny | O09, E01 |
| F11 | body 4 a 6 | Tvorba, schválenie a zverejnenie programu | V02, O07, C02 |
| F12 | body 6–7 | Ročníky, archív a možnosť ďalších konferencií | U01, S01–S02 |
| F13 | body 6–7 | SK/EN, prístupnosť a responzivita | všetky obrazovky |
| F14 | body 1 a 6 | Informovanie o údajoch, ochrana a záznam operácií | V04, A02, U02–U03, O03, S03–S04 |

F09 neznamená zákazníkom potvrdenú samostatnú internú schránku oznámení; zobrazenie upozornení v prehľade je N. PDF faktúry, exporty CSV/PDF a grafický editor e-mailov pochádzajú z pôvodného návrhu tímu; ich presný rozsah treba potvrdiť.

## 4. Používatelia, navigácia a kontext

### Navrhované rozdelenie rozhraní

| Rozhranie | Navigácia | Kontext |
| --- | --- | --- |
| Verejná konferencia | O konferencii, Program, Praktické informácie, Registrácia, Prihlásenie, SK/EN | konferencia + ročník |
| Účastník | Prehľad, Moja účasť a platba, Moje príspevky, Profil | vlastný účet + zvolený ročník |
| Organizátor | Prehľad, Účastníci, Príspevky, Recenzie, Program, Komunikácia, Exporty | pridelený ročník; rozsah pridelenia je O |
| Recenzent | Moje recenzie | vlastné pridelenia v ročníku |
| Programový výbor | Príspevky a rozhodnutia, Program na schválenie | pridelený ročník |
| Ekonomické oddelenie | Platby, Ekonomický export | rozsah konferencií/ročníkov je O |
| Superadministrátor | Konferencie a ročníky, Nastavenia, Účty a oprávnenia, Záznam operácií | celý systém; detail vybraného ročníka |

**N:** rozlišovať identitu používateľa, registráciu na ročník a odborný príspevok. Používateľský účet nesmie byť zamieňaný s jednou registráciou alebo jedným súborom.

**N:** konferenciu a ročník zobrazovať v hlavičke, breadcrumbs aj pri potvrdení dôležitých akcií. Pri zmene kontextu zrušiť neplatný výber v tabuľkách; filtre neprenášať potichu na iný ročník.

**O:** používateľ môže mať viac rolí; riešenie prepínania pracovného rozhrania a konfliktov rolí treba potvrdiť. Priama URL alebo skryté tlačidlo nie je ochrana – oprávnenia aj prístup k súborom musí vždy overiť backend.

### Matica hraníc oprávnení

| Rola | Povolený obsah podľa zákazníka | Hranice / otvorené otázky |
| --- | --- | --- |
| Účastník | vlastný profil, registrácia, platba, príspevky, autorovi určené pripomienky | cudzie údaje neprístupné; práva spoluautorov O |
| Organizátor | registrácie, organizačné údaje, príspevky, pridelenia recenzentov, priebeh recenzií, e-maily, exporty a návrh programu | potvrdenie platby iba pri poverení; konečné odborné rozhodnutie patrí výboru |
| Programový výbor | príspevky, abstrakty, výsledky a história hodnotenia, rozhodnutia, schválenie programu | prideľovanie recenzentov len poverenému členovi; automatický prístup k financiám nie je požadovaný |
| Recenzent | výlučne pridelené príspevky a vlastné recenzie | bez financií, registrácií iných účastníkov a nepridelených súborov; viditeľnosť autorov a iných posudkov O |
| Ekonomické oddelenie | údaje potrebné na kontrolu platieb, aktualizácia úhrad, ekonomické exporty | bez zásahov do odborného hodnotenia; stravovacie údaje nepotrebuje na platby |
| Superadministrátor | všetky funkcie a údaje podľa zadania | citlivé údaje nezobrazovať zbytočne v súhrnných zoznamoch; riešiť záznam prístupov a zmien podľa dohody |

## 5. Katalóg obrazoviek

Ide o **33 funkčných obrazoviek/variantov**. C01 a C02 využijú spoločné prvky O05 a O07; finančné a organizačné detaily môžu byť záložky. Neznamená to 33 rozdielnych layoutov.

V navrhnutých URL označuje `{conference}` konferenciu, `{edition}` ročník a `{submission}` príspevok. Presný formát identifikátorov je O. Verejná cesta je `/conferences/{conference}/{edition}`; pracovný kontext administrácie `/admin/editions/{edition}`. Tieto názvy neurčujú API kontrakt.

### Verejné rozhranie

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| V01 – Úvod konferencie; `/conferences/{conference}/{edition}` | Z: predstavenie, termín a miesto, dôležité lehoty, kategórie/poplatky a vstup do registrácie. Program/rečníci sú aj v pôvodnom zadaní; presný obsah dodá UCM. | Registrovať sa, prihlásiť sa, otvoriť program; N: odlíšiť otvorenú/ukončenú registráciu a archívny ročník. Nezverejnený ročník nemá byť verejne dostupný. |
| V02 – Verejný program; `.../program` | Z: zverejnený odborný program. N: deň, čas, blok, miestnosť, názov a prezentujúci podľa schváleného modelu. | N: prehľad dní/blokov; stav „program pripravujeme“. Verejne iba publikovaná verzia, nie pracovný návrh. Export programu ani verejné celé príspevky nie sú potvrdené. |
| V03 – Praktické informácie; `.../information` | Z: odporúčané hotely, kontakty, rezervačné odkazy a organizačné informácie. | Otvoriť externý rezervačný systém; bez rezervácie či platby v našom systéme. Môže byť sekciou V01 namiesto samostatnej stránky. |
| V04 – Informácie o spracúvaní údajov; `/privacy` | Z: dostupné informácie, na ktoré odkazuje registrácia. N: všeobecné pravidlá a informácie platné pre ročník, kontakt na zodpovednú osobu podľa dodaných podkladov. | Čitateľné bez účtu, SK/EN. Text, prevádzkovateľa, dobu uchovávania a právne podklady musí dodať/potvrdiť UCM; tento návrh ich neurčuje. |

### Účet a registrácia

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| A01 – Prihlásenie; `/login` | Z: bezpečné prihlásenie účastníka a oprávnených pracovníkov. N: e-mail a heslo. | Prihlásiť sa, prejsť na registráciu/obnovu prístupu. N: všeobecná chyba bez odhaľovania existencie účtu, vypršaná relácia, návrat na pôvodnú povolenú stránku. |
| A02 – Registrácia na konferenciu; `.../register` | Z: požadované osobné, pracovné, fakturačné údaje a kategória; údaje o príspevku podmienene, stravovanie dobrovoľne, potvrdenie oboznámenia sa s informáciami. Podrobný rozpis v časti 6. | N: kroky účet/profil → účasť/príspevok → rekapitulácia. Existujúci účet sa prihlási, nevytvára druhý účet. Zobraziť poplatok pred odoslaním. Úspech: registrácia uložená, platobné inštrukcie dostupné v účte; stav odoslania e-mailu riešiť oddelene. |
| A03 – Zabudnuté heslo; `/forgot-password` | N: e-mail na obnovu účtu. Nie je samostatne vyžiadanou obrazovkou zákazníka. | Odoslať požiadavku; rovnaká odpoveď pre existujúci/neexistujúci účet. Mechanizmus a e-mailové doručovanie potvrdiť s backendom. |
| A04 – Obnova hesla; `/reset-password/{token}` | N: nové heslo a jeho potvrdenie. | Uložiť; token platný/neplatný/použitý/expirovaný, návrat na A03. Nepovažovať toto za potvrdenie potreby vlastného auth riešenia; prípadné univerzitné SSO je O. |

### Účastnícke rozhranie

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| U01 – Môj prehľad; `/account` | Z: prístup k vlastnej účasti; pôvodný návrh počíta s viacerými konferenciami. N: zoznam vlastných ročníkov, najbližšie termíny, stručné stavy registrácie/platby/príspevkov a dôležité oznámenia. | Vybrať ročník, pokračovať na platbu/príspevok. N: prázdny účet a archívne účasti. Nie je to samostatná interná pošta. |
| U02 – Profil; `/account/profile` | Z: osobné a kontaktné údaje; N: globálne údaje oddeliť od údajov konkrétnej registrácie a fakturácie. | Upraviť a uložiť; zmena e-mailu/hesla a jej overenie sú O. Pred uložením vysvetliť, ktorých údajov sa zmena týka; historické fakturačné záznamy neprepisovať automaticky. |
| U03 – Moja účasť a platba; `/account/editions/{edition}` | Z: kategória, výška poplatku, celková suma, IBAN, identifikátor, splatnosť, aktuálny stav. N: uhradená/zostávajúca suma, vlastné fakturačné a dobrovoľné stravovacie údaje. | Kopírovať platobné údaje, otvoriť vlastné príspevky. O: zmena kategórie, storno používateľom, sťahovanie faktúry. Výnimka z poplatku musí mať zrozumiteľné zobrazenie bez výzvy na platbu. |
| U04 – Moje príspevky; `/account/editions/{edition}/submissions` | Z: prístup k vlastným príspevkom a ich stavom. N: názov, typ, stav a najbližšia požadovaná akcia. | Otvoriť detail, pridať príspevok podľa limitu. O: počet príspevkov na registráciu; prázdny zoznam nie je chyba, pasívnemu účastníkovi nevynucovať upload. |
| U05 – Nový/rozpracovaný príspevok; `.../submissions/new`, `.../{submission}/edit` | Z: názov, typ prednáška/poster, abstrakt, kľúčové slová, spoluautori a súbor. N: editor spoluautorov, kontrola uploadu, rekapitulácia pred odovzdaním. | N: uložiť koncept a odovzdať; koncept je návrh, nie potvrdený proces. Stav uploadu, nepovolený formát/veľkosť, termín uzavretý. O: editácia po odovzdaní, oddelené termíny abstraktu a plného textu. |
| U06 – Detail príspevku; `.../submissions/{submission}` | Z: aktuálny stav, konečné rozhodnutie, autorovi sprístupnené pripomienky, verzie a požiadavka na opravu. N: časová os s dátumami. | Stiahnuť vlastné súbory, nahrať revíziu v povolenom termíne. Nezobrazovať interné poznámky, cudzie posudky ani identitu recenzenta, pokým nie sú dohodnuté pravidlá viditeľnosti. |

### Organizátorské rozhranie

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| O01 – Prehľad ročníka; `/admin/editions/{edition}` | N odvodené zo Z: počty registrácií, príspevky na kontrolu, nevybavené recenzie, termíny a stav programu. | Odkazy na filtrované zoznamy. Finančné údaje iba podľa oprávnenia, bez citlivých stravovacích údajov v prehľade. |
| O02 – Účastníci; `.../registrations` | Z: zoznam a správa registrácií. N: meno, organizácia, kategória, stav registrácie; platba iba pri oprávnení. Vyhľadávanie, filter kategórie/stavu, stránkovanie. | Otvoriť O03, export podľa práv. N: počet vybraných záznamov a jasný rozsah hromadnej akcie; zmenou filtra nezahrnúť potichu iných účastníkov. |
| O03 – Detail registrácie; `.../registrations/{registration}` | Z: registrácia, kontaktné/fakturačné údaje, súvisiace príspevky, poplatok a výnimka. N: záložky namiesto jednej dlhej stránky. | Upraviť povolené údaje, otvoriť príspevok. Potvrdiť platbu iba poverený organizátor; pridelenie výnimky podľa dohodnutej kompetencie. Stravovacie údaje len oprávnenému pracovníkovi; O: kto ho určí. |
| O04 – Príspevky; `.../submissions` | Z: organizačný prehľad príspevkov. N: názov, typ, stav, formálna kontrola, pridelenie recenzentov a termíny. | Hľadať, filtrovať, otvoriť O05; zobrazenie autora podľa pravidiel anonymizácie. Platobný stav nesmie automaticky blokovať posúdenie bez potvrdenia zákazníka. |
| O05 – Detail a administratívna kontrola príspevku; `.../submissions/{submission}` | Z: údaje, súbory/verzie, kontrola úplnosti, pridelení recenzenti, priebeh a história. N: záložky detail/súbory/recenzie/história. | Zaznamenať formálnu kontrolu, prideliť jedného/viacerých recenzentov, nastaviť termín podľa pravidiel. Zmeny pridelenia, návrat autorovi a dôvody sú O. Organizátor nevydáva konečné odborné rozhodnutie bez roly výboru. |
| O06 – Priebeh recenzií; `.../reviews` | Z: sledovanie konania. N: príspevok, pridelený recenzent, stav a termín; filtre nepridelené/rozpracované/po termíne/dokončené podľa schválených stavov. | Otvoriť O05, prípadne zmeniť pridelenie podľa práv. Automatické upomienky a ich intervaly nie sú potvrdené. |
| O07 – Editor programu; `.../program` | Z: tvorba, úpravy a zverejnenie programu; návrh schvaľuje výbor. N: dni/bloky/položky/časy/miestnosti a väzba na prijaté príspevky. | Uložiť návrh, predložiť na schválenie, publikovať podľa práv. O: presné schvaľovanie, publikujúca rola, zmena už schváleného programu a verejné polia. Drag-and-drop nie je požadovaný. |
| O08 – Hromadné e-maily; `.../messages` | Z: komunikácia s účastníkmi. N: filter adresátov, počet príjemcov, predmet, telo a jazyk, náhľad. | Pripraviť a potvrdiť odoslanie, zobraziť výsledok doručovania podľa backendu. Rozlíšiť uloženie požiadavky od skutočného odoslania; opakovanie nesmie neúmyselne poslať duplicitné správy. Rich-text editor a šablóny sú O. |
| O09 – Exporty a prezenčné listiny; `.../exports` | Z: Excel zoznamy a prezenčné listiny. N: typ výstupu, kategórie/stavy, rozsah ročníka a prípustné stĺpce. | Náhľad počtu a rozsahu, vygenerovať/stiahnuť. CSV/PDF a presná podoba listiny sú O. Export nesmie obsahovať viac údajov, než dovoľuje rola. |

### Recenzentské rozhranie

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| R01 – Moje recenzie; `/reviewer/editions/{edition}/reviews` | Z: iba vlastné pridelené príspevky a stav recenzií. N: názov, typ, termín a stav. | Otvoriť R02; žiadne príspevky, po termíne, odobraté pridelenie. Nezobrazovať zoznam všetkých registrácií ani finančné prehľady. |
| R02 – Príspevok a hodnotiaci formulár; `.../reviews/{review}` | Z: pridelené súbory/abstrakt, hodnotiaci formulár, slovné hodnotenie a pripomienky autorovi, odporúčanie prijať/po úpravách/zamietnuť. O: kritériá, bodovanie a interné poznámky. | Stiahnuť pridelenú verziu, N: uložiť koncept, odovzdať recenziu. Jasne odlíšiť odporúčanie od rozhodnutia výboru. Po odovzdaní a pri novšej verzii príspevku správanie podľa dohodnutých pravidiel. |

### Programový výbor – varianty spoločných obrazoviek

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| C01 – Odborné rozhodnutie; `/committee/editions/{edition}/submissions/{submission}` | Z: príspevok, verzie, výsledky a história recenzií. Zoznam používa variant O04; detail variant O05. | Prijať, podmienečne prijať, vyžiadať úpravy, zamietnuť a vydať konečné rozhodnutie podľa dohodnutého procesu. O: odôvodnenie, potrebný počet posudkov, právomoc jednotlivca vs kolektívu. Rozlíšiť interný návrh rozhodnutia a rozhodnutie oznámené autorovi. |
| C02 – Schválenie programu; `/committee/editions/{edition}/program` | Z: odborný program na schválenie. Variant spoločného programu O07. | Schváliť, N: vrátiť na úpravu s poznámkou. O: zmeny po schválení a kto publikuje. Finančné údaje sem nepatria. |

### Ekonomické rozhranie

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| E01 – Platby; `/finance/editions/{edition}/payments` | Z: identifikácia platieb, stavy a ekonomické prehľady. N: účastník, identifikátor, predpis, prijatá suma, zostatok, splatnosť; filtre stav/kategória. | Otvoriť E02 a exportovať oprávnený rozsah. Bez článkov, recenzií či stravovacích obmedzení. |
| E02 – Detail úhrady; `.../payments/{payment}` | Z: údaje potrebné na kontrolu a aktualizáciu úhrad. N: predpis, prijaté úhrady, termín, stav a história zmien. | Manuálne zaznamenať/potvrdiť úhradu; čiastočné úhrady a ostatné požadované stavy. O: evidovať viac platieb, opravy, vrátenia, doklady a výnimky. Označenie „vrátené“ iba eviduje uskutočnenú operáciu, neposiela peniaze bankou. |

### Správa systému

| ID / route | Obsah a údaje | Akcie a osobitné stavy |
| --- | --- | --- |
| S01 – Konferencie a ročníky; `/admin/conferences` | Z: vytváranie a správa ročníkov, ďalšie konferencie ako rozšíriteľný rámec, archív. N: zoznam konferencií a ich ročníkov, stav publikácie a registrácie. | Vytvoriť/otvoriť ročník; archivácia podľa pravidiel uchovávania. Kopírovanie predlohy, mazanie a spôsob sprístupnenia archívu sú O. Nie je nutný verejný katalóg všetkých konferencií. |
| S02 – Nastavenia ročníka; `/admin/editions/{edition}/settings` | Z: kategórie/poplatky, dôležité termíny, informácie o konferencii a ubytovaní; N: záložky general/categories/fees/deadlines/content/payment-instructions. | Vytvoriť/upraviť kategórie, poplatky a termíny. O: kto okrem superadmina smie meniť jednotlivé záložky, meny, zmeny cien po registrácii, miesto správy IBAN a podmienky publikácie. Nahrávanie ľubovoľného HTML/CSS nie je potvrdenou požiadavkou. |
| S03 – Pracovníci a oprávnenia; `/admin/users` | Z: administrátorské účty a oprávnenia. N: meno, e-mail, roly, priradenie ku konferencii/ročníku; vytvorenie/editácia ako spoločný formulár. | Vytvoriť účet, prideliť/upraviť rolu. O: pozvánky, deaktivácia, kombinácie rolí a ich rozsah. Rozlišovať administrátorskú rolu od kategórie účasti. |
| S04 – Záznam operácií; `/admin/audit-log` | Z: evidencia dôležitých administrátorských operácií. N: oprávnený prehľad kto/kedy/čo zmenil, objekt, ročník a výsledok. | Filtrovať a prezerať detail; bez editácie záznamov. O: kto má prístup, sledované udalosti a doba uchovania. Neukladať do záznamu heslá, tokeny ani nadbytočné citlivé údaje. |

## 6. Registračný a príspevkový formulár podrobnejšie

### Registrácia A02

| Skupina | Polia / obsah | Pravidlá a otvorené otázky |
| --- | --- | --- |
| Účet | `email`, `password`; N: `passwordConfirmation` | Pri existujúcom účte preskočiť vytvorenie účtu. O: overenie e-mailu, pravidlá hesiel, SSO a obnova. |
| Identita | `firstName`, `lastName`, `titleBefore`, `titleAfter` | Zákazník požaduje polia, neurčil povinnosť každého. Vek/dátum narodenia nepridávať. |
| Pracovisko | `organization`, `department`, `country` | O: presné povinné polia, číselníky a zápis krajiny. |
| Účasť | `participantCategoryId`, zobrazený poplatok a ročník | Kategórie/poplatky čítať z konfigurácie; cenu nebrať z frontendového výpočtu ako autoritatívnu. |
| Príspevok | `submissionTitle`, `submissionType` | Podmienene pre rečníka/poster; pasívny účastník nevyplňuje. O: či sa príspevok vytvorí už pri registrácii alebo až po nej. |
| Fakturácia | `billingDetails` ako pracovná skupina | Presné polia dodá UCM. Nevyhlasovať IČO/DIČ/IČ DPH automaticky za povinné každému účastníkovi. |
| Stravovanie | N: `dietaryRequirements` | Dobrovoľné; oddelené zobrazenie a prístup. O: presný rozsah, účel, režim prístupu, uchovanie a texty dodá UCM. |
| Informovanie | `privacyNoticeAcknowledged`; N: odkaz na príslušnú verziu informácie | Potvrdenie oboznámenia nie je univerzálny súhlas so všetkým spracúvaním. Konkrétne právne/textové riešenie sa tu nenavrhuje. |
| Rekapitulácia | údaje, kategória, cena, splatnosť podľa dostupných pravidiel | Pred potvrdením umožniť opravu. Návrat z chyby nesmie zmazať už vyplnené bezpečne uchovateľné údaje; heslo neukladať do browser storage. |

Názvy sú návrh logických frontendových polí, nie potvrdené názvy databázových stĺpcov či JSON API.

### Príspevok U05 / U06

- Z: názov, typ prednáška/poster, abstrakt, kľúčové slová, spoluautori a nahraný článok/poster.
- N: spoluautorov spravovať ako opakovateľné položky; presné polia mena, pracoviska a kontaktu sú O.
- N: pri súbore ukázať názov, veľkosť, verziu, dátum, priebeh nahrávania a validáciu.
- Z: revidované verzie zachovať; oprava nie je prepísanie jediného starého súboru.
- O: limity súborov, povolené typy, šablóny, oddelený abstrakt/plný text, počet súborov a príspevkov, editácia po termíne, autor oprávnený odovzdať opravu.
- N: termín a oprávnenie kontroly backendu sú autoritatívne aj počas už otvoreného formulára. Výpadok a opakovaný submit nesmú vytvoriť neúmyselný duplikát.

## 7. Stavy a hlavné používateľské postupy

### Platby – potvrdené stavy, navrhované kódové názvy

| Zobrazenie SK | Návrh kódu |
| --- | --- |
| Čaká na úhradu | `pending` |
| Čiastočne zaplatené | `partially_paid` |
| Zaplatené | `paid` |
| Platba po splatnosti | `overdue` |
| Stornované | `cancelled` |
| Vrátené | `refunded` |

Výnimka z poplatku je potvrdená funkcia, ale zákazník neurčil, či je samostatným platobným stavom. **N:** samostatná vlastnosť `feeWaiver`, aby sa nezamieňalo „zaplatené“ a „nemá platiť“. Potvrdiť s backendom. Rovnako O: súbeh čiastočnej úhrady a omeškania; je možné, že omeškanie bude ďalší príznak.

Zoznam stavov nie je schválená matica prechodov. Spôsob evidencie súm, opráv, refundácií a storna treba dopracovať pred implementáciou E02.

### Príspevok – pracovný návrh stavov

Zákazník potvrdil proces, nie technický zoznam enumov. **N:** `draft` → `submitted` → `administrative_check` → `under_review` → rozhodovanie výboru. Výstupy: `accepted`, `revision_requested`, `conditionally_accepted`, `rejected`. Revízia sa vracia do dohodnutej fázy; nejde automaticky rovno do prijatia.

**O:** či `conditionally_accepted` a `revision_requested` znamenajú odlišné stavy, či `draft` treba a ako sa označí revidované odovzdanie. Stav recenzie (`assigned`, `draft`, `submitted`) viesť oddelene od stavu príspevku. Odporúčanie recenzenta oddeliť od rozhodnutia výboru. Stav úhrady nenahrádza stav registrácie ani odborného hodnotenia.

### Hlavné prechody obrazoviek

1. Nový účastník: V01 → A02 → potvrdenie registrácie → U03; návrat cez A01 → U01.
2. Existujúci používateľ: A01 → výber konferencie/ročníka → A02 bez zakladania účtu → U03.
3. Autor: U04 → U05 → U06 → po výzve nahrať revíziu → U06 s históriou a konečným rozhodnutím.
4. Organizátor: O04 → O05 formálna kontrola → pridelenie recenzenta → O06 sledovanie.
5. Recenzent: R01 → R02 → odovzdanie posudku; prístup iba cez vlastné platné pridelenie.
6. Výbor: zoznam príspevkov → C01 → rozhodnutie → autor dostane sprístupnené rozhodnutie/oznámenie.
7. Financie: E01 → E02 → manuálne potvrdenie → U03 aktuálny stav + automatický e-mail. Zlyhanie e-mailu nesmie zrušiť prijatú úhradu.
8. Program: O07 návrh → C02 schválenie → oprávnená publikácia → V02. Presné oprávnenia a prechody O.

## 8. Spoločné prvky a správanie frontendu

### Návrh opakovane použiteľných častí

| Skupina | Návrhy anglických názvov | Účel |
| --- | --- | --- |
| Layouty | `PublicLayout`, `AccountLayout`, `WorkspaceLayout` | tri základné kostry; administrátorské/odborné roly zdieľajú pracovný layout |
| Kontext | `ConferenceContext`, `EditionSwitcher`, `RoleNavigation`, `LanguageSwitcher` | jednoznačný ročník, pracovná rola a jazyk |
| Formuláre | `FormField`, `ValidationSummary`, `AuthorListEditor`, `FileUpload`, `UnsavedChangesPrompt` | čitateľné chyby, opakované údaje, bezpečný upload a ochrana rozpracovania |
| Prehľady | `DataTable`, `FilterBar`, `Pagination`, `EmptyState`, `StatusBadge` | zoznamy a ich stavy; finančný stav a odborný stav majú odlišné štítky |
| Detaily | `SubmissionSummary`, `SubmissionVersions`, `ReviewSummary`, `DecisionPanel`, `PaymentSummary`, `ActivityTimeline` | spoločné zobrazenie s údajmi povolenými konkrétnej role |
| Akcie | `ConfirmDialog`, `PermissionGate`, `ActionFeedback`, `ExportOptions` | potvrdenie dôsledku, spätná väzba, export; `PermissionGate` iba UX, nie bezpečnostná hranica |

Samostatné dizajnové návrhy rolí nie sú potrebné, ale obsah a povolené akcie musia byť odlišné. Filament môže prevziať časť pracovného rozhrania, ak ho tím schváli; nepredpisujeme zatiaľ vlastný admin framework.

### Stavy každej obrazovky

- Načítavanie, prázdny výsledok, žiadne dáta, chyba s možnosťou bezpečného opakovania.
- Ukladanie/odosielanie, úspech, validačná chyba pri poli aj v súhrne; žiadne dvojité odoslanie.
- Neprihlásený používateľ, chýbajúce oprávnenie, nenájdený alebo neprístupný záznam, vypršaná relácia.
- Zavretá registrácia, prekročený termín, odobraté pridelenie, archivovaný ročník podľa dohodnutých pravidiel.
- Konflikt pri súčasnej úprave: neprepísať potichu novšiu zmenu; ponúknuť obnovenie podľa backendu.
- Pri chybách zachovať kontext ročníka, filtre a rozpracované bezpečne uchovateľné údaje. Nezverejniť detaily cudzích objektov v chybových hláseniach.

### Jazyk, responzivita a prístupnosť

- Z: SK/EN používateľské rozhranie. N: texty viesť cez prekladové kľúče; dokumentáciu písať po slovensky, identifikátory a kód po anglicky. Preklady odborného obsahu a e-mailov sú O.
- N: na mobile zoznamy upraviť na čitateľné riadky/karty, filtre do zbaliteľnej časti. Recenzný formulár musí fungovať aj bez dvojstĺpcového zobrazenia.
- N: klávesnica, viditeľný fokus, popisy polí, nadpisová hierarchia, hlásenia čitateľné asistenčnými technológiami; stav neoznamovať len farbou.
- Z: vlastný moderný a konzistentný vzhľad s rešpektovaním identity UCM. Tmavý neubrutalistický štýl prezentačného webu nie je automaticky schváleným vzhľadom aplikácie.
- N: spoločný formát dátumov, časové pásmo konferencie a prehľadné zobrazenie termínu. O: autoritatívne pásmo a správanie na hranici termínu.

## 9. Navrhovaná štruktúra budúcej implementácie

Zatiaľ iba logické členenie; nižšie uvedené priečinky s kódom **neboli vytvorené**. Po schválení stacku ich prispôsobiť konvenciám frameworku, nevytvárať druhú paralelnú architektúru.

```text
aplikacia/
  README.md
  docs/                         slovenská dokumentácia
    README.md
    frontend-analyza.md
  [budúce logické oblasti frontendu]
    layouts/                    PublicLayout, AccountLayout, WorkspaceLayout
    features/
      conference/               verejný obsah a ročníky
      auth/                     prihlásenie a registrácia
      account/                  profil a vlastná účasť
      submissions/              príspevky, súbory a verzie
      reviews/                  pridelenia a posudky
      decisions/                rozhodnutia programového výboru
      payments/                 inštrukcie a ručná evidencia
      program/                  tvorba, schválenie a publikácia
      communications/           e-maily a spätná väzba
      exports/                  Excel a prezenčné listiny
      administration/           konfigurácia, účty, oprávnenia a záznam operácií
    shared/                     polia, tabuľky, filtre, stavy a potvrdenia
    locales/                    sk, en
```

Ak bude zvolený Laravel/Blade/Livewire/Filament, fyzické súbory budú v ich štandardných umiestneniach. Samostatná SPA, REST API ani konkrétna JS knižnica nie sú týmto dokumentom vybrané.

### Čo frontend potrebuje dohodnúť s backendom

- Identitu používateľa, kontext ročníka a efektívne oprávnenia, vrátane rolí na jednotlivé objekty.
- Tvar dát pre účet, registráciu, predpis/úhrady, príspevok/verziu, pridelenie/recenziu, rozhodnutie a program.
- Povolené akcie a stavové prechody vrátane dôvodov zablokovania; frontend si ich nemá odvodiť len z názvu roly.
- Kategórie, ceny, termíny, povinné polia a limity uploadov z autoritatívneho zdroja.
- Validačné chyby pri poliach, stránkovanie/filtrovanie a správanie pri súbežnej zmene či opakovaní požiadavky.
- Bezpečné sprístupnenie súborov a autorovi určených pripomienok; bežná verejná URL nesmie obísť práva.
- Stav e-mailu/exportu a spôsob aktualizácie rozhrania pri dlhšie trvajúcej operácii.

## 10. Otvorené otázky na revíziu

| ID | Otázka / rozhodnutie | Dopad |
| --- | --- | --- |
| Q01 | Čo má byť preukázateľne hotové v päťtýždňovom prototype a čo neskôr? | celý rozsah a harmonogram |
| Q02 | Laravel/Filament alebo iné riešenie? Ktoré obrazovky píše Ondro a ktoré prevezme admin framework? | fyzická štruktúra kódu, komponenty, FE/BE hranica |
| Q03 | Je prihlásenie vlastné alebo univerzitné SSO; treba overenie e-mailu? | A01–A04 a účty pracovníkov |
| Q04 | Vytvára sa účet a registrácia naraz? Môže sa používateľ registrovať na viac ročníkov a s viacerými kategóriami? | A02, U01–U03, dátové väzby |
| Q05 | Koľko príspevkov môže mať jedna registrácia? Môže byť spoluautor aj odosielateľ a vidieť ten istý príspevok? | U04–U06 a oprávnenia |
| Q06 | Aké formáty, veľkosti a počty súborov; oddelené termíny abstraktu a článku? | U05, R02, konfigurácia |
| Q07 | Anonymné hodnotenie? Viditeľnosť autorov, recenzentov, iných posudkov a interných pripomienok? | R02, C01, U06, súbory |
| Q08 | Kritériá formulára, počet recenzentov, úpravy už odovzdaného posudku a ďalšie kolá hodnotenia? | R02, O05–O06, C01 |
| Q09 | Kto smie prideliť recenzenta a vydať konečné rozhodnutie? Treba kolektívne schválenie? | C01, roly, audit |
| Q10 | Podmieňuje zaplatenie začiatok hodnotenia? | pôvodný tímový predpoklad nie je potvrdený |
| Q11 | Presné fakturačné polia, doklady/PDF faktúry, číslovanie, splatnosti, refundácie, čiastočné úhrady a výnimky? | A02, U03, E02, S02 |
| Q12 | Kto mení kategóriu/poplatok a čo sa stane s existujúcim predpisom po zmene ceny? | O03, S02, účtovná história |
| Q13 | Konkrétne práva podľa konferencie/ročníka, kombinovanie rolí a konflikt recenzent/autor? | navigácia a celý prístupový model |
| Q14 | Aké údaje programu sú verejné, kto schvaľuje/publikuje a čo znamená zmena schváleného programu? | V02, O07, C02 |
| Q15 | Stačí textový e-mail? Potrebné šablóny, jazyky, odosielateľ a viditeľnosť výsledkov doručovania? | O08 a automatické oznámenia |
| Q16 | Excel formát/stĺpce, PDF/CSV, vzhľad a rozsah prezenčných listín? | O09, E01 |
| Q17 | Kto smie vidieť stravovacie údaje; aké informácie a pravidlá uchovania dodá UCM? | A02, U03, O03, V04 |
| Q18 | Rozsah SK/EN: len UI alebo aj obsah, program a e-maily? | preklady a editory obsahu |
| Q19 | Konferenčné časové pásmo, lehoty, ich zmena a výnimky po termíne? | formuláre, recenzie a nastavovanie |
| Q20 | Archív: kto ho vidí, kedy je iba na čítanie a ako sa uchovávajú/anonymizujú údaje a záznamy operácií? | U01, S01, S04 |

## 11. Návrh poradia ďalšej práce

Nie je to schválené rozdelenie semestrov ani nový harmonogram.

1. Revidovať tento katalóg, Q01–Q04 a hranice oprávnení; potvrdiť technológiu a rozsah prototypu.
2. Navrhnúť spoločné layouty a základ vizuálu; začať cestou V01 → A02 → U01/U03.
3. Rozpracovať autorov proces U04–U06 a organizačnú kontrolu O04–O05.
4. Rozpracovať celý zvislý proces recenzia → rozhodnutie → revízia vrátane R01–R02 a C01; neodložiť ho bez dohody, je podstatou nového zadania.
5. Rozpracovať finančný proces E01–E02 a jeho odozvu v U03; konfiguráciu kategórií a cien riešiť už so základnou registráciou.
6. Doplniť program a jeho schválenie, komunikáciu, exporty, archív a záznam operácií podľa dohodnutého rozsahu. Oprávnenia, SK/EN a prístupnosť navrhovať od začiatku.

### Scenáre, ktoré má návrh umožniť overiť

- Pasívny účastník sa zaregistruje bez príspevku; rečník/poster má zodpovedajúce podmienené polia.
- Oslobodený účastník nedostane zavádzajúcu výzvu na zaplatenie.
- Recenzent neotvorí cudzí príspevok ani cez priamy odkaz; ekonomické oddelenie nemôže meniť odborné rozhodnutie.
- Autor vidí iba zverejnené pripomienky a verzie vlastného príspevku; recenzentovo odporúčanie sa nezobrazí ako konečné rozhodnutie výboru.
- Revízia zachová predchádzajúci súbor aj históriu; termín sa overí aj pri odoslaní.
- Čiastočná úhrada nezobrazí plne zaplatené; odoslanie e-mailu sa odlišuje od zaevidovania platby.
- Verejnosť vidí iba publikovaný program správneho ročníka.
- Rovnaké základné procesy sú dostupné v SK/EN, na mobile aj klávesnicou.

Tieto body sú podklady pre budúce akceptačné scenáre, nie tvrdenie o vykonaných testoch.
