const paths = {
  arrow: "M5 12h14m-5-5 5 5-5 5",
  external: "M8 5H5v14h14v-3M13 5h6v6M10 14l9-9",
  grid: "M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z",
  users:
    "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M16 3a4 4 0 0 1 0 8m6 10v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0",
  file: "M14 2H6v20h14V8zM14 2v6h6M9 13h8M9 17h6",
  check: "m5 12 4 4L19 6",
  calendar: "M4 5h16v16H4zM16 3v4M8 3v4M4 11h16",
  mail: "M3 5h18v14H3zM3 5l9 8 9-8",
  coin: "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0M15 8h-4a2 2 0 0 0 0 4h2a2 2 0 0 1 0 4H9M12 6v12",
  settings:
    "M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8M9 3h6l1 3 3 1 2 5-2 5-3 1-1 3H9l-1-3-3-1-2-5 2-5 3-1z",
  clock: "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0M12 7v5l3 2",
  bell: "M18 8a6 6 0 0 0-12 0v6l-2 3h16l-2-3zM10 21h4",
  search: "M16 10a6 6 0 1 1-12 0 6 6 0 0 1 12 0m-1 5 6 6",
  plus: "M12 5v14M5 12h14",
  download: "M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5",
  chevron: "m9 5 7 7-7 7",
  down: "m6 9 6 6 6-6",
  pin: "M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 0 1 14 0M15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0",
  logout: "M9 3H4v18h5M10 12h11m-5-5 5 5-5 5",
  shield: "M12 2 3 6v6c0 5 9 10 9 10s9-5 9-10V6zM8 12l3 3 5-6",
  copy: "M8 8h13v13H8zM16 8V3H3v13h5",
  upload: "M12 16V3m-5 5 5-5 5 5M4 16v5h16v-5",
  close: "m6 6 12 12M6 18 18 6",
  menu: "M3 6h18M3 12h18M3 18h18",
  info: "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0M12 11v6M12 7h.01",
  edit: "m16 3 5 5-12 12-6 1 1-6zM14 5l5 5",
  review: "M9 3H4v18h16V3h-5M9 2h6v4H9zM8 12l2 2 5-5M8 18h8",
  archive: "M3 3h18v5H3zM5 8v13h14V8M10 12h4",
  globe:
    "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0M3 12h18M12 3c5 5 5 13 0 18-5-5-5-13 0-18",
  help: "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0M9 9a3 3 0 1 1 4 3v2M12 18h.01",
};
export const icon = (name) =>
  `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="${paths[name] || paths.file}"/></svg>`;
export const esc = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
export const link = (label, route, kind = "primary", symbol = "arrow") =>
  `<a class="btn ${kind}" href="#/${route}">${label}${symbol ? icon(symbol) : ""}</a>`;
export const button = (label, action, kind = "", symbol = "") =>
  `<button type="button" class="btn ${kind}" data-action="${action}">${symbol ? icon(symbol) : ""}${label}</button>`;
export const badge = (label, color = "gray") =>
  `<span class="pill ${color}">${label}</span>`;
export const field = (label, name, value = "", type = "text", extra = "") =>
  `<label class="field ${extra}">${label}<input type="${type}" name="${name}" value="${esc(value)}" ${type === "email" ? 'autocomplete="email"' : ""} ${type === "password" ? 'minlength="8" autocomplete="new-password"' : ""} required></label>`;
export const select = (label, name, options, extra = "") =>
  `<label class="field ${extra}">${label}<select name="${name}">${options.map((o) => `<option>${o}</option>`).join("")}</select></label>`;
export const area = (label, name, value = "", extra = "") =>
  `<label class="field ${extra}">${label}<textarea name="${name}" required>${esc(value)}</textarea></label>`;
export const notice = (text, color = "") =>
  `<div class="notice ${color}">${icon("info")}<p>${text}</p></div>`;
export const panel = (title, content, action = "") =>
  `<section class="panel"><div class="panel-head"><h2>${title}</h2>${action}</div><div class="panel-body">${content}</div></section>`;
export const heading = (eyebrow, title, description, action = "") =>
  `<div class="page-heading"><div><div class="eyebrow">${eyebrow}</div><h1>${title}</h1><p>${description}</p></div><div class="actions">${action}</div></div>`;
export const stat = (label, value, foot, color = "", symbol = "grid") =>
  `<article class="card stat ${color}"><div class="stat-label">${label}${icon(symbol)}</div><div class="stat-value">${value}</div><div class="stat-foot">${foot}</div></article>`;
export const tabs = (names, active = 0) =>
  `<div class="tabs" role="tablist">${names.map((n, i) => `<button type="button" class="tab ${i === active ? "active" : ""}" role="tab" aria-selected="${i === active}" data-tab="${i}">${n}</button>`).join("")}</div>`;
export const formActions = (label = "Uložiť zmeny") =>
  `<div class="form-actions"><button class="btn primary" type="submit">${label}${icon("check")}</button></div>`;
