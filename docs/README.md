# Dokumentácia UCM Conferences

Dokumentácia žije v Gite spolu s kódom, vo formáte Markdown. Aktualizuje sa pri každej zmene, ktorá ovplyvní správanie, architektúru alebo spôsob úprav. Dokumentácia je po slovensky, identifikátory kódu po anglicky.

## Tri hlavné dokumenty

| Dokument | Pre koho | Čo obsahuje |
| --- | --- | --- |
| [Projektová dokumentácia](projektova-dokumentacia.md) | Tím, zákazník a správca | Rozsah, požiadavky, stav implementácie, architektúra, dáta, prevádzka, obmedzenia a rozvoj |
| [Používateľská príručka](pouzivatelska-prirucka.md) | Používatelia všetkých rolí | Postupy, formuláre, editor webu, ukladanie a riešenie problémov |
| [AI príručka](ai-prirucka.md) | AI a nový vývojár | Rýchly kontext, mapa súborov, kontrakty, recepty úprav a overovanie |

[AGENTS.md](../AGENTS.md) v koreni odkazuje AI priamo na príručku. [README aplikácie](../README.md) obsahuje rýchle spustenie.

## Odovzdané časti a priebeh

| Časť | Podklad a stav |
| --- | --- |
| Frontend a editor webu | [Prototyp](frontend-prototyp.md), [editor](editor-webu.md); implementovaná lokálna ukážka |
| Databáza | [Opis modelu](databaza.md), [originálne PDF](podklady/databaza.pdf); konceptuálny návrh 16 entít a 21 vzťahov |
| Kontrola databázy | [Report databázy](report-databaza.md); 30 otvorených zistení, otázok a úloh ďalšej fázy |
| Tímový priebeh | [Zápisnice](zapisnice.md), [PDF](podklady/03-zapisnice-stretnuti.pdf), [DOCX](podklady/03-zapisnice-stretnuti.docx), [snímka plánu](plan-projektu.json), [snímka Google zdroja](podklady/zapisnica-google-2026-11-01.txt) |
| Backend a nasadenie | Príprava podľa zápisnice; zdroje backendu a nasadzovacie balíky zatiaľ nedodané do tohto repozitára |

[Prezerať prototyp na GitHub Pages](https://ondrosvk.github.io/UCM-Conferences-timovy-projekt/). Podklady a opisy ďalších častí sa pridávajú priebežne; pri každej sa uvádza dátum, pôvod a rozdiel medzi návrhom, implementáciou a overeným nasadením.

## Doplnkové podklady

- [Analýza frontendu a obrazoviek](frontend-analyza.md): požiadavky Z, návrhy N, otvorené otázky O, pokrytie F01–F14 a otázky Q01–Q20. Analytické routy a navrhované komponenty nie sú súčasnou fyzickou štruktúrou.
- [Stav a historické overenie prototypu](frontend-prototyp.md): dizajn, vývojové kroky a datované výsledky kontrol.
- [Detail editora webu](editor-webu.md): vkladacie značky, obrázky, lokálne koncepty a publikované verzie.
- [Pôvodná analýza v Google Docs](https://docs.google.com/document/d/1Viv6vJhFhKZsgyNeoKLImNAMIpUWBhtpL-qs3OFqeQM/edit): externý podklad; lokálna analýza zaznamenáva požiadavky načítané 31. 10. 2026. Pri rozpore s pôvodnými úvahami tímu má prednosť novšia odpoveď zákazníka.

## Pravidlá údržby

Autor zmeny aktualizuje dotknuté dokumenty v rovnakom commite ako kód. Uvádza dátum a rozlišuje implementované správanie, požiadavku, návrh a otvorenú otázku. Používateľskú príručku píše podľa skutočného správania; plánované funkcie neprezentuje ako hotové. Historické testy ponecháva ako datované záznamy a nevyhlasuje ich za nové overenie.

Zdrojovou dokumentáciou sú tieto Markdown súbory. Prípadný PDF/DOCX export je odvodená snímka konkrétnej revízie. Git verzionuje dokumenty a kód; lokálne dáta prehliadača tým nie sú zálohované.
