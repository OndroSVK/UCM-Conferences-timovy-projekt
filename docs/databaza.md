# Databáza a dátový model

Aktualizované 1. 11. 2026. Stav: odovzdaný konceptuálny návrh pre MariaDB, nie vytvorená alebo pripojená produkčná databáza. Autorka podkladu: Adriana Rumanovská podľa tímovej zápisnice a správy z 30. 10. 2026.

## Zdroj a záväznosť

[Originál databaza.pdf](podklady/databaza.pdf) má 14 strán: funkčný katalóg a rozsah (1–2), 16 entít s atribútmi (3–8), 21 vzťahov (8–13), ER diagram (14). PDF uchovávame bez zásahu. Názvy entít a atribútov nižšie preberáme doslovne; nie sú automaticky finálnymi názvami SQL tabuliek alebo anglického kódu.

SHA-256 zdrojového PDF: `a35ff83e9566b541cb87878dde65cb78a30eaf47a06100989938bf4eef714f5f`.

Samostatný [report databázy](report-databaza.md) odlišuje chýbajúce pokrytie požiadaviek, otázky na rozhodnutie a technické detaily ďalšej fázy. Návrh môže prechádzať zmenami, čo uvádza aj autorkina správa v [zdrojovej zápisnici](podklady/zapisnica-google-2026-11-01.txt).

## Rozsah dodaného návrhu

PDF označuje funkcie ako F1–F13. Tieto čísla sú vlastným katalógom databázového podkladu; nemajú rovnaký význam ako F01–F14 v analýze frontendu. Pokrývajú konferencie a ročníky, účty/roly, osoby, kategórie, registrácie, príspevky, autorstvo, verzie, pridelenie recenzentov, rozhodnutia, program, termíny a požadovaný audit. Samotná auditná entita zatiaľ dodaná nie je.

PDF výslovne odkladá platby, úhrady, ekonomické stavy a poplatky. Ubytovanie autorka spomína ako možné rozšírenie; zákaznícky rozsah v analýze však určuje iba informácie a externý odkaz, bez rezervácií. Manuálna evidencia platieb zostáva požiadavkou celého systému. Odklad v databázovej etape ju neruší a nepredstavuje potvrdené vyradenie zákazníkom.

E-maily, dokumenty, Excel a UI sú označené ako aplikačná vrstva. Pred ich implementáciou treba určiť aj potrebnú perzistenciu; tvrdenie o aplikačnej vrstve neznamená, že nie sú potrebné dáta o odoslaní či exportoch. Aktuálny frontend zostáva nezávislým prototypom so vzorovými údajmi.

## Entity a atribúty

| Entita | Dodané atribúty | Význam / hranica |
| --- | --- | --- |
| `KONFERENCIA` | `id_konferencia`; `nazov`; `popis` | Samostatná konferencia. |
| `ROCNIK` | `id_rocnik`; `id_konferencia`; `rok`; `miesto`; `datum_zaciatok`; `datum_koniec` | Ročník jednej konferencie. |
| `OSOBA` | `id_osoba`; `meno`; `priezvisko`; `titul_pred`; `titul_za`; `email`; `organizacia`; `pracovisko`; `krajina` | Účastník, autor, recenzent alebo člen tímu; účet nie je povinný. |
| `POUZIVATELSKY_UCET` | `id_ucet`; `id_osoba`; `heslo_hash`; `stav_uctu`; `datum_vytvorenia` | Účet patriaci jednej osobe. |
| `ROLA` | `id_rola`; `nazov`; `popis` | Číselník rolí. |
| `ROLA_POUZIVATELA` | `id_ucet + id_rola (zložený PK)` | Viac rolí jedného účtu; bez rozsahu ročníka. |
| `KATEGORIA_REGISTRACIE` | `id_kategoria`; `id_rocnik`; `nazov`; `popis` | Kategória pre konkrétny ročník; zatiaľ bez ceny. |
| `REGISTRACIA` | `id_registracia`; `id_osoba`; `id_rocnik`; `id_kategoria`; `datum_registracie`; `stav` | Účasť osoby na ročníku. |
| `PRISPEVOK` | `id_prispevok`; `id_rocnik`; `id_registracia`; `nazov`; `abstrakt`; `typ`; `stav`; `datum_odovzdania` | Príspevok v rámci registrácie, nie iba abstrakt. |
| `AUTOR_PRISPEVKU` | `id_prispevok + id_osoba (zložený PK)`; `poradie` | Usporiadané autorstvo M:N. |
| `VERZIA_PRISPEVKU` | `id_verzia`; `id_prispevok`; `cislo_verzie`; `nazov_suboru`; `cesta_suboru`; `datum_nahratia`; `typ_dokumentu` | Metadáta súboru a jeho verzie. |
| `RECENZIA_PRISPEVKU` | `id_recenzia`; `id_prispevok`; `id_recenzent`; `datum_pridelenia`; `stav` | Pridelenie recenzentovi; text hodnotenia zatiaľ chýba. |
| `ROZHODNUTIE` | `id_rozhodnutie`; `id_prispevok`; `id_ucet`; `typ_rozhodnutia`; `odovodnenie`; `datum_rozhodnutia` | História rozhodnutí výboru. |
| `BOD_PROGRAMU` | `id_program`; `id_rocnik`; `id_prispevok`; `nazov`; `typ`; `cas_zaciatku`; `cas_konca` | Bod programu; väzba na príspevok je nepovinná. |
| `PREDNASAJUCI_PROGRAMU` | `id_program + id_osoba (zložený PK)` | Prednášajúci M:N voči bodom programu. |
| `TERMIN` | `id_termin`; `id_rocnik`; `nazov`; `popis`; `datum_od`; `datum_do` | Registračné, recenzné a ďalšie lehoty ročníka. |

