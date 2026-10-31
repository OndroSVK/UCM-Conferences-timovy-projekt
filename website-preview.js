import { defaultWebsite, loadWebsite, loadAssets, seedAssets } from "./website-store.js";
import { buildWebsiteDocument } from "./website-document.js";
const urls = [];
async function initialize() {
  try {
    const [state, library] = await Promise.all([loadWebsite(), loadAssets()]);
    const mode = new URLSearchParams(location.search).get("mode");
    const version = state?.versions.find((v) => v.id === state.publishedId);
    const draft =
      mode === "published" ? version?.snapshot : state?.draft || (await defaultWebsite());
    if (!draft) {
      document.querySelector("#web-preview-empty-title").textContent =
        "Zatiaľ bez publikovanej verzie.";
      document.querySelector("#web-preview-empty-description").textContent =
        "V editore najprv publikujte lokálnu ukážku. Náhľad je dostupný iba v prehliadači, kde ste ho vytvorili.";
      return;
    }
    const assets = new Map(
      seedAssets.map((asset) => [asset.id, { ...asset, ...draft.assetMetadata?.[asset.id] }]),
    );
    for (const asset of library) {
      const url = URL.createObjectURL(asset.blob);
      urls.push(url);
      assets.set(asset.id, { ...asset, ...draft.assetMetadata?.[asset.id], url });
    }
    document.querySelector("#web-preview-label").textContent =
      mode === "published"
        ? `Publikovaná ukážka · verzia ${version.number}`
        : "Náhľad uloženého konceptu";
    document.title = draft.content.title + " · lokálny náhľad";
    const frame = document.querySelector("#website-output");
    frame.srcdoc = buildWebsiteDocument(draft, assets);
    frame.hidden = false;
    document.querySelector("#web-preview-empty").hidden = true;
  } catch (error) {
    document.querySelector("#web-preview-empty-title").textContent =
      "Náhľad sa nepodarilo načítať.";
    document.querySelector("#web-preview-empty-description").textContent =
      "Skontrolujte dostupnosť lokálneho úložiska. " + error.message;
  }
}
window.addEventListener("pagehide", (event) => {
  if (!event.persisted) urls.forEach((url) => URL.revokeObjectURL(url));
});
initialize();
