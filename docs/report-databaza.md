# Report databáza

Dátum revízie: 1. 11. 2026. Predmet: `databaza.pdf`, 14 strán, návrh odovzdaný Adrianou podľa zápisnice 30. 10. 2026. Kontrola zahŕňa text všetkých strán, ER diagram a porovnanie s požiadavkami a existujúcim frontendovým prototypom. Nejde o test živej MariaDB ani schválenie opráv.

Model správne rozlišuje konferenciu a ročník, osobu a účet, registráciu a príspevok. Používa viacnásobné autorstvo, verzie dokumentov a históriu rozhodnutí. Pred implementáciou treba doplniť najmä obsah posudku, hodnotenú verziu, audit, rozsah rolí a pravidlá integrity medzi ročníkmi.

## Ako čítať zistenia

**Chýbajúce pokrytie/rozpor** označuje konkrétny rozdiel voči vlastnému katalógu PDF alebo požadovanému procesu. **Otvorená otázka** potrebuje rozhodnutie; neznamená, že už existuje chybná implementácia. **Vedome odložený rozsah** a **fyzický návrh** sú ďalšia etapa. Priority vyjadrujú dopad pred budúcou implementáciou, nie incident v produkcii. Všetky položky sú otvorené.

## Zistenia a odporúčané riešenia

### DB01 — Chýbajúce pokrytie

Priorita: **Vysoká**. Podklad: s. 6, RECENZIA_PRISPEVKU.

Entita eviduje pridelenie a stav, ale nie samotný posudok: hodnotenie, komentár autorovi, odporúčanie ani čas odovzdania.

Odporúčanie: Doplniť obsah posudku a pravidlá jeho uloženia/odovzdania; presné kritériá odsúhlasiť. Bez toho sa celý recenzný proces nedá uložiť.

### DB02 — Chýbajúce pokrytie

Priorita: **Vysoká**. Podklad: s. 6, VERZIA_PRISPEVKU a RECENZIA_PRISPEVKU.

Recenzia odkazuje iba na príspevok, nie na konkrétnu hodnotenú verziu; chýba rozlíšenie kôl.

Odporúčanie: Určiť väzbu pridelenia/posudku na verziu a kolo, aby revízia nezmenila význam staršej recenzie.

### DB03 — Chýbajúce pokrytie

Priorita: **Vysoká**. Podklad: s. 2 F13; s. 3–8 a 14.

Katalóg požaduje administratívny audit, ale v 16 entitách ani diagrame nie je auditný záznam.

Odporúčanie: Navrhnúť evidenciu aktéra, času, udalosti, objektu, ročníka a výsledku; určiť prístup a uchovanie.

### DB04 — Otvorený rozsah oprávnení

Priorita: **Vysoká**. Podklad: s. 4 ROLA_POUZIVATELA; s. 9.

Rola sa viaže iba na účet, bez konferencie alebo ročníka. Globálne priradenie nemusí stačiť organizátorovi či výboru konkrétneho ročníka.

Odporúčanie: Rozhodnúť globálne a ročníkové roly; doplniť rozsah priradenia. Backend musí kontrolovať aj konkrétne objekty, nie len názov roly.

### DB05 — Chýbajúce integritné pravidlo

Priorita: **Vysoká**. Podklad: s. 4–5, 7; diagram s. 14.

REGISTRACIA má ročník aj kategóriu s vlastným ročníkom; PRISPEVOK má ročník aj registráciu s ročníkom. Nie je stanovené ich zhodné priradenie.

Odporúčanie: Definovať a vynútiť zhodu ročníkov, rovnako pri zaradení príspevku do programu. Vybrať odstránenie redundancie alebo vhodné zložené väzby/validáciu.

### DB06 — Rozpor požiadavky a atribútov

Priorita: **Stredná**. Podklad: s. 1 F6; s. 5 PRISPEVOK.

F6 výslovne uvádza kľúčové slová; v zozname atribútov príspevku ani inde nie sú.

Odporúčanie: Doplniť atribút alebo samostatný normalizovaný model podľa požiadaviek na vyhľadávanie.

### DB07 — Doménový rozpor

Priorita: **Stredná**. Podklad: s. 7 BOD_PROGRAMU; s. 12 vzťah 18; s. 14.

