const escape = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c],
  );
export function templateValues(draft, assets) {
  const values = Object.fromEntries(
    Object.entries(draft.content).map(([key, value]) => [`conference.${key}`, value]),
  );
  for (const [slot, id] of Object.entries(draft.media))
    values[`media.${slot}`] = assets.get(id)?.url || "";
  values["media.heroAlt"] = assets.get(draft.media.hero)?.alt || "";
  for (const [id, asset] of assets) values[`media:${id}`] = asset.url;
  values["registration.url"] = new URL("./auth/register.html", import.meta.url).href;
  values["program.url"] = new URL("./public/program.html", import.meta.url).href;
  return values;
}
export function unknownTokens(draft, assets) {
  const values = templateValues(draft, assets);
  return [
    ...new Set(
      [...`${draft.html}\n${draft.css}`.matchAll(/\{\{\s*([^{}]+?)\s*\}\}/g)]
        .map((m) => m[1])
        .filter((key) => !(key in values)),
    ),
  ];
}
export function buildWebsiteDocument(draft, assets) {
  const values = templateValues(draft, assets);
  const replace = (text, html) =>
    text.replace(/\{\{\s*([^{}]+?)\s*\}\}/g, (_, key) =>
      html ? escape(values[key] || "") : String(values[key] || ""),
    );
  const document = new DOMParser().parseFromString(replace(draft.html, true), "text/html");
  // Preview documents run with no scripts, same-origin access, forms or network requests.
  document
    .querySelectorAll("script,iframe,object,embed,base,link,meta,style,form,svg,math")
    .forEach((el) => el.remove());
  for (const el of document.querySelectorAll("*")) {
    for (const attr of [...el.attributes]) {
      if (
        /^on/i.test(attr.name) ||
        ["srcdoc", "srcset", "action", "formaction", "ping", "style"].includes(
          attr.name.toLowerCase(),
        )
      )
        el.removeAttribute(attr.name);
      if (
        ["src", "href", "xlink:href"].includes(attr.name.toLowerCase()) &&
        /^\s*(javascript|vbscript|data):/i.test(attr.value)
      )
        el.removeAttribute(attr.name);
    }
    if (el.tagName === "IMG") {
      const src = el.getAttribute("src");
      const allowed = [...assets.values()].some((asset) => asset.url === src);
      if (!src || !allowed) {
        el.remove();
        continue;
      }
    }
    if (el.tagName === "A") {
      const href = el.getAttribute("href");
      if (href?.startsWith("#")) {
        el.setAttribute("href", "about:srcdoc" + href);
        el.setAttribute("target", "_self");
      } else if (href) {
        el.setAttribute("target", "_blank");
        el.setAttribute("rel", "noopener noreferrer");
      }
    }
  }
  document.documentElement.lang = "sk";
  const meta = document.createElement("meta");
  meta.setAttribute("charset", "utf-8");
  document.head.prepend(meta);
  const viewport = document.createElement("meta");
  viewport.name = "viewport";
  viewport.content = "width=device-width, initial-scale=1";
  document.head.append(viewport);
  const csp = document.createElement("meta");
  csp.httpEquiv = "Content-Security-Policy";
  csp.content =
    "default-src 'none'; img-src blob: data: " +
    new URL("./assets/website/", import.meta.url).href +
    "; style-src 'unsafe-inline'; script-src 'none'; form-action 'none'; base-uri 'none'";
  document.head.prepend(csp);
  const style = document.createElement("style");
  // Keep CSS text inside its style element when serializing the iframe document.
  style.textContent = replace(draft.css, false).replace(/<\/style/gi, "<\\/style");
  document.head.append(style);
  return "<!doctype html>\n" + document.documentElement.outerHTML;
}
