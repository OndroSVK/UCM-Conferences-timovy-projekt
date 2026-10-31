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
  searchBar,
  person,
  rowLink,
  demoFile,
  timeline,
  submissionTitle,
  abstract,
  submissions,
  people,
} from "./ui.js";
import { schedule } from "./public-pages.js";
const submissionRows = (role = "organizer", limit = 99) =>
  submissions
    .slice(0, limit)
    .map((s, i) => [
      `<strong>${s[0]}</strong>`,
      `<strong style="white-space:normal;min-width:200px;max-width:350px">${s[1]}</strong><span class="secondary">${s[2]}</span>`,
      s[3],
      badge(s[4], s[5]),
      rowLink(
        role === "committee" ? `decision?id=${i}` : `admin-submission?id=${i}`,
      ),
    ]);
export const organizerHome = () =>
  `${heading("PRACOVNÝ PRIESTOR / ANS 2027", "Veľké myšlienky. Dobrý prehľad.", "Dobrý deň, Jana. Tu je aktuálny stav vašej konferencie.", link("Verejný web", "home", "flat", "external"))}<section class="welcome-banner"><div><div class="eyebrow" style="margin-bottom:12px">APPLIED NATURAL SCIENCES 2027</div><h2>Veda spája. Vy jej dávate priestor.</h2><p>Registrácia je otvorená. Najbližší míľnik: odovzdanie abstraktov, 15. mája 2027.</p></div><span class="banner-symbol" aria-hidden="true">✳</span></section><div class="four-grid">${stat("Registrovaní účastníci", "128", "↑ 12 nových tento týždeň", "", "users")}${stat("Odovzdané príspevky", "86", "24 čaká na formálnu kontrolu", "lime", "file")}${stat("Dokončené recenzie", "42 <small>/ 64</small>", "22 posudkov ešte očakávame", "", "review")}${stat("Do konferencie", "148 <small>dní</small>", "15. – 17. september 2027", "lavender", "calendar")}</div><div class="content-grid"><div><section class="panel"><div class="panel-head"><h2>Príspevky pod kontrolou</h2>${rowLink("admin-submissions", "Všetky príspevky")}</div>${table(["ID", "PRÍSPEVOK", "FORMA", "STAV", ""], submissionRows("organizer", 4))}</section><section class="panel" style="margin-top:24px"><div class="panel-head"><h2>Vyžaduje vašu pozornosť</h2>${badge("3 úlohy", "orange")}</div><div class="panel-body"><a href="#/admin-submissions" class="task-row">${icon("file")}<div>Skontrolovať nové príspevky<small>24 príspevkov čaká na formálnu kontrolu</small></div>${icon("arrow")}</a><a href="#/admin-reviews" class="task-row">${icon("review")}<div>Prideliť recenzentov<small>8 príspevkov ešte nemá recenzenta</small></div>${icon("arrow")}</a><a href="#/admin-program" class="task-row">${icon("calendar")}<div>Pripraviť odborný program<small>Pracovný návrh ročníka 2027</small></div>${icon("arrow")}</a></div></section></div><aside><section class="panel"><div class="panel-head"><h2>Posledná aktivita</h2>${icon("clock")}</div><div class="activity">${[
    ["file", "Nový príspevok ANS-032", "Eva Poláková · pred 12 minútami"],
    ["review", "Recenzia bola odovzdaná", "Peter Malík · pred 38 minútami"],
    ["users", "Nová registrácia účastníka", "Nina Urbanová · pred 1 hodinou"],
    [
      "check",
      "Príspevok ANS-028 prijatý",
      "Programový výbor · pred 2 hodinami",
    ],
  ]
    .map(
      (a) =>
        `<div class="activity-item"><span class="activity-icon">${icon(a[0])}</span><div>${a[1]}<small>${a[2]}</small></div></div>`,
    )
    .join(
      "",
    )}</div></section><div class="card blue" style="margin-top:24px"><div class="eyebrow">NAJBLIŽŠÍ TERMÍN</div><h2 style="font-size:35px;margin-top:20px">15. máj</h2><h3>Odovzdanie abstraktov</h3><p>Máte všetko pripravené? Skontrolujte termíny a nastavenia ročníka.</p><div style="margin-top:24px">${link("Nastavenia ročníka", "settings", "small lime", "arrow")}</div></div></aside></div>`;
