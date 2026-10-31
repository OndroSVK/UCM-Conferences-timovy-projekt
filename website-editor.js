import {
  defaultWebsite,
  loadWebsite,
  saveWebsite,
  loadAssets,
  saveAsset,
  removeAsset,
  seedAssets,
} from "./website-store.js";
import { buildWebsiteDocument, unknownTokens } from "./website-document.js";
import { esc } from "./ui.js";
const $ = (selector) => document.querySelector(selector);
const clone = (value) => structuredClone(value);
let state,
  draft,
  defaults,
  assets = new Map(),
  activeTab = "content",
  previewWidth = 1024;
let previewTimer,
  htmlSelection,
  busy = false;
const urls = new Set();
const stamp = (value) =>
  new Intl.DateTimeFormat("sk-SK", { dateStyle: "short", timeStyle: "short" }).format(
    new Date(value),
  );
const effectiveAssets = (snapshot = draft) =>
  new Map([...assets].map(([id, asset]) => [id, { ...asset, ...snapshot.assetMetadata?.[id] }]));
const isDirty = () => state && JSON.stringify(draft) !== JSON.stringify(state.draft);
function message(text, error = false) {
  $("#web-message").textContent = text;
  $("#web-message").classList.toggle("error", error);
}
function syncStatus() {
  const dirty = isDirty();
  $("#web-save-state").textContent = dirty
    ? "Neuložené zmeny"
    : state.savedAt
      ? "Koncept uložený"
      : "Nový koncept";
  $("#web-save-state").className = "pill " + (dirty ? "orange" : "green");
  $("#web-saved-at").textContent = state.savedAt
    ? "Uložené " + stamp(state.savedAt)
    : "ANS 2027 · pripravené na úpravu";
  const published = state.versions.find((v) => v.id === state.publishedId);
  $("#web-published-state").textContent = published
    ? "Publikované lokálne · " + stamp(published.createdAt)
    : "Zatiaľ bez publikovanej verzie";
  document.querySelector('[data-web-action="open-published"]').disabled = !published;
  $('[data-web-action="history"]').textContent = `História verzií (${state.versions.length})`;
}
function changed() {
  syncStatus();
  clearTimeout(previewTimer);
  previewTimer = setTimeout(renderPreview, 220);
}
function resizePreview() {
  const width = $("#web-preview-viewport").clientWidth - 32;
  if (width <= 0) return;
  const scale = Math.min(1, width / previewWidth);
  $("#web-preview-frame").style.width = previewWidth + "px";
  $("#web-preview-frame").style.transform = `scale(${scale})`;
  $("#web-preview-canvas").style.height = 900 * scale + "px";
  $("#web-preview-width").textContent = previewWidth + " px";
}
function renderPreview() {
  const map = effectiveAssets();
  $("#web-preview-frame").srcdoc = buildWebsiteDocument(draft, map);
  const unknown = unknownTokens(draft, map);
  const warning = $("#web-preview-warning");
  warning.textContent = unknown.length
    ? "Neznáme značky: " + unknown.map((t) => "{{" + t + "}}").join(", ")
    : "";
  warning.hidden = !unknown.length;
  resizePreview();
}
function highlight(type) {
  const text = $(`#web-${type}-editor`).value;
  const pattern =
    /<!--[\s\S]*?-->|\/\*[\s\S]*?\*\/|\{\{[^{}]*\}\}|<\/?[^>]*>|"[^"\n]*"|'[^'\n]*'|#[0-9a-fA-F]{3,8}\b/g;
  let html = "",
    last = 0;
  for (const match of text.matchAll(pattern)) {
    html += esc(text.slice(last, match.index));
    const token = match[0];
    const kind = /^(<!--|\/\*)/.test(token)
      ? "comment"
      : token.startsWith("{{")
        ? "token"
        : token.startsWith("<")
          ? "tag"
          : "value";
    html += `<span class="syntax-${kind}">${esc(token)}</span>`;
    last = match.index + token.length;
  }
  $(`#web-${type}-highlight`).innerHTML = html + esc(text.slice(last)) + "\n";
  $(`[data-line-count="${type}"]`).textContent = `${text.split("\n").length} riadkov`;
}
function showTab(name, focus = false) {
  activeTab = name;
  document.querySelectorAll("[data-web-tab]").forEach((el) => {
    const active = el.dataset.webTab === name;
    el.classList.toggle("active", active);
    el.setAttribute("aria-selected", active);
    el.tabIndex = active ? 0 : -1;
    if (active && focus) el.focus();
  });
  for (const tab of ["content", "html", "css", "media"])
    $(`#web-panel-${tab}`).hidden = tab !== name;
  if (name === "html" || name === "css") highlight(name);
}
function hydrate() {
  for (const [key, value] of Object.entries(draft.content)) {
    const field = $("#web-content-form").elements.namedItem(key);
    if (field) field.value = value;
  }
  $("#web-accent-value").textContent = draft.content.accent;
  for (const type of ["html", "css"]) {
    $(`#web-${type}-editor`).value = draft[type];
    highlight(type);
  }
  syncMediaSelectors();
  renderMedia();
  renderPreview();
  syncStatus();
}
function syncMediaSelectors() {
  const map = effectiveAssets();
  for (const select of document.querySelectorAll("[data-media-slot]")) {
    select.replaceChildren(new Option("Bez obrázka", ""));
    for (const [id, asset] of map) select.add(new Option(asset.name, id));
    select.value = draft.media[select.dataset.mediaSlot] || "";
  }
}
function usages(id) {
  const names = { logo: "logo", hero: "úvodný obrázok", background: "pozadie" };
  const list = Object.entries(draft.media)
    .filter(([, value]) => value === id)
    .map(([slot]) => names[slot]);
  if ((draft.html + draft.css).includes("{{media:" + id + "}}")) list.push("vložené v kóde");
  if (
    state.versions.some(
      (v) =>
        Object.values(v.snapshot.media).includes(id) ||
        (v.snapshot.html + v.snapshot.css).includes("{{media:" + id + "}}"),
    )
  )
    list.push("publikovaná verzia");
  // A saved draft also retains its assets until explicitly replaced by a new save.
  if (
    Object.values(state.draft.media).includes(id) ||
    (state.draft.html + state.draft.css).includes("{{media:" + id + "}}")
  )
    list.push("uložený koncept");
  return [...new Set(list)];
}
function renderMedia() {
  const grid = $("#web-media-grid");
  grid.replaceChildren();
  for (const [id, asset] of effectiveAssets()) {
    const card = $("#web-media-card").content.cloneNode(true);
    const article = card.querySelector("article");
    article.dataset.assetId = id;
    const image = card.querySelector("img");
    image.src = asset.url;
    image.alt = asset.alt;
    card.querySelector("[data-asset-name]").value = asset.name;
    card.querySelector("[data-asset-alt]").value = asset.alt;
    card.querySelector("[data-asset-size]").textContent =
      `${asset.width} × ${asset.height} px · ${asset.builtIn ? "súčasť ukážky" : Math.ceil(asset.blob.size / 1024) + " kB"} `;
    const used = usages(id);
    card.querySelector("[data-asset-usage]").textContent = used.length
      ? "Použité: " + used.join(", ")
      : "Zatiaľ bez použitia";
    const remove = card.querySelector("[data-asset-delete]");
    remove.disabled = asset.builtIn || used.length > 0;
    remove.title = asset.builtIn
      ? "Predvolený obrázok je súčasťou ukážky."
      : used.length
        ? "Používaný obrázok nemožno odstrániť."
        : "";
    grid.append(card);
  }
  $("#web-media-count").textContent = "Obrázky: " + assets.size;
}
function insertHtml(text) {
  showTab("html");
  const editor = $("#web-html-editor");
  const fallback = editor.value.lastIndexOf("</main>");
  const [start, end] = htmlSelection || [
    fallback < 0 ? editor.value.length : fallback,
    fallback < 0 ? editor.value.length : fallback,
  ];
  editor.setRangeText(text, start, end, "end");
  draft.html = editor.value;
  htmlSelection = [editor.selectionStart, editor.selectionEnd];
  editor.focus();
  highlight("html");
  changed();
}
function setBusy(value) {
  busy = value;
  for (const button of document.querySelectorAll("[data-web-action]")) button.disabled = value;
  if (!value) syncStatus();
}
function validateDraft() {
  if (!$("#web-content-form").checkValidity()) {
    showTab("content");
    $("#web-content-form").reportValidity();
    return false;
  }
  if (!draft.html.trim()) {
    showTab("html");
    message("HTML šablóna je prázdna.", true);
    return false;
  }
  const unknown = unknownTokens(draft, effectiveAssets());
  if (unknown.length) {
    message("Pred publikovaním opravte neznáme vkladacie značky.", true);
    return false;
  }
  return true;
}
async function saveDraft() {
  const next = { ...state, draft: clone(draft), savedAt: new Date().toISOString() };
  await saveWebsite(next);
  state = next;
  syncStatus();
  renderMedia();
}
async function publish() {
  if (!validateDraft()) return;
  const createdAt = new Date().toISOString();
  const version = {
    id: crypto.randomUUID(),
    number: state.versions.length + 1,
    createdAt,
    snapshot: clone(draft),
  };
  const next = {
    ...state,
    draft: clone(draft),
    savedAt: createdAt,
    publishedId: version.id,
    versions: [...state.versions, version],
  };
  await saveWebsite(next);
  state = next;
  syncStatus();
  renderMedia();
  $("#web-publish-dialog").close();
  message("Verzia " + version.number + " je publikovaná v lokálnej ukážke.");
}
function openHistory() {
  const list = $("#web-history-list");
  list.replaceChildren();
  if (!state.versions.length) {
    const p = document.createElement("p");
    p.className = "web-help";
    p.textContent =
      "Zatiaľ tu nie je publikovaná verzia. Prvú vytvoríte tlačidlom Publikovať ukážku.";
    list.append(p);
  }
  for (const version of [...state.versions].reverse()) {
    const row = $("#web-history-item").content.cloneNode(true);
    row.querySelector("[data-version-title]").textContent =
      `Verzia ${version.number}${version.id === state.publishedId ? " · aktuálne publikovaná" : ""}`;
    row.querySelector("[data-version-date]").textContent =
      stamp(version.createdAt) + " · " + version.snapshot.content.title;
    row.querySelector("[data-version-restore]").dataset.versionRestore = version.id;
    list.append(row);
  }
  $("#web-history-dialog").showModal();
}
async function openPreview(published = false) {
  const popup = window.open("about:blank", "_blank");
  if (!popup) {
    message("Prehliadač zablokoval nové okno. Povoľte otvorenie náhľadu.", true);
    return;
  }
  try {
    if (!published) await saveDraft();
    popup.location.href = new URL(
      `./public/website-preview.html?mode=${published ? "published" : "draft"}`,
      import.meta.url,
    ).href;
    popup.opener = null;
  } catch (error) {
    popup.close();
    throw error;
  }
}
async function prepareImage(file) {
  if (!["image/jpeg", "image/png", "image/webp"].includes(file.type))
    throw new Error(file.name + ": podporované sú JPG, PNG a WebP.");
  if (file.size > 10 * 1024 * 1024) throw new Error(file.name + ": obrázok presahuje 10 MB.");
  const bitmap = await createImageBitmap(file);
  try {
    const scale = Math.min(1, 2400 / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement("canvas");
    canvas.width = Math.max(1, Math.round(bitmap.width * scale));
    canvas.height = Math.max(1, Math.round(bitmap.height * scale));
    canvas.getContext("2d").drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, "image/webp", 0.86));
    if (!blob) throw new Error("Obrázok sa nepodarilo spracovať.");
    return {
      id: crypto.randomUUID(),
      name: file.name.replace(/\.[^.]+$/, ""),
      alt: "",
      blob,
      width: canvas.width,
      height: canvas.height,
    };
  } finally {
    bitmap.close();
  }
}
async function upload(files) {
  if (!draft || busy) return;
  setBusy(true);
  $("#web-upload-input").disabled = true;
  let count = 0;
  const errors = [];
  try {
    for (const file of files)
      try {
        message("Pripravujem obrázok: " + file.name);
        const asset = await prepareImage(file);
        await saveAsset(asset);
        const url = URL.createObjectURL(asset.blob);
        urls.add(url);
        assets.set(asset.id, { ...asset, url });
        count++;
      } catch (error) {
        errors.push(error.message || "Obrázok sa nepodarilo uložiť.");
      }
    renderMedia();
    syncMediaSelectors();
    renderPreview();
    message(
      [count ? `Lokálne uložené obrázky: ${count}.` : "", ...errors].filter(Boolean).join(" "),
      errors.length > 0,
    );
  } finally {
    setBusy(false);
    $("#web-upload-input").disabled = false;
    $("#web-upload-input").value = "";
  }
}
function failure(error) {
  message(
    "Zmenu sa nepodarilo uložiť. " +
      (error?.name === "QuotaExceededError"
        ? "Úložisko prehliadača je plné."
        : error.message || "Skúste to znovu."),
    true,
  );
}
document.addEventListener("input", (event) => {
  const field = event.target;
  if (field.closest("#web-content-form") && field.name) {
    draft.content[field.name] = field.value;
    if (field.name === "accent") $("#web-accent-value").textContent = field.value;
    changed();
  }
  if (field.dataset.code) {
    draft[field.dataset.code] = field.value;
    highlight(field.dataset.code);
    changed();
  }
  const card = field.closest("[data-asset-id]");
  if (card && (field.hasAttribute("data-asset-name") || field.hasAttribute("data-asset-alt"))) {
    draft.assetMetadata ??= {};
    draft.assetMetadata[card.dataset.assetId] = {
      ...(draft.assetMetadata[card.dataset.assetId] || {}),
    };
    draft.assetMetadata[card.dataset.assetId][
      field.hasAttribute("data-asset-name") ? "name" : "alt"
    ] = field.value;
    changed();
  }
});
document.addEventListener("change", (event) => {
  const field = event.target;
  if (field.matches("[data-media-slot]")) {
    draft.media[field.dataset.mediaSlot] = field.value;
    renderMedia();
    changed();
  }
  if (field.matches("[data-asset-slot]") && field.value) {
    draft.media[field.value] = field.closest("[data-asset-id]").dataset.assetId;
    field.value = "";
    syncMediaSelectors();
    renderMedia();
    changed();
  }
  if (field.id === "web-upload-input") upload([...field.files]);
  if (field.matches("[data-asset-name]")) syncMediaSelectors();
});
document.addEventListener("keydown", (event) => {
  const editor = event.target;
  if (editor.dataset.code && event.key === "Tab") {
    event.preventDefault();
    editor.setRangeText("  ", editor.selectionStart, editor.selectionEnd, "end");
    editor.dispatchEvent(new Event("input", { bubbles: true }));
  }
  if (
    editor.matches("[data-web-tab]") &&
    ["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)
  ) {
    event.preventDefault();
    const tabs = ["content", "html", "css", "media"];
    let i = tabs.indexOf(activeTab);
    i =
      event.key === "Home"
        ? 0
        : event.key === "End"
          ? 3
          : (i + (event.key === "ArrowRight" ? 1 : 3)) % 4;
    showTab(tabs[i], true);
  }
});
document.addEventListener("click", async (event) => {
  const target = event.target.closest("button");
  if (!target) return;
  if (target.dataset.webTab) {
    showTab(target.dataset.webTab);
    return;
  }
  if (!draft) return;
  if (target.dataset.webView) {
    $("#web-editor-layout").dataset.mobileView = target.dataset.webView;
    document.querySelectorAll("[data-web-view]").forEach((el) => {
      const active = el === target;
      el.classList.toggle("active", active);
      el.setAttribute("aria-pressed", active);
    });
    resizePreview();
    return;
  }
  if (target.dataset.previewWidth) {
    previewWidth = Number(target.dataset.previewWidth);
    document.querySelectorAll("[data-preview-width]").forEach((el) => {
      el.classList.toggle("active", el === target);
      el.setAttribute("aria-pressed", el === target);
    });
    resizePreview();
    return;
  }
  if (target.dataset.webClose) {
    $("#" + target.dataset.webClose).close();
    return;
  }
  if (target.dataset.insertToken) {
    insertHtml("{{" + target.dataset.insertToken + "}}");
    return;
  }
  if (target.dataset.versionRestore) {
    if (isDirty() && !window.confirm("Nahradiť neuložený koncept vybranou verziou?")) return;
    draft = clone(state.versions.find((v) => v.id === target.dataset.versionRestore).snapshot);
    hydrate();
    $("#web-history-dialog").close();
    message("Verzia bola obnovená do konceptu. Pre jej zverejnenie ju znovu publikujte.");
    return;
  }
  const card = target.closest("[data-asset-id]");
  if (card && target.hasAttribute("data-asset-insert")) {
    const asset = effectiveAssets().get(card.dataset.assetId);
    insertHtml(`\n<img src="{{media:${asset.id}}}" alt="${esc(asset.alt)}" />\n`);
    return;
  }
  if (card && target.hasAttribute("data-asset-delete")) {
    const id = card.dataset.assetId;
    if (usages(id).length || assets.get(id)?.builtIn) return;
    if (!window.confirm("Odstrániť nepoužívaný obrázok z lokálnej knižnice?")) return;
    try {
      await removeAsset(id);
      const asset = assets.get(id);
      URL.revokeObjectURL(asset.url);
      urls.delete(asset.url);
      assets.delete(id);
      renderMedia();
      syncMediaSelectors();
      message("Obrázok bol odstránený z lokálnej knižnice.");
    } catch (error) {
      failure(error);
    }
    return;
  }
  const action = target.dataset.webAction;
  if (!action || busy) return;
  if (action === "history") {
    openHistory();
    return;
  }
  if (action === "show-media") {
    showTab("media", true);
    return;
  }
  if (action === "reset-template") {
    $("#web-reset-dialog").showModal();
    return;
  }
  if (action === "confirm-reset") {
    draft.html = defaults.html;
    draft.css = defaults.css;
    htmlSelection = null;
    hydrate();
    $("#web-reset-dialog").close();
    message("Predvolená HTML a CSS šablóna bola obnovená v koncepte.");
    return;
  }
  if (action === "publish") {
    if (validateDraft()) $("#web-publish-dialog").showModal();
    return;
  }
  setBusy(true);
  try {
    if (action === "save") {
      await saveDraft();
      message("Koncept je uložený v tomto prehliadači.");
    }
    if (action === "confirm-publish") await publish();
    if (action === "open-preview" || action === "open-published")
      await openPreview(action === "open-published");
  } catch (error) {
    failure(error);
  } finally {
    setBusy(false);
  }
});
for (const type of ["html", "css"]) {
  const editor = $(`#web-${type}-editor`);
  editor.addEventListener("scroll", () => {
    const pre = $(`#web-${type}-highlight`).parentElement;
    pre.scrollTop = editor.scrollTop;
    pre.scrollLeft = editor.scrollLeft;
  });
  if (type === "html")
    for (const name of ["select", "click", "keyup"])
      editor.addEventListener(
        name,
        () => (htmlSelection = [editor.selectionStart, editor.selectionEnd]),
      );
}
const zone = $("#web-upload-zone");
for (const type of ["dragenter", "dragover"])
  zone.addEventListener(type, (event) => {
    event.preventDefault();
    zone.classList.add("dragging");
  });
zone.addEventListener("dragleave", () => zone.classList.remove("dragging"));
zone.addEventListener("drop", (event) => {
  event.preventDefault();
  zone.classList.remove("dragging");
  upload([...event.dataTransfer.files]);
});
$("#web-content-form").addEventListener("submit", (event) => event.preventDefault());
window.addEventListener("beforeunload", (event) => {
  if (isDirty()) {
    event.preventDefault();
    event.returnValue = "";
  }
});
window.addEventListener("pagehide", (event) => {
  if (!event.persisted) for (const url of urls) URL.revokeObjectURL(url);
});
new ResizeObserver(resizePreview).observe($("#web-preview-viewport"));
async function initialize() {
  try {
    const [base, stored, library] = await Promise.all([
      defaultWebsite(),
      loadWebsite(),
      loadAssets(),
    ]);
    defaults = base;
    state = stored || { draft: clone(base), versions: [], publishedId: null, savedAt: null };
    draft = clone(state.draft);
    for (const asset of seedAssets) assets.set(asset.id, asset);
    for (const asset of library) {
      const url = URL.createObjectURL(asset.blob);
      urls.add(url);
      assets.set(asset.id, { ...asset, url });
    }
    $("#web-fields").disabled = false;
    $("#web-upload-input").disabled = false;
    for (const type of ["html", "css"]) $(`#web-${type}-editor`).disabled = false;
    setBusy(false);
    hydrate();
  } catch (error) {
    $("#web-save-state").textContent = "Úložisko nie je dostupné";
    message(
      "Editor sa nepodarilo pripraviť. Povoľte lokálne úložisko pre túto stránku a obnovte ju. " +
        error.message,
      true,
    );
  }
}
initialize();
