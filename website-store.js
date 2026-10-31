const databaseName = "ucm-website-prototype-v1";
export const editionKey = "ans-2027";
let connection;
export function openWebsiteStore() {
  if (!connection)
    connection = new Promise((resolve, reject) => {
      const request = indexedDB.open(databaseName, 1);
      request.onupgradeneeded = () => {
        const db = request.result;
        db.createObjectStore("editions");
        db.createObjectStore("assets", { keyPath: "id" });
      };
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => {
        connection = null;
        reject(request.error);
      };
      request.onblocked = () => {
        connection = null;
        reject(new Error("Zatvorte ostatné náhľady a skúste stránku obnoviť."));
      };
    });
  return connection;
}
async function transact(store, mode, operation) {
  const db = await openWebsiteStore();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(store, mode);
    const request = operation(tx.objectStore(store));
    tx.oncomplete = () => resolve(request.result);
    tx.onerror = () => reject(tx.error);
    tx.onabort = () => reject(tx.error || new Error("Uloženie bolo prerušené."));
  });
}
export const loadWebsite = () => transact("editions", "readonly", (s) => s.get(editionKey));
export const saveWebsite = (state) =>
  transact("editions", "readwrite", (s) => s.put(state, editionKey));
export const loadAssets = () => transact("assets", "readonly", (s) => s.getAll());
export const saveAsset = (asset) => transact("assets", "readwrite", (s) => s.put(asset));
export const removeAsset = (id) => transact("assets", "readwrite", (s) => s.delete(id));
export const seedAssets = [
  {
    id: "seed-logo",
    name: "UCM Conferences / logo",
    alt: "UCM Conferences",
    url: new URL("./assets/website/logo.svg", import.meta.url).href,
    width: 100,
    height: 100,
    builtIn: true,
  },
  {
    id: "seed-hero",
    name: "ANS 2027 / úvodná ilustrácia",
    alt: "Vedecká ilustrácia konferencie ANS 2027",
    url: new URL("./assets/website/hero.svg", import.meta.url).href,
    width: 800,
    height: 800,
    builtIn: true,
  },
];
export async function defaultWebsite() {
  const read = async (name) => {
    const response = await fetch(new URL(`./templates/${name}`, import.meta.url));
    if (!response.ok) throw new Error("Predvolenú šablónu sa nepodarilo načítať.");
    return response.text();
  };
  const [html, css] = await Promise.all([read("conference-index.html"), read("conference.css")]);
  return {
    content: {
      title: "Applied Natural Sciences 2027",
      edition: "ANS 2027",
      heading: "Veda, ktorá nás spája.",
      description:
        "Priestor pre nové poznatky, odvážne otázky a stretnutia naprieč prírodnými vedami.",
      about:
        "ANS prepája vedeckú komunitu, mladých výskumníkov a ľudí, ktorí chcú prírodné vedy posúvať ďalej. Prineste svoju perspektívu.",
      date: "15. – 17. september 2027",
      venue: "Štrbské Pleso, Slovensko",
      contact: "ans2027@example.com",
      accent: "#dbf587",
    },
    media: { logo: "seed-logo", hero: "seed-hero", background: "" },
    html,
    css,
  };
}