export const registrations = () =>
  `${heading("ORGANIZÁCIA / REGISTRÁCIE", "Ľudia, ktorí tvoria konferenciu.", "Prehľad účastníkov a ich registrácií na ANS 2027.", link("Exporty", "exports", "flat", "download"))}<div class="four-grid">${stat("Všetky registrácie", "128", "ANS 2027", "", "users")}${stat("Rečníci", "64", "Odborné prezentácie", "lime", "file")}${stat("Autori posterov", "38", "Posterové prezentácie", "", "file")}${stat("Pasívni účastníci", "26", "Súčasť vedeckej komunity", "lavender", "users")}</div><section class="panel">${searchBar("Hľadať meno alebo organizáciu...", ["Všetky kategórie", "Rečník", "Autor posteru", "Pasívny účastník"])}${table(
    ["ÚČASTNÍK", "ORGANIZÁCIA", "KATEGÓRIA", "PLATBA", ""],
    people.map((p, i) => [
      person(p[0], `ANS-2027-00${42 + i}`),
      p[1],
      p[2],
      badge(p[3], p[4]),
      rowLink(`registration-detail?id=${i}`),
    ]),
    "Zobrazených 8 ukážkových registrácií · ANS 2027",
  )}</section>`;
export const registrationDetail = (id = 0) => {
  const p = people[id] || people[0];
  return `${heading("ÚČASTNÍCI / DETAIL REGISTRÁCIE", p[0], `ANS-2027-00${42 + Number(id)} · Applied Natural Sciences 2027`, link("Späť na zoznam", "registrations", "flat", ""))}<div class="two-grid">${panel("Registrácia a kontaktné údaje", `<dl class="definition"><dt>Účastník</dt><dd>${p[0]}</dd><dt>Organizácia</dt><dd>${p[1]}</dd><dt>E-mail</dt><dd>ucastnik${id}@example.com</dd><dt>Kategória</dt><dd>${p[2]}</dd><dt>Stav registrácie</dt><dd>${badge("Potvrdená", "green")}</dd><dt>Dátum registrácie</dt><dd>16. apríl 2027</dd></dl>`)}${panel("Účastnícky poplatok", `${badge(p[3], p[4])}<div class="amount">180 <small>EUR</small></div><p class="muted" style="font-size:12px">Manuálna evidencia platby je dostupná v ekonomickom rozhraní.</p><div style="margin-top:22px">${link("Otvoriť detail úhrady", `payment-detail?id=${id}`, "small flat")}</div>`)}</div><div style="margin-top:24px">${panel("Súvisiaci príspevok", `<h3>${submissions[id % submissions.length][1]}</h3><p class="muted" style="font-size:12px;margin:12px 0 20px">${submissions[id % submissions.length][0]} · ${submissions[id % submissions.length][3]}</p>${link("Detail príspevku", `admin-submission?id=${id % submissions.length}`, "small flat")}`)}</div>`;
};
export const adminSubmissions = (committee = false) =>
  `${heading(committee ? "PROGRAMOVÝ VÝBOR / PRÍSPEVKY" : "ORGANIZÁCIA / PRÍSPEVKY", committee ? "Poznatky čakajú na rozhodnutie." : "Od nápadu k pódiu.", "Odborné príspevky konferencie Applied Natural Sciences 2027.")}<section class="panel">${searchBar("Názov, autor alebo ID...", ["Všetky stavy", "Na kontrolu", "V recenzii", "Prijaté", "Vyžiadané úpravy"])}${table(["ID", "NÁZOV A AUTOR", "FORMA", "STAV", ""], submissionRows(committee ? "committee" : "organizer"), "6 ukážkových príspevkov · ANS 2027")}</section>`;