Každý bod programu má podľa kardinality 1..N prednášajúcich, no opis zahŕňa aj prestávku.

Odporúčanie: Povoliť 0..N prednášajúcich alebo určiť podmienené pravidlo podľa typu bodu; nevytvárať fiktívneho rečníka prestávky.

### DB08 — Neurčené unikátne hodnoty

Priorita: **Vysoká**. Podklad: s. 3 OSOBA; s. 4 POUZIVATELSKY_UCET; s. 8 vzťah 3.

Nie je definovaný prihlasovací identifikátor a jeho unikátnosť/normalizácia. Koncept 0..1 účtu na osobu musí fyzická schéma tiež vynútiť.

Odporúčanie: Dohodnúť e-mail/SSO a zmeny e-mailu, prípadnú unikátnosť; pre účet vynútiť jedinečné id_osoba. Nepredpokladať, že PDF už obsahuje constraint.

### DB09 — Otvorená doménová podmienka

Priorita: **Stredná**. Podklad: s. 5 REGISTRACIA; s. 9 vzťah 7.

Nie je určené, či tá istá osoba smie mať viac registrácií na rovnaký ročník.

Odporúčanie: Uzavrieť pravidlo Q04; podľa výsledku UNIQUE(osoba, rocnik), alebo iný kľúč a pravidlá kategórií.

### DB10 — Chýbajúca špecifikácia stavov

Priorita: **Vysoká**. Podklad: s. 4–6 stav_uctu / stav.

Stavy účtu, registrácie, príspevku a recenzie nemajú úplné hodnoty, prechody, zodpovednosti ani pravidlá revízií.

Odporúčanie: Dohodnúť oddelené stavové procesy a povolené prechody; pracovné enumy z frontendu nepreberať automaticky.

### DB11 — Chýbajúce pokrytie histórie

Priorita: **Vysoká**. Podklad: s. 2 F10; s. 5 PRISPEVOK; s. 6 ROZHODNUTIE.

F10 požaduje históriu zmien stavov; aktuálny stav a história rozhodnutí nepokrývajú napríklad formálnu kontrolu alebo návrat do recenzií.

Odporúčanie: Doplniť históriu stavov alebo explicitný model udalostí pre každý relevantný prechod.

### DB12 — Neurčená platnosť rozhodnutia

Priorita: **Vysoká**. Podklad: s. 6–7 ROZHODNUTIE; s. 12 vzťah 15.

Nie je určené, ku ktorej verzii/kolu rozhodnutie patrí, kedy je konečné a kedy sprístupnené autorovi.

Odporúčanie: Oddeliť interný návrh, konečné rozhodnutie a oznámenie; definovať výber aktuálne platného rozhodnutia aj pri rovnakých časoch.

### DB13 — Neurčené obmedzenia autorstva

Priorita: **Stredná**. Podklad: s. 5–6 AUTOR_PRISPEVKU.

Zložený PK bráni duplicite tej istej osoby v príspevku, ale nie duplicite alebo neplatnej hodnote poradia. Nie je určený korešpondenčný autor.

Odporúčanie: Dohodnúť poradie a prípadnú rolu korešpondenčného autora; vynútiť kladné a jedinečné poradie v príspevku. Minimálne jedného autora riešiť pri odovzdaní.

### DB14 — Neurčená identita verzie

Priorita: **Stredná**. Podklad: s. 6 VERZIA_PRISPEVKU.

cislo_verzie nemá definovanú unikátnosť a pravidlá pri článku aj posteri; chýba nahrávajúci aktér a údaje o skutočnom type/veľkosti súboru.

Odporúčanie: Určiť číslovanie a potrebné metadáta, vrátane autora nahratia; hash/integritu podľa spôsobu úložiska. Cesta súboru sama nevynucuje bezpečný prístup.

### DB15 — Otvorený model pridelení

Priorita: **Stredná**. Podklad: s. 6 RECENZIA_PRISPEVKU.

Nie sú definované duplicity recenzenta na príspevku, odmietnutie/odobranie pridelenia, individuálna lehota alebo konflikt autor–recenzent.

Odporúčanie: Dohodnúť pridelenie, kolá, oprávnenie recenzenta a konflikt záujmov. Globálny TERMIN nemusí nahradiť individuálnu výnimku.

