import {
  icon,
  link,
  button,
  badge,
  field,
  select,
  area,
  notice,
  panel,
  heading,
  stat,
  tabs,
  formActions,
  table,
  rowLink,
  demoFile,
  timeline,
  submissionTitle,
  abstract,
} from "./ui.js";
export const accountHome = () =>
  `${heading("ÚČASTNÍCKA ZÓNA / ANS 2027", "Dobrý deň, Martin.", "Všetko dôležité pre vašu konferenciu na jednom mieste.", link("Nový príspevok", "submission-new", "primary", "plus"))}<section class="welcome-banner"><div><div class="eyebrow" style="margin-bottom:12px">VAŠA ĎALŠIA VEDECKÁ ZASTÁVKA</div><h2>ANS ’27. Sme radi, že budete pri tom.</h2><p>15. – 17. september 2027 · Štrbské Pleso<br>Registráciu máte hotovú. Teraz je priestor pre vaše myšlienky.</p></div><span class="banner-symbol" aria-hidden="true">✳</span></section><div class="four-grid">${stat("Moja účasť", "Potvrdená", "Rečník · ANS 2027", "", "users")}${stat("Účastnícky poplatok", "180 €", "✓ Zaplatené", "lime", "coin")}${stat("Moje príspevky", "02", "1 v recenzii · 1 koncept", "", "file")}${stat("Odovzdanie abstraktov", "15. 05.", "2027 · dôležitý termín", "lavender", "calendar")}</div><div class="content-grid"><div><section class="panel"><div class="panel-head"><h2>Moje príspevky</h2>${rowLink("submissions", "Všetky príspevky")}</div>${table(
    ["PRÍSPEVOK", "STAV", ""],
    [
      [
        `<strong>${submissionTitle}</strong><span class="secondary">ANS-027 · Prednáška</span>`,
        badge("V recenzii", "blue"),
        rowLink("submission-detail"),
      ],
      [
        '<strong>Modelovanie environmentálnych dát</strong><span class="secondary">Koncept · Poster</span>',
        badge("Rozpracované"),
        rowLink("submission-new", "Pokračovať"),
      ],
    ],
  )}</section><section class="panel" style="margin-top:24px"><div class="panel-head"><h2>Čo vás čaká</h2>${icon("calendar")}</div><div class="panel-body"><div class="task-row">${icon("file")}<div>Odovzdanie abstraktov<small>Posledný deň na odovzdanie príspevku</small></div>${badge("15. máj", "orange")}</div><div class="task-row">${icon("review")}<div>Výsledky recenzného konania<small>Rozhodnutie nájdete v detaile príspevku</small></div>${badge("30. jún")}</div><div class="task-row">${icon("users")}<div>Stretneme sa na ANS ’27<small>Tri dni vedy a nových spojení</small></div>${badge("15. september", "green")}</div></div></section></div><aside>${panel("Najnovšie oznámenia", `<ol class="timeline"><li><strong>Váš príspevok sa posudzuje</strong><small>20. apríl 2027</small>Príspevok ANS-027 je v recenznom konaní.</li><li><strong>Platba bola zaevidovaná</strong><small>18. apríl 2027</small>Ďakujeme, váš poplatok je uhradený.</li><li><strong>Vitajte na ANS 2027!</strong><small>16. apríl 2027</small>Vaša registrácia je potvrdená.</li></ol>`)}<div class="card lavender" style="margin-top:24px"><div class="eyebrow">PRIPRAVTE SA NA STRETNUTIE</div><h3 style="margin-top:18px">Ako sa k nám dostanete?</h3><p>Doprava, ubytovanie a všetko praktické pred cestou.</p><div style="margin-top:18px">${rowLink("information", "Praktické informácie")}</div></div></aside></div>`;
export const profile = () =>
  `${heading("MÔJ ÚČET", "Môj profil", "Kontaktné a pracovné údaje vášho účtu.")}<div class="content-grid" style="margin-top:0"><section class="panel"><div class="panel-head"><h2>Osobné údaje</h2>${badge("Účastník", "blue")}</div><div class="panel-body"><form data-form="profile"><div class="form-grid">${field("Titul pred menom", "title", "Mgr.")}${field("Titul za menom", "suffix", "PhD.")}${field("Meno", "firstName", "Martin")}${field("Priezvisko", "lastName", "Novák")}${field("E-mail", "email", "martin.novak@example.com", "email", "full")}${field("Organizácia", "organization", "Univerzita sv. Cyrila a Metoda v Trnave", "text", "full")}${field("Pracovisko", "department", "Fakulta prírodných vied", "text", "full")}${select("Krajina", "country", ["Slovensko", "Česko", "Iná"])}<label class="field">Telefón <small>Voliteľné</small><input name="phone" type="tel" placeholder="+421"></label></div>${formActions()}</form></div></section><aside class="card lavender"><div class="avatar" style="width:64px;height:64px;font-size:23px;margin-bottom:22px">MN</div><h2>Martin Novák</h2><p>UCM v Trnave<br>Účastnícky účet</p><hr style="margin:24px 0;border:0;border-top:1px solid var(--ink)"><p>Údaje profilu sa používajú pri registráciách na konferencie. Fakturačné údaje nájdete v konkrétnej účasti.</p><div style="margin-top:20px">${link("Moja účasť", "participation", "small flat")}</div></aside></div>`;