export const table = (headers, rows, footer = "") =>
  `<div class="table-wrap"><table><thead><tr>${headers.map((h) => `<th scope="col">${h}</th>`).join("")}</tr></thead><tbody>${rows.map((r) => `<tr data-search-row>${r.map((c) => `<td>${c}</td>`).join("")}</tr>`).join("")}</tbody></table></div>${footer ? `<div class="table-footer">${footer}</div>` : ""}`;
export const searchBar = (
  placeholder = "Vyhľadať...",
  options = ["Všetky stavy", "Zaplatené", "Čaká na úhradu"],
) =>
  `<div class="toolbar"><div class="filters"><label class="search">${icon("search")}<input type="search" aria-label="${placeholder}" placeholder="${placeholder}" data-search></label><select aria-label="Filter podľa stavu" data-filter>${options.map((o) => `<option>${o}</option>`).join("")}</select></div><small class="muted" data-result-count>Ukážkové záznamy</small></div>`;
export const person = (name, sub = "", color = "") =>
  `<div class="table-person"><span class="avatar" style="${color ? `background:${color}` : ""}">${name
    .split(" ")
    .map((n) => n[0])
    .slice(0, 2)
    .join(
      "",
    )}</span><div><strong>${name}</strong><span class="secondary">${sub}</span></div></div>`;
export const rowLink = (route, label = "Otvoriť") =>
  `<a class="link" href="#/${route}">${label}${icon("arrow")}</a>`;
export const demoFile = (name = "ANS-027_abstrakt_v2.pdf") =>
  `<div class="file">${icon("file")}<div>${name}<small>PDF · 248 kB · verzia 2 · ukážkový súbor</small></div>${button("Náhľad", "file", "small flat", "external")}</div>`;
export const timeline = () =>
  `<ol class="timeline"><li><strong>Príspevok je v recenznom konaní</strong><small>20. 04. 2027 · 10:42</small>Čakáme na posudky odborných recenzentov.</li><li><strong>Formálna kontrola dokončená</strong><small>18. 04. 2027 · 14:15</small>Príspevok spĺňa formálne požiadavky.</li><li><strong>Príspevok bol odovzdaný</strong><small>16. 04. 2027 · 09:30</small>Abstrakt a príloha, verzia 1.</li></ol>`;
export const submissionTitle =
  "Využitie strojového učenia pri monitorovaní kvality vody";
export const abstract =
  "Kvalita vodných ekosystémov predstavuje jednu z kľúčových výziev environmentálneho výskumu. V príspevku predstavujeme prístup založený na metódach strojového učenia, ktorý umožňuje identifikovať zmeny vo vybraných ukazovateľoch kvality vody. Navrhnutý model kombinuje senzorické merania s historickými údajmi a poskytuje podklad pre včasné odhalenie environmentálnych rizík. Výsledky poukazujú na potenciál medziodborovej spolupráce informatiky a environmentálnych vied.";
export const submissions = [
  [
    "ANS-027",
    submissionTitle,
    "Martin Novák",
    "Prednáška",
    "V recenzii",
    "blue",
  ],
  [
    "ANS-028",
    "Biodegradácia polymérov v pôdnom prostredí",
    "Petra Kováčová",
    "Poster",
    "Prijaté",
    "green",
  ],
  [
    "ANS-029",
    "Nové prístupy v analytickej chémii",
    "Tomáš Horváth",
    "Prednáška",
    "Na kontrolu",
    "orange",
  ],
  [
    "ANS-030",
    "Biodiverzita mestských ekosystémov",
    "Lucia Vargová",
    "Poster",
    "Vyžiadané úpravy",
    "red",
  ],
  [
    "ANS-031",
    "Optimalizácia neurónových sietí pre malé dáta",
    "Adam Bielik",
    "Prednáška",
    "V recenzii",
    "blue",
  ],
  [
    "ANS-032",
    "Bioaktívne látky v liečivých rastlinách",
    "Eva Poláková",
    "Prednáška",
    "Prijaté",
    "green",
  ],
];
export const people = [
  ["Martin Novák", "UCM v Trnave", "Rečník", "Zaplatené", "green"],
  [
    "Petra Kováčová",
    "Univerzita Komenského",
    "Autor posteru",
    "Čaká na úhradu",
    "orange",
  ],
  ["Tomáš Horváth", "STU v Bratislave", "Rečník", "Zaplatené", "green"],
  [
    "Lucia Vargová",
    "Univerzita Mateja Bela",
    "Autor posteru",
    "Čiastočne zaplatené",
    "blue",
  ],
  [
    "Adam Bielik",
    "UCM v Trnave",
    "Pasívny účastník",
    "Čaká na úhradu",
    "orange",
  ],
  ["Eva Poláková", "SAV Bratislava", "Rečník", "Zaplatené", "green"],
  ["Daniel Král", "Masarykova univerzita", "Rečník", "Oslobodené", "gray"],
  ["Nina Urbanová", "UCM v Trnave", "Pasívny účastník", "Zaplatené", "green"],
];
