import * as ui from "./ui.js";
import * as pub from "./public-pages.js";
import * as account from "./account-pages.js";
import * as work from "./workspace-pages.js";
const {
  icon,
  esc,
  link,
  button,
  field,
  select,
  area,
  notice,
  badge,
  table,
  people,
  submissions,
} = ui;
export const screens = [
  ["V01", "home", "O konferencii", "public", "globe"],
  ["V02", "program", "Program konferencie", "public", "calendar"],
  ["V03", "information", "Praktické informácie", "public", "pin"],
  ["V04", "privacy", "Ochrana údajov", "public", "shield"],
  ["A01", "login", "Prihlásenie", "public", "logout"],
  ["A02", "register", "Registrácia", "public", "users"],
  ["A03", "forgot-password", "Zabudnuté heslo", "public", "help"],
  ["A04", "reset-password", "Obnova hesla", "public", "shield"],
  ["U01", "account", "Môj prehľad", "participant", "grid"],
  ["U02", "profile", "Môj profil", "participant", "users"],
  ["U03", "participation", "Moja účasť a platba", "participant", "coin"],
  ["U04", "submissions", "Moje príspevky", "participant", "file"],
  ["U05", "submission-new", "Nový príspevok", "participant", "plus"],
  ["U06", "submission-detail", "Detail príspevku", "participant", "file"],
  ["O01", "organizer", "Prehľad ročníka", "organizer", "grid"],
  ["O02", "registrations", "Účastníci", "organizer", "users"],
  ["O03", "registration-detail", "Detail registrácie", "organizer", "users"],
  ["O04", "admin-submissions", "Príspevky", "organizer", "file"],
  ["O05", "admin-submission", "Kontrola príspevku", "organizer", "review"],
  ["O06", "admin-reviews", "Priebeh recenzií", "organizer", "review"],
  ["O07", "admin-program", "Editor programu", "organizer", "calendar"],
  ["O08", "messages", "Komunikácia", "organizer", "mail"],
  ["O09", "exports", "Exporty a listiny", "organizer", "download"],
  ["R01", "reviews", "Moje recenzie", "reviewer", "review"],
  ["R02", "review-detail", "Hodnotenie príspevku", "reviewer", "edit"],
  ["C01", "decision", "Odborné rozhodnutie", "committee", "review"],
  ["C02", "committee-program", "Schválenie programu", "committee", "calendar"],
  [
    "—",
    "committee-submissions",
    "Príspevky na rozhodnutie",
    "committee",
    "file",
  ],
  ["E01", "payments", "Platby", "finance", "coin"],
  ["E02", "payment-detail", "Detail úhrady", "finance", "coin"],
  ["—", "finance-export", "Ekonomický export", "finance", "download"],
  ["S01", "conferences", "Konferencie a ročníky", "superadmin", "archive"],
  ["S02", "settings", "Nastavenia ročníka", "superadmin", "settings"],
  ["S03", "users", "Pracovníci a oprávnenia", "superadmin", "users"],
  ["S04", "audit", "Záznam operácií", "superadmin", "clock"],
];
const roles = {
  public: ["Verejnosť", "home", "Verejné rozhranie"],
  participant: ["Účastník", "account", "Účastnícka zóna"],
  organizer: ["Organizátor", "organizer", "Organizácia konferencie"],
  reviewer: ["Recenzent", "reviews", "Odborné recenzie"],
  committee: ["Programový výbor", "committee-submissions", "Programový výbor"],
  finance: ["Ekonomické oddelenie", "payments", "Ekonomické oddelenie"],
  superadmin: ["Superadministrátor", "conferences", "Správa systému"],
};
let route = "home",
  query = new URLSearchParams(),
  role = "public",
  registerStep = 0,
  registration = {},
  tab = 0,
  day = 0,
  toastTimer,
  savedForms = {},
  savedSubmission = null,
  paymentOverrides = {},
  exportRows = [],
  exportHeaders = [];
