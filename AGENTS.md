# Pokyny pre AI a vývojárov

Tento repozitár obsahuje statický frontendový prototyp UCM Conferences v koreni a Laravel backend v priečinku `/backend`. Pred úpravou načítaj [AI príručku](docs/ai-prirucka.md); pri malej zmene stačí úvod a kapitoly príslušnej oblasti.

- **Backend (`/backend`)**: jediný zdroj rozhodnutí, otvorených otázok a stavu je [backend.md](docs/backend.md). Modulárna architektúra, jedna feature = jedna Action + test, stav iba cez `Workflow\Actions\ChangeStatus`, každý dopyt v kontexte konferencie.
- **Pripojenie databázy MariaDB**: postupuj podľa [manuálu prepojenia databázy](docs/prepojenie-databazy.md); schéma vzniká iba migráciami.
- Rozsah, architektúra a obmedzenia: [projektová dokumentácia](docs/projektova-dokumentacia.md).
- Skutočné používateľské postupy: [používateľská príručka](docs/pouzivatelska-prirucka.md).
- Požiadavky, návrhy a otvorené otázky: [analýza](docs/frontend-analyza.md), značky Z/N/O.
- Dokumentácia je po slovensky; identifikátory kódu po anglicky. Texty backendu pre používateľa sú v `backend/lang/en` (BO12).
- Zachovaj oddelenie aplikácie od nadradeného školského prezentačného webu.
- Pri funkčnej zmene aktualizuj dotknuté príručky v rovnakom commite a over skutočné správanie zmenenej oblasti (v backende `vendor/bin/pest`).
- Nevkladaj skutočné osobné údaje, heslá ani lokálnu konfiguráciu (`.env`) do repozitára. Statický prototyp nemá autentifikáciu; backend má vlastné účty, testovacie dáta sú iba vymyslené (`example.test`).