export const adminSubmission = (id = 0) => {
  const s = submissions[id] || submissions[0];
  return `${heading("PRÍSPEVKY / " + s[0], "Detail a formálna kontrola", "Organizátorské rozhranie · ANS 2027", link("Všetky príspevky", "admin-submissions", "flat", ""))}<div class="card" style="margin-bottom:24px"><div class="card-top"><span class="eyebrow">${s[0]} / ${s[3]}</span>${badge(s[4], s[5])}</div><h2 class="submission-title">${s[1]}</h2><p>${s[2]} · Ukážkové autorské údaje</p></div><div class="content-grid" style="margin-top:0"><div>${panel("Abstrakt a súbory", `<p class="prose">${abstract}</p><div style="margin-top:24px">${demoFile()}</div>`)}<div style="margin-top:24px">${panel("Pridelení recenzenti", `<div class="task-row">${person("Peter Malík", "Odborný recenzent")}${badge("Rozpracované", "orange")}</div><div class="task-row">${person("Zuzana Veselá", "Odborná recenzentka")}${badge("Odovzdané", "green")}</div><div style="margin-top:20px">${button("Prideliť recenzenta", "assign-reviewer", "small flat", "plus")}</div>`)}</div></div><aside>${panel("Formálna kontrola", `<form data-form="formal-check"><label class="checkbox"><input type="checkbox" checked> Názov a abstrakt sú vyplnené</label><label class="checkbox" style="margin-top:12px"><input type="checkbox" checked> Údaje autorov sú úplné</label><label class="checkbox" style="margin-top:12px"><input type="checkbox" checked> Súbor je priložený</label>${formActions("Potvrdiť kontrolu")}</form>`)}<div style="margin-top:24px">${panel("História", timeline())}</div></aside></div>`;
};
export const adminReviews = () =>
  `${heading("ORGANIZÁCIA / RECENZNÉ KONANIE", "Odborný pohľad. Jasný priebeh.", "Sledujte pridelenia, termíny a postup recenzného konania.")}<div class="four-grid">${stat("Pridelené recenzie", "64", "32 príspevkov", "", "review")}${stat("Dokončené", "42", "66 % všetkých pridelení", "lime", "check")}${stat("Rozpracované", "18", "Práca recenzentov pokračuje", "", "edit")}${stat("Po termíne", "04", "Vyžaduje pozornosť", "yellow", "clock")}</div><section class="panel">${searchBar("Príspevok alebo recenzent...", ["Všetky stavy", "Rozpracované", "Odovzdané", "Po termíne"])}${table(
    ["PRÍSPEVOK", "RECENZENT", "TERMÍN", "STAV", ""],
    submissions
      .slice(0, 5)
      .map((s, i) => [
        `<strong>${s[0]}</strong><span class="secondary">${s[1].slice(0, 45)}…</span>`,
        [
          "Peter Malík",
          "Zuzana Veselá",
          "Milan Tóth",
          "Peter Malík",
          "Ivana Holá",
        ][i],
        "31. 05. 2027",
        badge(
          [
            "Rozpracované",
            "Odovzdané",
            "Po termíne",
            "Odovzdané",
            "Rozpracované",
          ][i],
          ["orange", "green", "red", "green", "orange"][i],
        ),
        rowLink(`admin-submission?id=${i}`),
      ]),
  )}</section>`;