## Vzťahy

Kardinalita v treťom stĺpci znamená počet záznamov druhej entity pre jeden záznam prvej / počet záznamov prvej pre jeden záznam druhej. Preberá text PDF; prípadné doménové otázky sú v reporte.

| # | Prvá → druhá entita | Počty |
| --- | --- | --- |
| 1 | `KONFERENCIA` → `ROCNIK` | 0..N / 1 |
| 2 | `ROCNIK` → `KATEGORIA_REGISTRACIE` | 0..N / 1 |
| 3 | `OSOBA` → `POUZIVATELSKY_UCET` | 0..1 / 1 |
| 4 | `POUZIVATELSKY_UCET` → `ROLA_POUZIVATELA` | 0..N / 1 |
| 5 | `ROLA` → `ROLA_POUZIVATELA` | 0..N / 1 |
| 6 | `ROCNIK` → `REGISTRACIA` | 0..N / 1 |
| 7 | `OSOBA` → `REGISTRACIA` | 0..N / 1 |
| 8 | `KATEGORIA_REGISTRACIE` → `REGISTRACIA` | 0..N / 1 |
| 9 | `REGISTRACIA` → `PRISPEVOK` | 0..N / 1 |
| 10 | `PRISPEVOK` → `AUTOR_PRISPEVKU` | 1..N / 1 |
| 11 | `OSOBA` → `AUTOR_PRISPEVKU` | 0..N / 1 |
| 12 | `PRISPEVOK` → `VERZIA_PRISPEVKU` | 0..N / 1 |
| 13 | `PRISPEVOK` → `RECENZIA_PRISPEVKU` | 0..N / 1 |
| 14 | `OSOBA` → `RECENZIA_PRISPEVKU` | 0..N / 1 |
| 15 | `PRISPEVOK` → `ROZHODNUTIE` | 0..N / 1 |
| 16 | `POUZIVATELSKY_UCET` → `ROZHODNUTIE` | 0..N / 1 |
| 17 | `ROCNIK` → `BOD_PROGRAMU` | 0..N / 1 |
| 18 | `BOD_PROGRAMU` → `PREDNASAJUCI_PROGRAMU` | 1..N / 1 |
| 19 | `OSOBA` → `PREDNASAJUCI_PROGRAMU` | 0..N / 1 |
| 20 | `PRISPEVOK` → `BOD_PROGRAMU` | 0..N / 0..1 |
| 21 | `ROCNIK` → `TERMIN` | 0..N / 1 |

Autorstvo, roly a prednášajúci používajú prepojovacie entity M:N. Osoba môže existovať bez účtu; to umožňuje evidovať spoluautora alebo prednášajúceho bez samostatného prihlásenia. Registrácia môže mať viac príspevkov a príspevok viac verzií, recenzií i rozhodnutí. Povinný aspoň jeden prednášajúci pri každom bode programu treba prehodnotiť pre prestávky.

## Väzba na frontend

| Oblasť | Entity / chýbajúca časť | Dotknuté obrazovky |
| --- | --- | --- |
| Účet a roly | OSOBA, POUZIVATELSKY_UCET, ROLA, ROLA_POUZIVATELA | `auth/`, profil, pracovníci |
| Účasť | ROCNIK, KATEGORIA_REGISTRACIE, REGISTRACIA | Registrácia, účasť, organizátorské registrácie |
| Príspevky | PRISPEVOK, AUTOR_PRISPEVKU, VERZIA_PRISPEVKU | Účastnícke a organizátorské príspevky |
| Recenzia a rozhodnutie | RECENZIA_PRISPEVKU, ROZHODNUTIE; chýba obsah hodnotenia a väzba na verziu | Recenzent, výbor, priebeh recenzií |
| Program a termíny | BOD_PROGRAMU, PREDNASAJUCI_PROGRAMU, TERMIN | Verejný program, editor, schválenie a nastavenia |
| Financie | V dodanom návrhu odložené | Účasť/platba, `finance/`, kategórie/poplatky |
| Audit | Požadovaný F13, entita nedodaná | `superadmin/audit.html` |
| Verejný web a jeho editor | Dodaný model zatiaľ nemá koncepty, médiá a publikované verzie webu | `superadmin/website.html` |

IndexedDB editora webu ani sessionStorage formulárov nie sú implementáciou tohto modelu. `VERZIA_PRISPEVKU` označuje odborné súbory; verzie verejného webu sú iná doména.

## Stav a ďalší postup

Odovzdané sú analýza funkcií, dátová analýza a ER diagram. Zdroj neobsahuje SQL DDL, migrácie, seed dáta, vykonané databázové testy ani potvrdenie reálneho nasadenia. Tieto implementačné úlohy nesmú byť označené za dokončené len podľa PDF.

Pred fyzickou schémou uzavrieť report najmä pre recenzovanie, rozsah oprávnení, audit a konzistenciu ročníkov; potom dohodnúť mapovanie na anglické názvy, typy, kľúče, indexy, integritné pravidlá a migrácie. Pri každom ďalšom dodanom module doplniť jeho vlastnú podrobnú kapitolu a prepojiť ju s hlavnou projektovou dokumentáciou.