### DB16 — Chýbajúce pokrytie programu

Priorita: **Vysoká**. Podklad: s. 7 BOD_PROGRAMU; s. 12–13.

Model neobsahuje návrh, schválenie a publikáciu programu, schvaľujúceho aktéra ani verziu programu.

Odporúčanie: Navrhnúť stav a publikovaný snapshot/verziu tak, aby úpravy návrhu nezmenili verejný program bez publikovania.

### DB17 — Nejednoznačný čas programu

Priorita: **Stredná**. Podklad: s. 7 cas_zaciatku / cas_konca; s. 8 datum_od / datum_do.

Nie sú určené dátové typy ani pásmo. Pri viacdňovej konferencii nemusia samotné časové hodnoty identifikovať deň; miestnosť a paralelné bloky nie sú opísané.

Odporúčanie: Určiť dátum+čas a pásmo, kontroly začiatok ≤ koniec a podľa požiadaviek miestnosť/blok a kolízie. Názov cas sám nedokazuje použitie typu TIME.

### DB18 — Neurčený význam lehôt

Priorita: **Stredná**. Podklad: s. 7–8 TERMIN.

Lehota je identifikovaná voľným názvom/popisom bez stabilného typu; nie je určené, ako backend vyberie registráciu, recenziu alebo revíziu a rieši výnimku.

Odporúčanie: Dohodnúť strojový typ termínu, kontext a pravidlá platnosti; nevyhľadávať pravidlá podľa preloženého textu názvu.

### DB19 — Vedome odložený rozsah

Priorita: **Stredná**. Podklad: s. 2 rozsah; s. 5 kategória bez poplatku.

Platby, predpisy, úhrady a poplatky sú výslovne odložené, hoci zákaznícka analýza ich požaduje. Nie je to skrytá chyba PDF.

Odporúčanie: Zaznamenať fázu a zodpovednosť rozšírenia: čiastočné úhrady, výnimky, storno/refundácia, história cien a väzby na registráciu. Nemať tieto obrazovky za serverovo pokryté.

### DB20 — Rozsah na zosúladenie

Priorita: **Stredná**. Podklad: s. 2; zápisnica, správa Adriany 30. 10..

Budúce rozšírenie o ubytovanie nesmie znamenať hotelové rezervácie, ktoré zákaznícky rozsah v analýze vylučuje.

Odporúčanie: Uchovať len organizačné informácie a externý odkaz, pokiaľ zákazník výslovne nezmení rozsah.

### DB21 — Nezahrnutý neskorší modul

Priorita: **Stredná**. Podklad: porovnanie s aktuálnym editorom webu; s. 3–8.

Model zatiaľ nemá HTML/CSS obsah, médiá, koncepty a publikované verzie verejného webu. Modul bol doplnený do prototypu.

Odporúčanie: Navrhnúť serverový model editora samostatne; nezamieňať ho s VERZIA_PRISPEVKU. Je to integračný dlh novšieho modulu, nie dôkaz nesplnenia pôvodného zadania autorky.

### DB22 — Požiadavka na dopracovanie

Priorita: **Stredná**. Podklad: s. 2 aplikačná vrstva; zákaznícka analýza.

Odloženie e-mailov/exportov do aplikačnej vrstvy neurčuje, či a kde sa uložia šablóny, doručenie a opakované pokusy.

Odporúčanie: Dohodnúť perzistenciu a audit odosielania a generovania podľa rozsahu; výpadok e-mailu nesmie zrušiť platbu alebo odborné rozhodnutie.

### DB23 — Požiadavka na dopracovanie

Priorita: **Stredná**. Podklad: s. 3–8; porovnanie so zákazníckou analýzou.

SK/EN, ochrana údajov, dobrovoľné stravovacie údaje, verzia informovania a retenčné pravidlá nie sú rozpracované.

Odporúčanie: Doplniť len schválené údaje, režim prístupu a uchovania; od zadávateľa získať texty a rozhodnutia. Automaticky nepridávať vek ani plošný súhlas.

### DB24 — Fyzický návrh ešte nedodaný

Priorita: **Stredná**. Podklad: celý dokument.