export const reviewList = () =>
  `${heading("RECENZENT / ANS 2027", "Váš odborný pohľad sa počíta.", "Príspevky, ktoré vám boli pridelené na odborné posúdenie.")}<div class="three-grid" style="margin-bottom:25px">${stat("Pridelené príspevky", "03", "Iba vaše pridelenia", "", "file")}${stat("Na dokončenie", "02", "Termín 31. máj 2027", "lavender", "edit")}${stat("Odovzdané", "01", "Ďakujeme za váš čas", "lime", "check")}</div><section class="panel">${table(
    ["PRÍSPEVOK", "OBLASŤ", "TERMÍN", "STAV", ""],
    submissions
      .slice(0, 3)
      .map((s, i) => [
        `<strong style="white-space:normal;min-width:200px">${s[1]}</strong><span class="secondary">${s[0]}</span>`,
        ["Informatika", "Biotechnológie", "Chémia"][i],
        "31. 05. 2027",
        badge(
          i === 2 ? "Odovzdané" : "Rozpracované",
          i === 2 ? "green" : "orange",
        ),
        rowLink(`review-detail?id=${i}`, i === 2 ? "Prezrieť" : "Hodnotiť"),
      ]),
  )}</section>`;
export const reviewDetail = (id = 0) => {
  const s = submissions[id] || submissions[0];
  return `${heading("MOJE RECENZIE / " + s[0], "Priestor pre odborné hodnotenie", "Recenzia sa týka pridelenej verzie 2 · termín 31. máj 2027", link("Moje recenzie", "reviews", "flat", ""))}<div class="review-grid"><section class="panel"><div class="panel-body"><span class="eyebrow">${s[0]} / ${s[3]}</span><h2 style="font-size:28px;margin:18px 0 24px">${s[1]}</h2><h3 style="margin-bottom:16px">Abstrakt</h3><p class="prose">${abstract}</p><div style="margin-top:24px">${demoFile()}</div></div></section><section class="panel"><div class="panel-head"><h2>Hodnotiaci formulár</h2>${badge("Koncept", "orange")}</div><div class="panel-body">${notice("Ukážkové kritériá. Odporúčanie recenzenta nie je konečné rozhodnutie výboru.")}<form data-form="review">${["Originalita a prínos", "Odborná úroveň", "Zrozumiteľnosť spracovania"].map((n, i) => `<label class="score">${n}<select name="score${i}"><option>5 / 5</option><option>4 / 5</option><option>3 / 5</option><option>2 / 5</option><option>1 / 5</option></select></label>`).join("")}${area("Pripomienky pre autora", "comments")}<div style="margin-top:18px">${select("Odporúčanie", "recommendation", ["Prijať", "Prijať po úpravách", "Zamietnuť"])}</div><div class="form-actions">${button("Uložiť koncept", "save-review", "flat")}<button class="btn primary">Odovzdať recenziu ${icon("check")}</button></div></form></div></section></div>`;
};
export const decision = (id = 0) => {
  const s = submissions[id] || submissions[0];
  return `${heading("PROGRAMOVÝ VÝBOR / " + s[0], "Odporúčania poznáme. Rozhodnutie je na vás.", "Odborné rozhodnutie o príspevku · ANS 2027", link("Všetky príspevky", "committee-submissions", "flat", ""))}<div class="content-grid" style="margin-top:0"><div>${panel("Príspevok", `<h2 class="submission-title">${s[1]}</h2><p class="prose">${abstract}</p><div style="margin-top:25px">${demoFile()}</div>`)}<div style="margin-top:24px">${panel("Odovzdané odborné posudky", `<article style="margin-bottom:24px"><div class="card-top" style="margin-bottom:12px"><h3>Recenzia 01</h3>${badge("Odporúča prijať", "green")}</div><p class="prose">Príspevok prináša zaujímavé výsledky a má zrozumiteľne opísanú metodiku. Odporúčam rozšíriť porovnanie s existujúcimi prístupmi.</p></article><article><div class="card-top" style="margin-bottom:12px"><h3>Recenzia 02</h3>${badge("Prijať po úpravách", "orange")}</div><p class="prose">Odporúčam spresniť opis dátového súboru a doplniť diskusiu limitov modelu.</p></article>`)}</div></div><aside>${panel("Rozhodnutie výboru", `<form data-form="decision">${select("Výsledok posúdenia", "decision", ["Prijať", "Podmienečne prijať", "Vyžiadať úpravy", "Zamietnuť"])}<div style="margin-top:20px">${area("Odôvodnenie pre autora", "reason")}</div>${formActions("Potvrdiť ukážkové rozhodnutie")}</form>`)}<div style="margin-top:24px">${notice("Pracovná ukážka rozhodovacieho procesu. Žiadne rozhodnutie ani e-mail sa neodosiela.")}</div></aside></div>`;
};
export const programEditor = (committee = false, day = 0) =>
  `${heading(committee ? "PROGRAMOVÝ VÝBOR / PROGRAM" : "ORGANIZÁCIA / PROGRAM", committee ? "Program pripravený na váš pohľad." : "Dajte vede jej čas a miesto.", committee ? "Skontrolujte návrh odborného programu pred schválením." : "Pracovný návrh programu konferencie ANS 2027.", committee ? button("Schváliť návrh", "approve-program", "primary", "check") : button("Pridať blok", "add-session", "primary", "plus"))}${notice(committee ? "Program čaká na schválenie výborom. Ukážka zobrazenia pre programový výbor." : "Pracovný návrh · verejný program sa zobrazí až po schválení a publikovaní.")}<div id="schedule-container">${schedule(!committee, day)}</div><div class="form-actions">${committee ? button("Vrátiť na úpravu", "return-program", "flat") : button("Predložiť na schválenie", "submit-program", "primary", "arrow")}</div>`;
