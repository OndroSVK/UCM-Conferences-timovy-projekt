# Web konferencie – frontendová ukážka

Dátum: 31. 10. 2026. Podľa pokynu používateľa je implementovaný prvý preklikateľný návrh správy verejného webu ročníka ANS 2027. Backend, oprávnenia a serverové publikovanie zatiaľ nie sú zapojené.

## Vstup a obrazovky

`superadmin/website.html` je dostupný zo správy ročníka, zo zoznamu konferencií aj z bočnej navigácie organizátora a správy systému. Katalóg obsahuje novú obrazovku S05. Práva jednotlivých rolí sú zatiaľ iba ukážkou navigácie.

- **Obsah a vzhľad:** názov, označenie ročníka, nadpis, texty, termín, miesto, kontakt, farba a výber loga, úvodnej fotky a pozadia.
- **HTML:** priamo upraviteľná šablóna s farebným zvýraznením, počtom riadkov, odsadením klávesom Tab, vkladacími značkami a obnovením predvolenej šablóny.
- **CSS:** vlastné štýly verejného webu so zvýraznením a priebežným náhľadom.
- **Obrázky:** výber súborov alebo potiahnutie, lokálne spracovanie, názov, alternatívny text, rozmery, priradenie do vizuálnych pozícií a vloženie do HTML.

Náhľad prepína šírky 1024, 768 a 390 px. Na menšom displeji sa prepína medzi editorom a náhľadom. Väčší náhľad možno otvoriť v samostatnej karte.

## Koncept a verzie

1. Úpravy sa ihneď zobrazujú v izolovanom náhľade.
2. **Uložiť koncept** uloží aktuálne HTML, CSS, obsah a výber obrázkov v tomto prehliadači.
3. **Publikovať ukážku** po potvrdení vytvorí samostatnú lokálnu verziu.
4. **Publikovaný náhľad** zobrazuje poslednú publikovanú verziu; ďalšie úpravy konceptu ju nemenia.
5. **História verzií** obnoví vybranú verziu do rozpracovaného konceptu. Publikovaný výstup sa prepne až ďalším publikovaním.

Obnovenie predvolenej šablóny mení len HTML a CSS konceptu. Texty a obrázky zostávajú zachované. Pri odchode s neuloženými zmenami sa zobrazí štandardná otázka prehliadača. Chyba ukladania ponechá stav „Neuložené zmeny“.

Údaje sú v IndexedDB `ucm-website-prototype-v1`, ročník `ans-2027`, úložiská `editions` a `assets`. Zachovajú sa po obnovení aj zavretí karty podľa pravidiel úložiska prehliadača. Zmazanie dát webu ich odstráni. Iné zariadenie, prehliadač alebo doména má vlastnú nezávislú ukážku; odkaz na náhľad tieto dáta neprenáša.

Textové polia tejto ukážky sú nezávislé od formulára všeobecných nastavení ročníka. Pri implementácii backendu majú oba pohľady používať spoločný zdroj údajov.

## Obrázky

Povolené vstupy sú JPG, PNG a WebP do 10 MB. Obrázok sa dekóduje a zmenší na najviac 2400 px po dlhšej strane. Preferuje sa WebP; výsledný formát závisí od podpory prehliadača. Obrazové dáta sa ukladajú ako Blob v IndexedDB, náhľady používajú dočasné blob URL.

Vloženie do HTML používa trvalú značku `{{media:ID}}`, ktorá sa pri renderovaní nahradí aktuálnou blob URL. Publikované verzie uchovávajú vlastný obsah, výber obrázkov a alternatívne texty. Obrázok používaný v koncepte alebo publikovanej verzii sa nedá odstrániť. Odstrániť možno nepoužívané nahraté obrázky; predvolené ilustrácie zostávajú súčasťou ukážky.

## Šablóny a moduly

- `templates/conference-index.html` – predvolený verejný HTML dokument.
- `templates/conference.css` – predvolený štýl s vkladacími značkami.
- `superadmin/website.html` – administračná obrazovka, formuláre a HTML šablóny kariet a dialógov.
- `website-editor.css` – štýly editora a samostatného náhľadu.
- `website-editor.js` – ovládanie, editácia, náhľad, obrázky a história.
- `website-store.js` – lokálne úložisko, predvolené údaje a základné obrázky.
- `website-document.js` – dosadenie značiek a príprava izolovaného dokumentu.
- `public/website-preview.html` a `website-preview.js` – samostatný náhľad konceptu alebo publikovanej verzie podľa `?mode=draft` / `?mode=published`.

Značky zahŕňajú `{{conference.title}}`, `{{conference.edition}}`, `{{conference.heading}}`, `{{conference.description}}`, `{{conference.about}}`, `{{conference.date}}`, `{{conference.venue}}`, `{{conference.contact}}`, `{{conference.accent}}`, `{{media.logo}}`, `{{media.hero}}`, `{{media.heroAlt}}`, `{{media.background}}`, `{{registration.url}}` a `{{program.url}}`. Neznáme značky sa zobrazia pri náhľade a zablokujú lokálne publikovanie.

Náhľad beží v iframe bez povolenia skriptov alebo prístupu k administrácii. Vložené skripty, formuláre, vnorené rámce a obsluhy udalostí sa odstraňujú; CSP obmedzuje načítanie zdrojov. Toto je ochrana náhľadu prototypu, nie hotová serverová validácia pre budúcu produkčnú implementáciu.

Zmeny editora sa nezapisujú do zdrojového `index.html`, na GitHub ani do aktuálneho verejného webu. Ide o lokálnu ukážku budúceho pracovného postupu.