export const participation = (paid = true) =>
  `${heading("ANS 2027 / MOJA ÚČASŤ", "Všetko pripravené na stretnutie.", "Prehľad registrácie, účastníckeho poplatku a fakturačných údajov.")}${notice("Vaša registrácia je potvrdená. Tešíme sa na vašu účasť na ANS 2027.", "lime")}<div class="two-grid"><section class="card shadow ${paid ? "lime" : "yellow"}"><div class="card-top"><span class="eyebrow">ÚČASTNÍCKY POPLATOK</span>${badge(paid ? "Zaplatené" : "Čaká na úhradu", paid ? "green" : "orange")}</div><div class="amount">180 <small>EUR</small></div><p style="color:var(--ink)">${paid ? "Úhrada zaevidovaná 18. apríla 2027. Ďakujeme." : "Prosíme, uhraďte účastnícky poplatok bankovým prevodom."}</p><dl class="definition" style="margin-top:24px"><dt style="color:var(--ink)">Predpis</dt><dd>180,00 €</dd><dt style="color:var(--ink)">Uhradené</dt><dd>${paid ? "180,00" : "0,00"} €</dd><dt style="color:var(--ink)">Zostáva uhradiť</dt><dd><strong>${paid ? "0,00" : "180,00"} €</strong></dd></dl></section>${panel("Moja registrácia", `<dl class="definition"><dt>Konferencia</dt><dd>Applied Natural Sciences 2027</dd><dt>Kategória</dt><dd>Rečník</dd><dt>Účastník</dt><dd>Martin Novák</dd><dt>Registrácia</dt><dd>ANS-2027-0042</dd><dt>Registrované</dt><dd>16. apríl 2027</dd><dt>Organizácia</dt><dd>UCM v Trnave</dd></dl><div style="margin-top:24px">${rowLink("submissions", "Moje príspevky")}</div>`)}</div><div class="two-grid" style="margin-top:24px">${panel("Platobné údaje", `${notice("Ilustračné platobné údaje. Neposielajte žiadnu platbu.", "yellow")}<dl class="definition"><dt>Príjemca</dt><dd>UCM v Trnave – ukážka</dd><dt>IBAN</dt><dd>SK•• •••• •••• •••• •••• ••••</dd><dt>Variabilný symbol</dt><dd>20270042 ${button("Kopírovať", "copy-vs", "small flat", "copy")}</dd><dt>Splatnosť</dt><dd>30. apríl 2027</dd></dl>`)}${panel("Fakturačné a organizačné údaje", `<dl class="definition"><dt>Organizácia</dt><dd>Univerzita sv. Cyrila a Metoda</dd><dt>Mesto</dt><dd>Trnava, Slovensko</dd><dt>Stravovanie</dt><dd>Bez uvedených obmedzení</dd></dl><div style="margin-top:25px">${button("Upraviť údaje", "billing", "small flat", "edit")}</div>`)}</div>`;
export const mySubmissions = () =>
  `${heading("AUTORSKÁ ZÓNA", "Moje príspevky", "Vaše myšlienky, výsledky a ich cesta na konferenciu.", link("Nový príspevok", "submission-new", "primary", "plus"))}<div class="two-grid"><article class="card shadow"><div class="card-top">${badge("V recenzii", "blue")}<span class="eyebrow">ANS-027</span></div><h2 style="font-size:26px">${submissionTitle}</h2><p>Prednáška · Informatika / environmentálne vedy</p><p>Martin Novák, Petra Kováčová</p><div class="progress"><span style="width:60%"></span></div><p>Príspevok bol odovzdaný a aktuálne sa odborne posudzuje.</p><div style="margin-top:24px">${link("Detail príspevku", "submission-detail", "small flat")}</div></article><article class="card"><div class="card-top">${badge("Rozpracované")}<span class="eyebrow">KONCEPT</span></div><h2 style="font-size:26px">Modelovanie environmentálnych dát</h2><p>Poster · Environmentálne vedy</p><p>Martin Novák</p><div class="progress"><span style="width:20%;background:var(--muted)"></span></div><p>Vaša práca ešte nie je odovzdaná. Pokračujte v príprave.</p><div style="margin-top:24px">${link("Pokračovať v úprave", "submission-new", "small flat")}</div></article></div><div style="margin-top:28px">${notice("Termín odovzdania abstraktov v tejto ukážke: 15. máj 2027. Svoje príspevky spravujete v kontexte ANS 2027.")}</div>`;