export const payments = () =>
  `${heading("EKONOMICKÉ ODDELENIE / ANS 2027", "Platby v dobrom poriadku.", "Manuálna evidencia účastníckych poplatkov a prijatých úhrad.", link("Ekonomický export", "finance-export", "flat", "download"))}<div class="four-grid">${stat("Predpísané poplatky", "19 440 €", "128 registrácií", "", "coin")}${stat("Prijaté úhrady", "14 580 €", "75 % z predpisu", "lime", "check")}${stat("Zostáva uhradiť", "4 860 €", "32 otvorených predpisov", "", "clock")}${stat("Po splatnosti", "06", "Vyžaduje kontrolu", "yellow", "info")}</div><section class="panel">${searchBar("Meno alebo variabilný symbol...", ["Všetky stavy", "Zaplatené", "Čaká na úhradu", "Čiastočne zaplatené", "Oslobodené"])}${table(
    ["ÚČASTNÍK", "VARIABILNÝ SYMBOL", "PREDPIS", "UHRADENÉ", "STAV", ""],
    people.map((p, i) => [
      person(p[0], p[1]),
      `202700${42 + i}`,
      i === 6 ? "0,00 €" : "180,00 €",
      p[3] === "Zaplatené"
        ? "180,00 €"
        : p[3] === "Čiastočne zaplatené"
          ? "90,00 €"
          : "0,00 €",
      badge(p[3], p[4]),
      rowLink(`payment-detail?id=${i}`),
    ]),
  )}</section>`;
