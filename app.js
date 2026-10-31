import * as ui from "./ui.js";
import { screens, roles, pagePaths, routeHref } from "./routes.js";
const { icon, esc, link, button, field, select, notice, badge, table, people, submissions } = ui;
const route = document.body.dataset.screen;
const query = new URLSearchParams(location.search);
let registerStep = 0,
  registration = {},
  tab = 0,
  day = 0,
  toastTimer,
  savedForms = {},
  savedSubmission = null,
  paymentOverrides = {},
  exportRows = [],
  exportHeaders = [];
const id = () => {
  const value = Number(query.get("id"));
  return Number.isInteger(value) && document.getElementById(`record-${value}`) ? value : 0;
};
const storageKey = "ucm-prototype-state-v1";
let baseContent,
  navigating = false;

function formValues(form) {
  return Object.fromEntries(
    [...new FormData(form)].filter(([name]) => {
      const field = form.elements.namedItem(name);
      return field?.type !== "password" && field?.type !== "file";
    }),
  );
}

// Only mock form values are carried between separate HTML pages in this tab.
// Passwords and local file selections never enter browser storage.
function remember() {
  try {
    sessionStorage.setItem(
      storageKey,
      JSON.stringify({ registration, savedForms, savedSubmission, paymentOverrides }),
    );
  } catch {
    /* The prototype also works when browser storage is disabled. */
  }
}
function navigate(target) {
  remember();
  navigating = true;
  location.href = routeHref(target);
}
function mountTemplate(target, name) {
  const template = document.getElementById(name);
  if (!template) return;
  document.querySelector(target).replaceChildren(template.content.cloneNode(true));
}
function restoreForm(form, values) {
  if (!values) return;
  for (const [key, value] of Object.entries(values)) {
    const el = form.elements.namedItem(key);
    if (!el || el.type === "file" || el.type === "password") continue;
    if (el instanceof RadioNodeList) el.value = value;
    else if (el.type === "checkbox") el.checked = Boolean(value);
    else el.value = value;
  }
}
function render() {
  if (route === "register") {
    mountTemplate("#screen-content", `register-step-${registerStep}`);
    restoreForm(document.querySelector('form[data-form="register"]'), registration);
    const summary = {
      ...registration,
      name: [registration.firstName, registration.lastName].filter(Boolean).join(" "),
      fee:
        (registration.category === "Autor posteru"
          ? "120"
          : registration.category === "Pasívny účastník"
            ? "90"
            : "180") + " €",
    };
    document
      .querySelectorAll("[data-registration]")
      .forEach((el) => (el.textContent = summary[el.dataset.registration] || "—"));
  } else if (route === "settings" || route === "submission-detail") {
    mountTemplate("#tab-content", `tab-${tab}`);
    document.querySelectorAll("[data-tab]").forEach((el) => {
      const active = Number(el.dataset.tab) === tab;
      el.classList.toggle("active", active);
      el.setAttribute("aria-selected", active);
    });
  } else if (route === "payment-detail") {
    document.querySelector("#screen-content").replaceChildren(baseContent.cloneNode(true));
    updatePayment();
  }
  for (const form of document.querySelectorAll("form[data-form]"))
    restoreForm(form, savedForms[`${route}-${tab}`]);
  if (savedSubmission && route === "submission-detail") {
    document.querySelector(".submission-title").textContent = savedSubmission.title;
    const prose = document.querySelector("#tab-content .prose");
    if (prose) prose.textContent = savedSubmission.abstract;
  }
}
function updatePayment() {
  const amount = paymentOverrides[id()];
  if (amount === undefined) return;
  const paid = amount >= 180;
  const card = document.querySelector("#screen-content .card.shadow");
  card.classList.toggle("lime", paid);
  card.classList.toggle("yellow", !paid);
  const badge = card.querySelector(".pill");
  badge.textContent = paid ? "Zaplatené" : "Čiastočne zaplatené";
  badge.className = "pill " + (paid ? "green" : "blue");
  const values = card.querySelectorAll("dd");
  const money = (n) => n.toFixed(2).replace(".", ",") + " €";
  values[0].textContent = money(amount);
  values[1].textContent = money(Math.max(0, 180 - amount));
  // Replace the illustrative received-payment row with this session's entry.
  const rows = document.querySelector("#screen-content tbody");
  while (rows.children.length > 1) rows.lastElementChild.remove();
  const tr = document.createElement("tr");
  const form = savedForms[`${route}-0`] || {};
  for (const value of [
    form.paymentDate || "2027-04-20",
    "Prijatá úhrada",
    money(amount),
    "Ekonomické oddelenie",
  ]) {
    const td = document.createElement("td");
    td.textContent = value;
    tr.append(td);
  }
  rows.append(tr);
}
function initialize() {
  // Preserve previously shared prototype links while using real HTML navigation.
  if (location.hash.startsWith("#/")) {
    const oldRoute = location.hash.slice(2);
    if (pagePaths[oldRoute.split("?")[0]]) {
      location.replace(routeHref(oldRoute));
      return;
    }
  }
  try {
    const state = JSON.parse(sessionStorage.getItem(storageKey) || "{}");
    registration = state.registration || {};
    savedForms = state.savedForms || {};
    savedSubmission = state.savedSubmission || null;
    paymentOverrides = state.paymentOverrides || {};
  } catch {
    /* Ignore malformed or unavailable prototype state. */
  }
  if (document.getElementById("record-0"))
    mountTemplate(
      "#screen-content",
      document.getElementById(`record-${id()}`) ? `record-${id()}` : "record-0",
    );
  if (route === "participation" && registration.email)
    mountTemplate("#screen-content", "participation-unpaid");
  baseContent = document.createDocumentFragment();
  document
    .querySelector("#screen-content")
    .childNodes.forEach((el) => baseContent.append(el.cloneNode(true)));
  render();
  try {
    const flash = sessionStorage.getItem("ucm-prototype-flash");
    if (flash) {
      sessionStorage.removeItem("ucm-prototype-flash");
      toast(flash);
    }
  } catch {
    /* Browser storage is optional. */
  }
}
document.addEventListener("click", (e) => {
  if (e.target.closest("a[href]") && !e.target.closest(".skip")) remember();
});
window.addEventListener("pagehide", remember);
window.addEventListener("pageshow", (event) => {
  if (event.persisted) initialize();
});
function toast(message) {
  if (navigating) {
    try {
      sessionStorage.setItem("ucm-prototype-flash", message);
    } catch {}
  }
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
              `<a class="map-link" href="${routeHref(s[1])}" data-map-link><span>${s[0]}</span>${s[2]}${icon("arrow")}</a>`,
          )
          .join("")}</section>`,
    )
    .join("")}</div>`;
  d.showModal();
}
function filterRows() {
  const search = document.querySelector("[data-search]")?.value.toLocaleLowerCase("sk") || "",
    filter = document.querySelector("[data-filter]")?.value || "";
  let visible = 0;
  document.querySelectorAll("[data-search-row]").forEach((row) => {
    const text = row.textContent.toLocaleLowerCase("sk");
    const show =
      text.includes(search) &&
      (!filter || filter.startsWith("Všetky") || text.includes(filter.toLocaleLowerCase("sk")));
    row.hidden = !show;
    if (show) visible++;
  });
  const count = document.querySelector("[data-result-count]");
  if (count) count.textContent = `${visible} ${visible === 1 ? "záznam" : "záznamov"}`;
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
      .map((r) => r.map((v) => '"' + String(v).replaceAll('"', '""') + '"').join(";"))
      .join("\r\n");
  const url = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8" }));
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
    if (form) savedForms[`${route}-${tab}`] = formValues(form);
    tab = Number(tabButton.dataset.tab);
    render();
    return;
  }
  const dayButton = e.target.closest("[data-day]");
  if (dayButton) {
    day = Number(dayButton.dataset.day);
    mountTemplate("#schedule-container", `schedule-${day}`);
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
      const open = document.querySelector(".workspace").classList.toggle("menu-open");
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
      savedForms[`${route}-${tab}`] = formValues(f);
      toast("Koncept je uložený v pamäti tejto ukážky.");
      break;
    }
    case "save-review": {
      const f = document.querySelector('[data-form="review"]');
      savedForms[`${route}-${tab}`] = formValues(f);
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
        notice("Táto akcia iba zobrazí výsledok v prototype. Verejný program sa nezmení."),
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
    navigate(roles[e.target.value][1]);
  }
  if (e.target.matches("[data-filter]")) filterRows();
  if (e.target.type === "file") {
    const label = document.querySelector("[data-file-label]");
    if (label) label.textContent = e.target.files[0] ? `Vybrané: ${e.target.files[0].name}` : "";
  }
  if (e.target.name === "recipients") {
    const counts = {
      "Všetci účastníci": 128,
      Rečníci: 64,
      "Autori posterov": 38,
      "Účastníci s neuhradeným poplatkom": 32,
    };
    document.querySelector("[data-recipient-count]").textContent = counts[e.target.value];
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
  if (data.passwordConfirmation !== undefined && data.password !== data.passwordConfirmation) {
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
      navigate("participation");
      toast("Ukážková registrácia je hotová. Platobné údaje sú ilustračné.");
    }
    return;
  }
  if (type === "login") {
    form.reset();
    navigate("account");
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
    navigate("login");
    toast("Ukážka obnovy je dokončená. Žiadne heslo sa neuložilo.");
    return;
  }
  if (type === "submission") {
    savedSubmission = { title: data.title, abstract: data.abstract };
    savedForms[`${route}-${tab}`] = formValues(form);
    navigate("submission-detail");
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
    if (!Number.isFinite(Number(data.amount)) || Number(data.amount) <= 0) {
      toast("Zadajte kladnú prijatú sumu.");
      return;
    }
    savedForms[`${route}-${tab}`] = formValues(form);
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
      .filter((p) => data.paymentStatus === "Všetky stavy" || data.paymentStatus === p[3]);
    exportHeaders = finance
      ? ["Meno a priezvisko", "Organizácia", "Variabilný symbol", "Predpis a úhrada"]
      : ["Meno a priezvisko", "Organizácia", "Kategória účasti", "Stav registrácie"];
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
  savedForms[`${route}-${tab}`] = formValues(form);
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
      if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom)
        d.close();
    }
  });
  d.addEventListener("click", (e) => {
    if (e.target.closest("a[href]")) d.close();
  });
}
initialize();