Chýbajú dátové typy, dĺžky, NULL/default, CHECK/UNIQUE, indexy, kolácie, pravidlá FK pri zmazaní, SQL a migrácie. Ide o konceptuálny model, nie tvrdenie o existujúcej chybnej SQL schéme.

Odporúčanie: Pripraviť fyzickú špecifikáciu MariaDB a migrácie; navrhnúť transakcie a súbežné zmeny. Nemať tento bod za chybu konceptuálnej úrovne samotnej.

### DB25 — Otvorená politika mazania a archívu

Priorita: **Stredná**. Podklad: s. 1 opakované ročníky; s. 3–8.

Nie sú popísané archivácia/čítanie, zmazanie osoby/účtu, historické autorstvo, rozhodnutia a súbory.

Odporúčanie: Určiť retenciu a referenčné správanie. Kaskádovým zmazaním účtu nesmie bez rozhodnutia zaniknúť odborná alebo účtovná história.

### DB26 — Dokumentačný nesúlad

Priorita: **Nízka**. Podklad: s. 5 PRISPEVOK id_rocnik; s. 8–13 a s. 14.

PRISPEVOK uvádza priamy id_rocnik, ale samostatný vzťah ROCNIK–PRISPEVOK nie je medzi 21 vzťahmi ani v ER diagrame; ročník je dosiahnuteľný cez registráciu.

Odporúčanie: Buď priamu väzbu zakresliť a určiť jej integritu, alebo atribút odstrániť ako odvodený. Nepridávať 22. vzťah do prepisu zdroja bez označenia zmeny.

### DB27 — Otvorená politika účtov

Priorita: **Stredná**. Podklad: s. 3–4 OSOBA/POUZIVATELSKY_UCET.

Model umožňuje osobu bez účtu, ale nepopisuje spojenie existujúceho spoluautora s novým účtom, overenie e-mailu ani obnovu prístupu.

Odporúčanie: Dohodnúť identifikáciu a zlúčenie osoby, tokeny/SSO podľa schváleného riešenia; heslo_hash je správny základ, nie celý autentifikačný návrh.

### DB28 — Chýbajúce overenie implementácie

Priorita: **Stredná**. Podklad: celý dokument a odovzdané súbory.

PDF nedokladá vytvorenú MariaDB, migrácie, seed dáta, testy alebo nasadenie. Hotový diagram nie je hotová databáza.

Odporúčanie: Vyžiadať a verzionovať implementačné podklady pri ich dodaní; testovať FK, duplicity, ročníky, verzie a oprávnenia.

### DB29 — Čitateľnosť podkladu

Priorita: **Nízka**. Podklad: s. 14 ER diagram.

Diagram má drobné popisy, dlhé prepojenia a kríženia; pri bežnom zobrazení A4 sa horšie kontrolujú kardinality.

Odporúčanie: Dodať aj upraviteľný zdroj a väčší samostatný export, zoskupiť oblasti a doplniť legendu; význam nezmeniť bez revízie.

### DB30 — Správa dokumentácie

Priorita: **Nízka**. Podklad: s. 1–14.

PDF nemá uvedenú revíziu a schválenosť modelu, mapovanie na anglické názvy ani jednoznačné oddelenie DB F1–F13 od frontend F01–F14.

Odporúčanie: Zaviesť verziu/dátum/autorstvo, stav návrhu a slovník. Existujúce kódy nekombinovať bez prefixu DB/FE.

## Odporúčané poradie revízie

1. Pred implementáciou jadra: DB01–DB05, DB08, DB10–DB12 a DB16; zároveň zosúladiť priamy ročník DB26.
2. Pri fyzickom modeli: autori/verzie, identity, duplicity, termíny, mazanie a integrita (DB06–DB09, DB13–DB18, DB24–DB28).
3. Pri plánovaní rozšírení: financie, organizačné informácie, web editor, e-maily/exporty a ochrana údajov (DB19–DB23).
4. Pri ďalšom odovzdaní: revízia dokumentu, upraviteľný diagram a slovník názvov (DB29–DB30).

Každý uzavretý bod má dostať dátum, rozhodnutie, zodpovednosť a odkaz na implementáciu alebo revidovaný návrh. Čistý prepis dodaného modelu je v `databaza.md`; pôvodné PDF sa nemení.