export const paymentDetail = (id = 0, paidOverride = false) => {
  const p = people[id] || people[0],
    received =
      paidOverride !== false
        ? Number(paidOverride)
        : p[3] === "Zaplatené"
          ? 180
          : p[3] === "Čiastočne zaplatené"
            ? 90
            : 0,
    paid = received >= 180;
  return `${heading("PLATBY / DETAIL ÚHRADY", p[0], `Predpis ANS-2027-00${42 + Number(id)} · ANS 2027`, link("Späť na platby", "payments", "flat", ""))}<div class="two-grid"><article class="card shadow ${paid ? "lime" : "yellow"}"><div class="card-top"><span class="eyebrow">ÚČASTNÍCKY POPLATOK</span>${badge(paid ? "Zaplatené" : received > 0 ? "Čiastočne zaplatené" : p[3], paid ? "green" : received > 0 ? "blue" : p[4])}</div><div class="amount">180 <small>EUR</small></div><dl class="definition"><dt>Uhradená suma</dt><dd>${received.toFixed(2).replace(".", ",")} €</dd><dt>Zostatok</dt><dd>${Math.max(
    0,
    180 - received,
  )
    .toFixed(2)
    .replace(
      ".",
      ",",
    )} €</dd><dt>Variabilný symbol</dt><dd>202700${42 + Number(id)}</dd><dt>Splatnosť</dt><dd>30. 04. 2027</dd></dl></article>${panel("Zaznamenať prijatú úhradu", `<form data-form="payment"><div class="form-grid">${field("Prijatá suma (€)", "amount", "180", "number")}${field("Dátum prijatia", "paymentDate", "2027-04-20", "date")}${field("Poznámka / referencia", "reference", "Bankový prevod", "text", "full")}</div>${notice("Tlačidlo iba simuluje manuálnu evidenciu. Bankové operácie sa nevykonávajú.", "yellow")}${formActions("Zaevidovať ukážkovú úhradu")}</form>`)}</div><div style="margin-top:25px">${panel("História platieb", table(["DÁTUM", "OPERÁCIA", "SUMA", "ZAEVIDOVAL"], [["16. 04. 2027", "Vytvorený predpis", "180,00 €", "Registrácia"], ...(paid ? [["18. 04. 2027", "Prijatá úhrada", "180,00 €", "Ekonomické oddelenie"]] : [])]))}</div>`;
};
export const messages = () =>
  `${heading("ORGANIZÁCIA / KOMUNIKÁCIA", "Správne slová. Správnym ľuďom.", "Návrh hromadnej správy pre účastníkov ANS 2027.")}<div class="content-grid" style="margin-top:0"><section class="panel"><div class="panel-body"><form data-form="message"><div class="form-grid">${select("Adresáti", "recipients", ["Všetci účastníci", "Rečníci", "Autori posterov", "Účastníci s neuhradeným poplatkom"])}${select("Jazyk správy", "language", ["Slovenčina", "English"])}${field("Predmet", "subject", "Dôležité informácie k ANS 2027", "text", "full")}${area("Text správy", "body", "Milí účastníci,\n\ntešíme sa na naše spoločné stretnutie na konferencii ANS 2027. Radi by sme vám pripomenuli blížiaci sa termín odovzdania abstraktov.\n\nS pozdravom,\nOrganizačný tím ANS 2027", "full")}</div>${formActions("Zobraziť náhľad správy")}</form></div></section><aside><div class="card lime"><span class="eyebrow">OKRUH ADRESÁTOV</span><div class="amount" data-recipient-count>128</div><h3>účastníkov konferencie</h3><p>Pred odoslaním vždy skontrolujte príjemcov, predmet a znenie správy.</p></div><div style="margin-top:24px">${notice("V prototype sa žiadne e-maily neposielajú. Môžete si pozrieť návrh a simulovať výsledok.")}</div></aside></div>`;
