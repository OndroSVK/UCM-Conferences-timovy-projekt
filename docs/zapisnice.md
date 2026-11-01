# Zápisnice a priebeh projektu UCM Conferences

Aktualizované 1. 11. 2026. Zdroj: [tímová zápisnica v Google Docs](https://docs.google.com/document/d/1UGouF6UbpzQYj0o3fd-yql3aJioUEau_ns_nu8vUubs/edit), načítaná 1. 11. 2026; doplnené realizované práce podľa Git histórie a tohto pracovného záznamu. Originálny Google dokument týmto exportom nemeníme. [Snímka zdroja](podklady/zapisnica-google-2026-11-01.txt).

## Stretnutie 24. októbra 2026

Miesto: Trnava. Úvodná debata o projekte, spoločné fotenie, plán ďalšieho stretnutia a individuálna analýza zadania.

## Stretnutie 25. októbra 2026

Forma: online. Rozdelenie pozícií a úloh, spisovanie otázok pre zákazníka, osobných profilov a špecifikácie.

Lukáš: analýza zadania a návrh technologického stacku. Ondro: logo, prezentačný web, zhromažďovanie podkladov a spoločné tímové dokumenty. Všetci: profily, špecifikácia a kontrola výstupov.

Večer Ondro vytvoril web v odsúhlasenom dizajne a nahral dokumenty z interných tímových podkladov. Podľa zdrojovej zápisnice tím web a dokumenty skontroloval a prijal bez výhrad. Ďalšie stretnutie bolo naplánované na 31. októbra.

## Aktivita 26. októbra 2026

Lukáš poslal otázky na zákazníka. Odpoveď zákazníka bola následne doplnená do analýzy; presný dátum odpovede nie je v zápisnici uvedený.

## Stretnutie 28. októbra 2026

Miesto: študovňa, počas obedajšej prestávky. Zápis potvrdzuje dokončenie všetkých úloh predchádzajúceho týždňa.

Aktualizovaný záznam uvádza vytvorenie obrazoviek, založenie GitHub repozitára a sprístupnenie prototypu na GitHub Pages. Konkrétne frontendové commity sú z 31. októbra; nejde o dôkaz, že všetky tieto práce vznikli už 28. októbra.

Doriešenie hostingu aplikácie má Ondro naďalej rozpracované. Verejný statický prototyp na GitHub Pages sa odlišuje od budúceho hostingu backendu.

## Odovzdanie databázového návrhu 30. októbra 2026

Adriana o 19:47 odovzdala tri analytické výstupy v jednom PDF: katalóg funkčných požiadaviek, dátovú analýzu a entitno-relačný diagram. Upozornila, že atribúty a diagram môžu pri implementácii prechádzať zmenami.

Podklad obsahuje 16 entít a 21 textovo opísaných vzťahov. Platby a poplatky odkladá na neskoršie rozšírenie; nejde o zrušenie požiadavky celého systému. Ubytovanie v zákazníckom rozsahu zostáva organizačnou informáciou a externým odkazom bez rezervácií.

## Dominikove dokončené úlohy podľa zápisnice

Príprava prostredia Docker na Debiane a kontrola kompatibility PHP, JavaScriptu, HTML a CSS.

Príprava nasadenia cez Git bez ručného kopírovania zdrojov.

Príprava balíka projektu na hosting. Zápisnica tieto tri úlohy uvádza ako splnené; príslušný balík a konfigurácia zatiaľ nie sú vo frontendovom repozitári. Produkčné nasadenie nimi nie je doložené.

## Realizované frontendové práce 31. októbra 2026

Ondro: samostatný frontendový repozitár, verejné stránky, účastnícky účet a pracovné obrazovky všetkých rolí; rozdelenie do samostatných HTML dokumentov a kompatibilita starších odkazov.

Doplnené lokálne formulárové interakcie, filtre, dialógy, ukážkový CSV export a uchovanie bezpečných vzorových údajov v relácii karty. Opravené mobilné rozloženie; historické merania a limity sú v dokumentácii prototypu.

Implementovaná frontendová ukážka editora HTML/CSS a obsahu, knižnice obrázkov, responzívneho náhľadu, konceptov a verzií v IndexedDB.

Zavedená projektová dokumentácia, používateľská a AI príručka v Gite a vstupné pokyny AGENTS.md. Námet na používateľskú/AI príručku a HTML/CSS editor priniesol podľa zápisnice Lukáš. Backend a serverové oprávnenia v prototype nie sú pripojené.

## Aktualizácia podkladov 1. novembra 2026

Ide o doplnenie pracovného záznamu z odovzdaných podkladov a Git histórie, nie o tvrdenie o ďalšom spoločnom stretnutí.

Databázový PDF podklad bol pridaný do Git dokumentácie spolu s opisom entít, vzťahov a väzby na frontend. Samostatný report databázy eviduje otvorené zistenia a odporúčania; pôvodné PDF zostáva nezmenené.

Plán označuje všetky úlohy 1. projektového týždňa ako splnené. V 2. týždni sú dokončené doložené frontendové výstupy a tri Dominikove prípravné úlohy; ostatné úlohy sú rozpracované. Hosting zostáva rozpracovaný.

Pôvodné Dominikove implementačné úlohy migrácií, ukladania príspevkov a integritných obmedzení sa presúvajú do 3. týždňa; ich hotovosť nie je doložená prípravou balíka ani konceptuálnym PDF.

Prezentačný web dostáva odkazy na živý frontendový prototyp a dokumentáciu na GitHube. Zápisnice a databázový podklad sú dostupné aj z jeho dokumentovej sekcie.

## Otvorené nadväzujúce práce

Doriešiť hosting backendu, dodať jeho zdroje a nasadzovacie podklady do dohodnutého repozitára. Odovzdané moduly postupne dopĺňať do projektovej a AI dokumentácie.

Pred fyzickou databázovou schémou uzavrieť pripomienky reportu, najmä obsah posudku, hodnotenú verziu, audit, rozsah rolí, zhodu ročníkov a stavy programu.

Finančný model zostáva budúcim rozšírením databázy; používateľská príručka zatiaľ opisuje skutočné možnosti lokálneho prototypu.

## Prepojené podklady

- [Plán úloh](plan-projektu.json)
- [Databázový model](databaza.md)
- [Report databázy](report-databaza.md)
- [Dokumentácia prototypu a historické overenia](frontend-prototyp.md)
- [Git história](https://github.com/OndroSVK/UCM-Conferences-timovy-projekt/commits/main/)

Exporty: [PDF zápisnice](podklady/03-zapisnice-stretnuti.pdf), [editovateľný DOCX](podklady/03-zapisnice-stretnuti.docx), [štruktúrovaný zdroj](podklady/zapisnice.json).