export const newSubmission = () =>
  `${heading("MOJE PRÍSPEVKY / NOVÝ PRÍSPEVOK", "Každý objav začína myšlienkou.", "Pripravte svoj abstrakt a pridajte súbor k odbornému príspevku.")}<div class="content-grid" style="margin-top:0"><section class="panel"><div class="panel-body"><form data-form="submission"><section class="form-section"><h3>Základné informácie</h3><div class="form-grid">${field("Názov príspevku", "title", "", "text", "full")}${select("Forma príspevku", "type", ["Prednáška", "Poster"])}${select("Odborná oblasť", "track", ["Informatika", "Biológia", "Biotechnológie", "Chémia", "Environmentálne vedy"])}${area("Abstrakt", "abstract", "", "full")}${field("Kľúčové slová", "keywords", "", "text", "full")}</div></section><section class="form-section"><h3>Autori a spoluautori</h3><div id="authors"><div class="form-grid">${field("Meno a priezvisko", "authorName", "Martin Novák")}${field("Pracovisko", "authorOrganization", "UCM v Trnave")}</div></div><div style="margin-top:18px">${button("Pridať spoluautora", "add-author", "small flat", "plus")}</div></section><section class="form-section"><h3>Súbor príspevku</h3><label class="dropzone field">${icon("upload")}Vyberte článok alebo poster<small>Lokálna ukážka výberu súboru. Súbor sa nikam neodosiela.</small><input type="file" name="submissionFile" aria-label="Súbor príspevku"><span data-file-label></span></label></section><div class="form-actions"><button type="button" class="btn flat" data-action="save-draft">Uložiť koncept</button><button class="btn primary" type="submit">Odovzdať ukážku ${icon("arrow")}</button></div></form></div></section><aside><div class="card lavender"><div class="eyebrow">MALÝ CHECKLIST. VEĽKÁ POMOC.</div><h3 style="margin:20px 0">Pred odovzdaním</h3><ul class="check-list"><li>${icon("check")}Výstižný názov príspevku</li><li>${icon("check")}Abstrakt so zhrnutím výsledkov</li><li>${icon("check")}Všetci spoluautori a pracoviská</li><li>${icon("check")}Správny súbor a forma prezentácie</li></ul></div><div class="card" style="margin-top:22px"><span class="eyebrow">TERMÍN ODOVZDANIA</span><h2 style="margin:16px 0">15. máj 2027</h2><p>Ukážkový termín pre ANS 2027. Formáty a limity súborov budú spresnené.</p></div></aside></div>`;
export const submissionDetail = (tab = 0) =>
  `${heading("MOJE PRÍSPEVKY / ANS-027", "Detail príspevku", "Applied Natural Sciences 2027", link("Späť na príspevky", "submissions", "flat", ""))}<section class="card" style="margin-bottom:25px"><div class="card-top"><span class="eyebrow">ANS-027 / PREDNÁŠKA</span>${badge("V recenzii", "blue")}</div><h2 class="submission-title">${submissionTitle}</h2><p>Martin Novák, Petra Kováčová · UCM v Trnave</p></section>${tabs(["Prehľad", "Súbory a verzie", "Pripomienky", "História"], tab)}<div id="tab-content">${detailTab(tab)}</div>`;
export const detailTab = (tab) =>
  tab === 1
    ? panel(
        "Súbory a verzie",
        `${demoFile()}<div style="margin-top:14px">${demoFile("ANS-027_abstrakt_v1.pdf")}</div><p class="muted" style="font-size:11px;margin-top:20px">Predchádzajúce verzie sú zachované. Ďalšia revízia bude dostupná po výzve výboru.</p>`,
      )
    : tab === 2
      ? panel(
          "Pripomienky pre autora",
          `<div class="empty">${icon("review")}<h3>Na dobré posúdenie treba čas.</h3><p>Po ukončení hodnotenia tu nájdete pripomienky a rozhodnutie určené vám.</p></div>`,
        )
      : tab === 3
        ? panel("História príspevku", timeline())
        : `<div class="content-grid" style="margin-top:0">${panel("Abstrakt", `<p class="prose">${abstract}</p><div style="margin-top:22px">${badge("Strojové učenie", "blue")} ${badge("Kvalita vody", "green")} ${badge("Environmentálne dáta")}</div>`)}${panel("Priebeh príspevku", timeline())}</div>`;