export const exportsPage = (finance = false) =>
  `${heading(finance ? "EKONOMICKÉ ODDELENIE / EXPORT" : "ORGANIZÁCIA / EXPORTY", finance ? "Financie pripravené na ďalšiu prácu." : "Dáta, s ktorými sa dobre pracuje.", "Nastavte rozsah a pozrite si ukážkový výstup.")}<div class="content-grid" style="margin-top:0"><section class="panel"><div class="panel-head"><h2>Nastavenia exportu</h2>${icon("download")}</div><div class="panel-body"><form data-form="export"><div class="form-grid">${select("Typ výstupu", "exportType", finance ? ["Prehľad platieb"] : ["Zoznam účastníkov", "Prezenčná listina", "Zoznam príspevkov"])}${select("Kategória", "category", ["Všetky kategórie", "Rečníci", "Autori posterov", "Pasívni účastníci"])}${select("Platobný stav", "paymentStatus", ["Všetky stavy", "Zaplatené", "Čaká na úhradu"])}${select("Ročník", "edition", ["ANS 2027"])}</div><h3 style="margin:25px 0 20px">Stĺpce vo výstupe</h3>${["Meno a priezvisko", "Organizácia", finance ? "Variabilný symbol" : "Kategória účasti", finance ? "Predpis a úhrada" : "Stav registrácie"].map((t) => `<label class="checkbox" style="margin:12px 0"><input type="checkbox" name="columns" value="${t}" checked>${t}</label>`).join("")}${formActions("Zobraziť náhľad exportu")}</form></div></section><aside class="card lavender"><div class="eyebrow">UKÁŽKOVÝ VÝSTUP</div><h2 style="margin:20px 0">Najprv kontrola.<br>Potom export.</h2><p>Náhľad obsahuje vzorové záznamy z prototypu. Ukážkový súbor možno stiahnuť vo formáte CSV, finálny Excel export zatiaľ nie je zapojený.</p><p>Výstup nezahŕňa stravovacie ani interné recenzné údaje.</p></aside></div>`;
export const conferences = () =>
  `${heading("SPRÁVA SYSTÉMU", "Priestor pre každý ročník.", "Konferencie, aktuálne ročníky a ich archív.", button("Nový ročník", "new-edition", "primary", "plus"))}<div class="two-grid"><article class="card shadow lime"><div class="card-top"><span class="eyebrow">APPLIED NATURAL SCIENCES</span>${badge("Registrácia otvorená", "green")}</div><div class="conference-tile"><div><h2>ANS</h2><p style="color:var(--ink)">Aktuálny ročník · 128 účastníkov</p></div><div class="conference-number">’27</div></div><div class="actions" style="margin-top:30px">${link("Spravovať ročník", "settings", "small dark")}${link("Prehľad", "organizer", "small flat")}</div></article><article class="card"><div class="card-top"><span class="eyebrow">APPLIED NATURAL SCIENCES</span>${badge("Archív")}</div><div class="conference-tile"><div><h2>ANS</h2><p>Predchádzajúci ročník</p></div><div class="conference-number muted">’25</div></div><div class="actions" style="margin-top:30px">${button("Otvoriť ukážku archívu", "archive", "small flat", "archive")}</div></article></div>`;
export const settings = (tab = 0) =>
  `${heading("SPRÁVA SYSTÉMU / ANS 2027", "Dobrý ročník začína detailmi.", "Nastavenia konferencie, termínov a účastníckych kategórií.")}${tabs(["Základné údaje", "Kategórie a poplatky", "Dôležité termíny", "Platobné údaje"], tab)}<div id="tab-content">${settingsTab(tab)}</div>`;