const navRoutes = {
  participant: ["account", "participation", "submissions", "profile"],
  organizer: [
    "organizer",
    "registrations",
    "admin-submissions",
    "admin-reviews",
    "admin-program",
    "messages",
    "exports",
  ],
  reviewer: ["reviews"],
  committee: ["committee-submissions", "committee-program"],
  finance: ["payments", "finance-export"],
  superadmin: ["conferences", "settings", "users", "audit"],
};
const id = () => Math.max(0, Number(query.get("id")) || 0);
function content() {
  switch (route) {
    case "home":
      return pub.publicHome();
    case "program":
      return pub.publicProgram();
    case "information":
      return pub.information();
    case "privacy":
      return pub.privacy();
    case "login":
    case "forgot-password":
    case "reset-password":
      return pub.auth(route);
    case "register":
      return pub.register(registerStep, registration);
    case "account":
      return account.accountHome();
    case "profile":
      return account.profile();
    case "participation":
      return account.participation(!registration.email);
    case "submissions":
      return account.mySubmissions();
    case "submission-new":
      return account.newSubmission();
    case "submission-detail":
      return account.submissionDetail(tab);
    case "organizer":
      return work.organizerHome();
    case "registrations":
      return work.registrations();
    case "registration-detail":
      return work.registrationDetail(id());
    case "admin-submissions":
      return work.adminSubmissions();
    case "admin-submission":
      return work.adminSubmission(id());
    case "admin-reviews":
      return work.adminReviews();
    case "admin-program":
      return work.programEditor(false, day);
    case "reviews":
      return work.reviewList();
    case "review-detail":
      return work.reviewDetail(id());
    case "committee-submissions":
      return work.adminSubmissions(true);
    case "decision":
      return work.decision(id());
    case "committee-program":
      return work.programEditor(true, day);
    case "payments":
      return work.payments();
    case "payment-detail":
      return work.paymentDetail(id(), paymentOverrides[id()] ?? false);
    case "messages":
      return work.messages();
    case "exports":
      return work.exportsPage();
    case "finance-export":
      return work.exportsPage(true);
    case "conferences":
      return work.conferences();
    case "settings":
      return work.settings(tab);
    case "users":
      return work.users();
    case "audit":
      return work.audit();
    default:
      return `<div class="empty"><h1>Táto stránka tu nie je.</h1><p>Vyberte si obrazovku z mapy prototypu.</p><div style="margin-top:24px">${link("Späť na úvod", "home")}</div></div>`;
  }
}
const brand = () =>
  `<a class="brand" href="#/home" aria-label="UCM Conferences – úvod"><span class="brand-mark">u</span><span>UCM<small>CONFERENCES</small></span></a>`;
const prototype = () =>
  `<div class="prototype"><div class="prototype-left"><span class="dot"></span><span>DIZAJNOVÝ PROTOTYP <span class="prototype-detail">/ Vzorové údaje · ANS 2027</span></span></div><div class="prototype-tools"><label><span class="sr-only">Rozhranie</span><select id="role-switch" aria-label="Prepnúť rozhranie">${Object.entries(
    roles,
  )
    .map(
      ([key, r]) =>
        `<option value="${key}" ${role === key ? "selected" : ""}>${r[0]}</option>`,
    )
    .join(
      "",
    )}</select></label><button data-action="screen-map">${icon("grid")}Mapa obrazoviek</button></div></div>`;