export const settingsTab = (tab) =>
  tab === 1
    ? panel(
        "Kategórie a účastnícke poplatky",
        `<form data-form="settings"><div class="three-grid">${["Rečník", "Autor posteru", "Pasívny účastník"].map((n, i) => `<div class="card ${i === 0 ? "lime" : ""}"><h3 style="margin-bottom:20px">${n}</h3>${field("Poplatok (€)", `price${i}`, [180, 120, 90][i], "number")}</div>`).join("")}</div>${formActions()}</form>`,
      )
    : tab === 2
      ? panel(
          "Dôležité termíny",
          `<form data-form="settings"><div class="form-grid">${field("Ukončenie registrácie", "registrationDeadline", "2027-04-30", "date")}${field("Odovzdanie abstraktov", "submissionDeadline", "2027-05-15", "date")}${field("Odovzdanie recenzií", "reviewDeadline", "2027-05-31", "date")}${field("Oznámenie rozhodnutí", "decisionDate", "2027-06-30", "date")}</div>${formActions()}</form>`,
        )
      : tab === 3
        ? panel(
            "Platobné inštrukcie",
            `${notice("Ide o návrh polí. Bankové údaje v prototype nie sú skutočné.", "yellow")}<form data-form="settings"><div class="form-grid">${field("Príjemca", "recipient", "UCM v Trnave", "text", "full")}<label class="field full">IBAN<input name="iban" placeholder="Platobné údaje doplní UCM"></label>${field("Splatnosť poplatku", "dueDate", "2027-04-30", "date")}</div>${formActions()}</form>`,
          )
        : panel(
            "Základné údaje ročníka",
            `<form data-form="settings"><div class="form-grid">${field("Názov konferencie", "conferenceName", "Applied Natural Sciences 2027", "text", "full")}${field("Skratka", "shortName", "ANS")}${field("Ročník", "year", "2027", "number")}${field("Začiatok", "startDate", "2027-09-15", "date")}${field("Koniec", "endDate", "2027-09-17", "date")}${field("Miesto konania", "venue", "Štrbské Pleso, Slovensko", "text", "full")}${area("Predstavenie konferencie", "description", "Priestor pre nové poznatky, odvážne otázky a stretnutia naprieč prírodnými vedami.", "full")}</div>${formActions()}</form>`,
          );
export const users = () =>
  `${heading("SPRÁVA SYSTÉMU / PRACOVNÍCI", "Správni ľudia na správnom mieste.", "Ukážka účtov, rolí a priradenia ku konferencii.", button("Pridať pracovníka", "add-user", "primary", "plus"))}<section class="panel">${searchBar("Vyhľadať pracovníka...", ["Všetky roly", "Organizátor", "Recenzent", "Programový výbor", "Ekonomické oddelenie", "Superadministrátor"])}${table(
    ["PRACOVNÍK", "ROLA", "KONTEXT", "STAV", ""],
    [
      ["Jana Kováčová", "Organizátor"],
      ["Peter Malík", "Recenzent"],
      ["Zuzana Veselá", "Programový výbor"],
      ["Mária Urbanová", "Ekonomické oddelenie"],
      ["Ondrej Hudec", "Superadministrátor"],
    ].map((u, i) => [
      person(u[0], `pracovnik${i}@example.com`),
      badge(u[1], i === 4 ? "green" : "blue"),
      i === 4 ? "Celý systém" : "ANS 2027",
      badge("Aktívny", "green"),
      button("Upraviť", `edit-user-${i}`, "small flat", "edit"),
    ]),
  )}</section>`;
export const audit = () =>
  `${heading("SPRÁVA SYSTÉMU / ZÁZNAM OPERÁCIÍ", "Každá dôležitá zmena má svoj príbeh.", "Prehľad administrátorských operácií v konferenčnom systéme.")}<section class="panel">${searchBar("Pracovník alebo operácia...", ["Všetky operácie", "Platba", "Recenzia", "Registrácia", "Program"])}${table(
    ["DÁTUM A ČAS", "PRACOVNÍK", "OPERÁCIA", "OBJEKT", "VÝSLEDOK"],
    [
      [
        "20. 04. 2027 · 14:32",
        "Mária Urbanová",
        "Platba zaevidovaná",
        "ANS-2027-0042",
      ],
      [
        "20. 04. 2027 · 13:18",
        "Jana Kováčová",
        "Recenzia – pridelenie recenzenta",
        "ANS-027",
      ],
      [
        "20. 04. 2027 · 11:40",
        "Zuzana Veselá",
        "Rozhodnutie výboru",
        "ANS-028",
      ],
      [
        "20. 04. 2027 · 10:12",
        "Jana Kováčová",
        "Registrácia – úprava kategórie",
        "ANS-2027-0048",
      ],
      [
        "19. 04. 2027 · 16:03",
        "Ondrej Hudec",
        "Program – zmena termínu",
        "ANS 2027",
      ],
    ].map((r) => [...r, badge("Úspešné", "green")]),
  )}</section>`;