function publicLayout(body) {
  return `${prototype()}<header class="public-header">${brand()}<nav class="public-nav" aria-label="Hlavná navigácia">${[
    ["O konferencii", "home"],
    ["Program", "program"],
    ["Praktické informácie", "information"],
  ]
    .map(
      ([label, r]) =>
        `<a href="#/${r}" class="${route === r ? "active" : ""}" ${route === r ? 'aria-current="page"' : ""}>${label}</a>`,
    )
    .join(
      "",
    )}<a href="#/login">Prihlásenie</a>${link("Registrácia", "register", "lime", "arrow")}</nav></header><main id="main" tabindex="-1" class="public-main">${body}</main><footer class="public-footer">${brand()}<span>Veda. Ľudia. Nové spojenia.</span><a href="#/privacy">Informácie o spracúvaní údajov ↗</a><span>© 2027 · Vizuálna ukážka</span></footer>`;
}
function activeNav(r) {
  if (route === r) return true;
  return (
    {
      "registration-detail": "registrations",
      "admin-submission": "admin-submissions",
      "submission-new": "submissions",
      "submission-detail": "submissions",
      "review-detail": "reviews",
      decision: "committee-submissions",
      "payment-detail": "payments",
    }[route] === r
  );
}
function workspaceLayout(body) {
  const user =
    role === "participant"
      ? ["Martin Novák", "MN"]
      : role === "reviewer"
        ? ["Peter Malík", "PM"]
        : role === "finance"
          ? ["Mária Urbanová", "MU"]
          : ["Jana Kováčová", "JK"];
  return `${prototype()}<div class="workspace"><aside class="sidebar">${brand()}<a class="edition-card" href="#/${roles[role][1]}"><div class="eyebrow">AKTUÁLNY ROČNÍK</div><div class="edition-title">ANS 2027 ${icon("down")}</div><small class="muted" style="font-size:9px">Applied Natural Sciences</small></a><nav aria-label="Pracovná navigácia"><div class="nav-label">${roles[role][2].toUpperCase()}</div>${navRoutes[
    role
  ]
    .map((r) => {
      const s = screens.find((s) => s[1] === r);
      return `<a href="#/${r}" class="${activeNav(r) ? "active" : ""}" ${activeNav(r) ? 'aria-current="page"' : ""}>${icon(s[4])}${s[2]}${r === "admin-reviews" ? '<span class="nav-count">22</span>' : ""}</a>`;
    })
    .join(
      "",
    )}</nav><div class="sidebar-bottom"><div class="help-box"><strong>Potrebujete sa zorientovať?</strong><p>Objavte všetky obrazovky a používateľské rozhrania.</p><button type="button" class="link" style="border:0;background:none;padding:0;font-size:11px" data-action="screen-map">Mapa prototypu ${icon("arrow")}</button></div><div class="user"><span class="avatar">${user[1]}</span><div>${user[0]}<small>${roles[role][0]}</small></div><a style="margin-left:auto" href="#/home" aria-label="Vrátiť sa na verejný web">${icon("logout")}</a></div></div></aside><div class="workspace-body"><header class="workspace-header"><div class="header-left"><button class="icon-btn mobile-menu" aria-label="Otvoriť navigáciu" aria-expanded="false" data-action="menu">${icon("menu")}</button><span>Pracovný priestor</span><span>ANS 2027</span></div><div class="header-tools"><a class="view-public link" href="#/home" style="font-size:11px">Verejný web ${icon("external")}</a><button class="icon-btn" data-action="notifications" aria-label="Oznámenia">${icon("bell")}</button><span class="avatar">${user[1]}</span></div></header><main class="workspace-main" id="main" tabindex="-1">${body}<footer class="workspace-footer"><span>UCM CONFERENCES / ANS 2027</span><span>Dizajnový prototyp · ukážkové údaje</span></footer></main></div></div>`;
}
function render(reset = false) {
  if (reset) {
    const parts = location.hash.replace(/^#\/?/, "").split("?");
    route = parts[0] || "home";
    query = new URLSearchParams(parts[1] || "");
    tab = 0;
    day = 0;
    role = screens.find((s) => s[1] === route)?.[3] || "public";
  }
  document.title = `${screens.find((s) => s[1] === route)?.[2] || "Stránka nenájdená"} · ANS 2027 / UCM`;
  document.querySelector("#app").innerHTML =
    role === "public" ? publicLayout(content()) : workspaceLayout(content());
  for (const form of document.querySelectorAll("form[data-form]")) {
    const values = savedForms[`${route}-${tab}`];
    if (values)
      for (const [key, value] of Object.entries(values)) {
        const el = form.elements.namedItem(key);
        if (el && el.type !== "file" && el.type !== "password")
          el.value = value;
      }
  }
  if (savedSubmission && route === "submission-detail") {
    document.querySelector(".submission-title").textContent =
      savedSubmission.title;
    const prose = document.querySelector("#tab-content .prose");
    if (prose) prose.textContent = savedSubmission.abstract;
  }
  if (reset) {
    window.scrollTo({ top: 0 });
    document.querySelector("#main")?.focus({ preventScroll: true });
  }
}
function toast(message) {
  const el = document.querySelector("#toast");
  el.textContent = message;
  el.classList.add("visible");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove("visible"), 4500);
}
function modal(title, body, actions = "") {
  const d = document.querySelector("#action-dialog");
  d.innerHTML = `<div class="map-head"><h2 id="dialog-title">${title}</h2><button class="icon-btn" data-action="close-dialog" aria-label="Zavrieť">${icon("close")}</button></div>${body}${actions ? `<div class="form-actions">${actions}</div>` : ""}`;
  if (!d.open) d.showModal();
}
function closeModal() {
  document.querySelector("#action-dialog").close();
}
function map() {
  const d = document.querySelector("#screen-map");
  d.innerHTML = `<div class="map-head"><div><div class="eyebrow">UCM CONFERENCES / DIZAJNOVÝ PROTOTYP</div><h2 id="map-title" style="margin-top:12px">Jedna konferencia. Všetky pohľady.</h2><p>33 obrazoviek podľa analýzy + 2 pomocné zoznamy. Vyberte si, kam sa chcete pozrieť.</p></div><button class="icon-btn" data-action="close-map" aria-label="Zavrieť mapu">${icon("close")}</button></div><div class="map-grid">${Object.entries(
    roles,
  )
    .map(
      ([key, r]) =>
        `<section class="map-group"><h3>${r[0]}</h3>${screens
          .filter((s) => s[3] === key)
          .map(
            (s) =>
              `<a class="map-link" href="#/${s[1]}" data-map-link><span>${s[0]}</span>${s[2]}${icon("arrow")}</a>`,
          )
          .join("")}</section>`,
    )
    .join("")}</div>`;
  d.showModal();
}
function filterRows() {
  const search =
      document.querySelector("[data-search]")?.value.toLocaleLowerCase("sk") ||
      "",
    filter = document.querySelector("[data-filter]")?.value || "";
  let visible = 0;
  document.querySelectorAll("[data-search-row]").forEach((row) => {
    const text = row.textContent.toLocaleLowerCase("sk");
    const show =
      text.includes(search) &&
      (!filter ||
        filter.startsWith("Všetky") ||
        text.includes(filter.toLocaleLowerCase("sk")));
    row.hidden = !show;
    if (show) visible++;
  });
  const count = document.querySelector("[data-result-count]");
  if (count)
    count.textContent = `${visible} ${visible === 1 ? "záznam" : "záznamov"}`;
  let empty = document.querySelector("[data-filter-empty]");
  if (!empty) {
    empty = document.createElement("p");
    empty.className = "empty";
    empty.dataset.filterEmpty = "";
    empty.textContent = "Pre tento výber sa nenašli žiadne záznamy.";
    document.querySelector(".table-wrap")?.after(empty);
  }
  empty.hidden = visible > 0;
}
function genericForm(title, body, key, submit = "Uložiť ukážku") {
  modal(
    title,
    `<form data-form="${key}">${body}<div class="form-actions"><button class="btn primary">${submit}${icon("check")}</button></div></form>`,
  );
}
function downloadCsv() {
  const csv =
    "\uFEFF" +
    [exportHeaders, ...exportRows]
      .map((r) =>
        r.map((v) => '"' + String(v).replaceAll('"', '""') + '"').join(";"),
      )
      .join("\r\n");
  const url = URL.createObjectURL(
    new Blob([csv], { type: "text/csv;charset=utf-8" }),
  );
  const a = document.createElement("a");
  a.href = url;
  a.download = "ANS-2027-ukazkovy-export.csv";
  a.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
  toast("Ukážkový CSV export bol pripravený na stiahnutie.");
}
document.addEventListener("click", async (e) => {
  if (e.target.closest(".skip")) {
    e.preventDefault();
    document.querySelector("#main")?.focus();
    return;
  }
  const mapLink = e.target.closest("[data-map-link]");
  if (mapLink) document.querySelector("#screen-map").close();
  const tabButton = e.target.closest("[data-tab]");
  if (tabButton) {
    const form = document.querySelector("#tab-content form");
    if (form)
      savedForms[`${route}-${tab}`] = Object.fromEntries(new FormData(form));
    tab = Number(tabButton.dataset.tab);
    render();
    return;
  }
  const dayButton = e.target.closest("[data-day]");
  if (dayButton) {
    day = Number(dayButton.dataset.day);
    document.querySelector("#schedule-container").innerHTML = pub.schedule(
      route === "admin-program",
      day,
    );
    return;
  }
  const action = e.target.closest("[data-action]")?.dataset.action;
  if (!action) return;
  switch (action) {
    case "screen-map":
      map();
      break;
    case "close-map":
      document.querySelector("#screen-map").close();
      break;
    case "close-dialog":
      closeModal();
      break;
    case "menu": {
      const open = document
        .querySelector(".workspace")
        .classList.toggle("menu-open");
      e.target.closest("button").setAttribute("aria-expanded", open);
      break;
    }
    case "notifications":
      modal(
        "Najnovšie oznámenia",
        `<div class="task-row">${icon("file")}Príspevok ANS-027 je v recenznom konaní.</div><div class="task-row">${icon("check")}Platba bola zaevidovaná.</div><div class="task-row">${icon("calendar")}Odovzdanie abstraktov: 15. máj 2027.</div>`,
      );
      break;
    case "register-back": {
      const f = document.querySelector('[data-form="register"]');
      if (f)
        for (const [k, v] of new FormData(f))
          if (!k.toLowerCase().includes("password")) registration[k] = v;
      registerStep = Math.max(0, registerStep - 1);
      render();
      break;
    }
    case "copy-vs":
      try {
        await navigator.clipboard.writeText("20270042");
        toast("Variabilný symbol 20270042 bol skopírovaný.");
      } catch {
        modal("Variabilný symbol", "<p>20270042</p>");
      }
      break;
    case "file":
      modal(
        "Náhľad ukážkového súboru",
        `<div class="card"><div class="eyebrow">ANS-027 / ABSTRAKT</div><h3 style="margin:18px 0">${ui.submissionTitle}</h3><p class="prose">${ui.abstract}</p></div><p class="muted" style="font-size:11px;margin-top:18px">Textová ukážka náhľadu. Skutočný PDF súbor nie je pripojený.</p>`,
      );
      break;
    case "add-author": {
      const n = document.querySelectorAll("#authors .form-grid").length;
      document
        .querySelector("#authors")
        .insertAdjacentHTML(
          "beforeend",
          `<div class="form-grid" style="margin-top:18px">${field("Meno spoluautora", `coauthor${n}`)}${field("Pracovisko", `coauthorOrganization${n}`)}</div>`,
        );
      break;
    }
    case "save-draft": {
      const f = document.querySelector('[data-form="submission"]');
      savedForms[`${route}-${tab}`] = Object.fromEntries(new FormData(f));
      toast("Koncept je uložený v pamäti tejto ukážky.");
      break;
    }
    case "save-review": {
      const f = document.querySelector('[data-form="review"]');
      savedForms[`${route}-${tab}`] = Object.fromEntries(new FormData(f));
      toast("Koncept recenzie je uložený v tejto ukážke.");
      break;
    }
    case "assign-reviewer":
      genericForm(
        "Prideliť recenzenta",
        `${select("Recenzent", "reviewer", ["Peter Malík", "Zuzana Veselá", "Milan Tóth"])}<div style="margin-top:18px">${field("Termín posudku", "deadline", "2027-05-31", "date")}</div>`,
        "assign",
      );
      break;
    case "billing":
      genericForm(
        "Fakturačné a organizačné údaje",
        `${field("Organizácia", "organization", "UCM v Trnave")}<div style="margin-top:18px">${field("Mesto", "city", "Trnava")}</div>`,
        "billing",
      );
      break;
    case "add-session":
      genericForm(
        "Nový programový blok",
        `${field("Názov bloku", "sessionTitle")}<div class="form-grid" style="margin-top:18px">${field("Začiatok", "time", "09:00", "time")}${field("Miestnosť", "room", "Sála A")}</div>`,
        "session",
      );
      break;
    case "submit-program":
    case "approve-program":
    case "return-program":
      modal(
        action === "approve-program"
          ? "Schváliť pracovný návrh?"
          : action === "submit-program"
            ? "Predložiť program výboru?"
            : "Vrátiť program na úpravu?",
        notice(
          "Táto akcia iba zobrazí výsledok v prototype. Verejný program sa nezmení.",
        ),
        button("Potvrdiť ukážku", "confirm-program", "primary", "check"),
      );
      break;
    case "confirm-program":
      closeModal();
      toast("Ukážkový stav programu bol potvrdený.");
      break;
    case "new-edition":
      genericForm(
        "Nový ročník konferencie",
        `${field("Názov konferencie", "name", "Applied Natural Sciences")}<div style="margin-top:18px">${field("Ročník", "year", "2029", "number")}</div>`,
        "edition",
      );
      break;
    case "archive":
      modal(
        "ANS 2025 / Archív",
        `<div class="card lavender"><span class="eyebrow">UKÁŽKA ARCHÍVNEHO ROČNÍKA</span><h2 style="margin:20px 0">Applied Natural Sciences 2025</h2><p>Archívny ročník by tu sprístupňoval údaje a program na čítanie. Historické údaje do prototypu zatiaľ nie sú vložené.</p></div>`,
      );
      break;
    case "add-user":
      genericForm(
        "Nový pracovník",
        `${field("Meno a priezvisko", "name")}<div style="margin-top:18px">${field("E-mail", "email", "", "email")}</div><div style="margin-top:18px">${select("Rola pre ANS 2027", "role", ["Organizátor", "Recenzent", "Programový výbor", "Ekonomické oddelenie", "Superadministrátor"])}</div>`,
        "user",
      );
      break;
    case "confirm-message":
      closeModal();
      toast("Simulácia dokončená. Žiadna správa nebola odoslaná.");
      break;
    case "download-csv":
      downloadCsv();
      break;
    default:
      if (action.startsWith("session-"))
        genericForm(
          "Upraviť programový blok",
          `${field("Názov bloku", "sessionTitle", e.target.closest(".schedule-row")?.querySelector("h3")?.textContent || "Odborný blok")}<div style="margin-top:18px">${field("Začiatok", "time", "10:00", "time")}</div>`,
          "session",
        );
      else if (action.startsWith("edit-user-"))
        genericForm(
          "Úprava pracovníka",
          `${select("Rola pre ANS 2027", "role", ["Organizátor", "Recenzent", "Programový výbor", "Ekonomické oddelenie", "Superadministrátor"])}`,
          "user",
        );
      break;
  }
});
document.addEventListener("change", (e) => {
  if (e.target.id === "role-switch") {
    location.hash = "/" + roles[e.target.value][1];
  }
  if (e.target.matches("[data-filter]")) filterRows();
  if (e.target.type === "file") {
    const label = document.querySelector("[data-file-label]");
    if (label)
      label.textContent = e.target.files[0]
        ? `Vybrané: ${e.target.files[0].name}`
        : "";
  }
  if (e.target.name === "recipients") {
    const counts = {
      "Všetci účastníci": 128,
      Rečníci: 64,
      "Autori posterov": 38,
      "Účastníci s neuhradeným poplatkom": 32,
    };
    document.querySelector("[data-recipient-count]").textContent =
      counts[e.target.value];
  }
});
document.addEventListener("input", (e) => {
  if (e.target.matches("[data-search]")) filterRows();
  if (e.target.name === "passwordConfirmation") e.target.setCustomValidity("");
});
document.addEventListener("submit", (e) => {
  const form = e.target;
  if (!form.matches("[data-form]")) return;
  e.preventDefault();
  const type = form.dataset.form,
    data = Object.fromEntries(new FormData(form));
  if (
    data.passwordConfirmation !== undefined &&
    data.password !== data.passwordConfirmation
  ) {
    form.elements.passwordConfirmation.setCustomValidity("Heslá sa nezhodujú.");
    form.elements.passwordConfirmation.reportValidity();
    return;
  }
  if (type === "register") {
    for (const [k, v] of Object.entries(data))
      if (!k.toLowerCase().includes("password")) registration[k] = v;
    if (registerStep < 2) {
      registerStep++;
      render();
      window.scrollTo({ top: 0 });
    } else {
      registerStep = 0;
      location.hash = "/participation";
      toast("Ukážková registrácia je hotová. Platobné údaje sú ilustračné.");
    }
    return;
  }
  if (type === "login") {
    form.reset();
    location.hash = "/account";
    toast("Otvorený ukážkový účet Martina Nováka.");
    return;
  }
  if (type === "forgot-password") {
    modal(
      "Ukážka potvrdenia",
      `<p>Ak je e-mail priradený k účtu, používateľ dostane pokyny na obnovu prístupu. V tomto prototype sa e-mail neposiela.</p><div style="margin-top:24px">${link("Pozrieť obnovu hesla", "reset-password", "primary")}</div>`,
    );
    return;
  }
  if (type === "reset-password") {
    form.reset();
    location.hash = "/login";
    toast("Ukážka obnovy je dokončená. Žiadne heslo sa neuložilo.");
    return;
  }
  if (type === "submission") {
    savedSubmission = { title: data.title, abstract: data.abstract };
    savedForms[`${route}-${tab}`] = Object.fromEntries(new FormData(form));
    location.hash = "/submission-detail";
    toast("Príspevok bol odovzdaný v lokálnej ukážke.");
    return;
  }
  if (type === "review") {
    modal(
      "Recenzia pripravená",
      `<p>Odporúčanie: <strong>${esc(data.recommendation)}</strong></p><p style="margin-top:14px">${esc(data.comments)}</p><p class="muted" style="margin-top:20px;font-size:11px">Ukážka odovzdania. Údaje sa nikam neposielajú.</p>`,
    );
    return;
  }
  if (type === "payment") {
    if (Number(data.amount) <= 0) {
      toast("Zadajte kladnú prijatú sumu.");
      return;
    }
    paymentOverrides[id()] = Number(data.amount);
    render();
    toast(`V ukážke je zaevidovaných ${data.amount} €. E-mail sa neposiela.`);
    return;
  }
  if (type === "message") {
    modal(
      "Náhľad správy",
      `${badge(data.recipients, "blue")}<h3 style="margin:20px 0">${esc(data.subject)}</h3><p class="prose" style="white-space:pre-wrap">${esc(data.body)}</p>`,
      button("Simulovať odoslanie", "confirm-message", "primary", "mail"),
    );
    return;
  }
  if (type === "export") {
    const finance = route === "finance-export";
    let rows = people
      .filter(
        (p) =>
          data.category === "Všetky kategórie" ||
          {
            Rečníci: "Rečník",
            "Autori posterov": "Autor posteru",
            "Pasívni účastníci": "Pasívny účastník",
          }[data.category] === p[2],
      )
      .filter(
        (p) =>
          data.paymentStatus === "Všetky stavy" || data.paymentStatus === p[3],
      );
    exportHeaders = finance
      ? [
          "Meno a priezvisko",
          "Organizácia",
          "Variabilný symbol",
          "Predpis a úhrada",
        ]
      : [
          "Meno a priezvisko",
          "Organizácia",
          "Kategória účasti",
          "Stav registrácie",
        ];
    exportRows = rows.map((p) =>
      finance
        ? [p[0], p[1], `202700${42 + people.indexOf(p)}`, p[3]]
        : [p[0], p[1], p[2], "Potvrdená"],
    );
    if (data.exportType === "Zoznam príspevkov") {
      exportHeaders = ["ID", "Názov", "Autor", "Stav"];
      exportRows = submissions.map((s) => [s[0], s[1], s[2], s[4]]);
    } else {
      const checked = new FormData(form).getAll("columns");
      const selected = exportHeaders
        .map((h, i) => (checked.includes(h) ? i : -1))
        .filter((i) => i >= 0);
      if (!selected.length) {
        toast("Vyberte aspoň jeden stĺpec.");
        return;
      }
      exportRows = exportRows.map((r) => selected.map((i) => r[i]));
      exportHeaders = selected.map((i) => exportHeaders[i]);
    }
    modal(
      "Náhľad ukážkového exportu",
      `<p class="muted" style="margin-bottom:20px;font-size:12px">${esc(data.exportType)} · ${exportRows.length} ukážkových záznamov · CSV</p>${table(exportHeaders, exportRows)}`,
      button("Stiahnuť ukážkové CSV", "download-csv", "primary", "download"),
    );
    return;
  }
  savedForms[`${route}-${tab}`] = data;
  if (form.closest("dialog")) closeModal();
  toast(
    type === "decision"
      ? "Ukážkové rozhodnutie je pripravené. Autorovi sa nič neposiela."
      : "Zmeny sú potvrdené v tejto ukážke. Backend nie je pripojený.",
  );
});
for (const d of document.querySelectorAll("dialog")) {
  d.addEventListener("click", (e) => {
    if (e.target === d) {
      const r = d.getBoundingClientRect();
      if (
        e.clientX < r.left ||
        e.clientX > r.right ||
        e.clientY < r.top ||
        e.clientY > r.bottom
      )
        d.close();
    }
  });
  d.addEventListener("click", (e) => {
    if (e.target.closest('a[href^="#/"]')) d.close();
  });
}
window.addEventListener("hashchange", () => {
  for (const d of document.querySelectorAll("dialog[open]")) d.close();
  render(true);
});
render(true);
