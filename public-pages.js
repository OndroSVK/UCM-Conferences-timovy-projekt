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
  formActions,
} from "./ui.js";
const intro = (tag, title, description) =>
  `<div class="page-intro"><div class="eyebrow">${tag}</div><h1>${title}</h1><p>${description}</p></div>`;
const orbit = `<svg viewBox="0 0 400 400" aria-hidden="true"><g transform="translate(200 180)"><ellipse rx="154" ry="61" transform="rotate(-35)"/><ellipse rx="154" ry="61" transform="rotate(35)"/><ellipse rx="154" ry="61" transform="rotate(90)"/><circle r="26" fill="#dbf587" stroke="#19211f" stroke-width="1.5"/><circle cx="126" cy="-82" r="15" fill="#ffb8a0" stroke="#19211f" stroke-width="1.5"/><circle cx="-126" cy="-82" r="12" fill="#dbf587" stroke="#19211f" stroke-width="1.5"/><circle cy="154" r="12" fill="#fffef9" stroke="#19211f" stroke-width="1.5"/></g></svg>`;
export const publicHome = () =>
  `<section class="hero"><div class="hero-copy"><div class="eyebrow"><span class="dot"></span>UCM V TRNAVE / FAKULTA PRÍRODNÝCH VIED</div><h1>Veda, ktorá<br>nás <em>spája.</em></h1><p>Applied Natural Sciences 2027. Priestor pre nové poznatky, odvážne otázky a stretnutia naprieč prírodnými vedami.</p><div class="actions">${link("Chcem sa zúčastniť", "register")}${link("Objaviť program", "program", "flat")}</div><div class="hero-meta"><span>${icon("calendar")}15. — 17. september 2027</span><span>${icon("pin")}Štrbské Pleso, Slovensko</span></div></div><div class="hero-art"><span class="art-year">SCIENCE. CONNECTED.</span><span class="art-edition">MEET · SHARE · DISCOVER</span>${orbit}<div class="art-label">ANS ’27<small>APPLIED NATURAL SCIENCES</small></div><div class="art-sticker"><strong>3 dni.</strong>Nekonečno<br>možností.</div></div></section><div class="ticker"><span>Biológia <b>↗</b></span><span>Biotechnológie <b>↗</b></span><span>Chémia <b>↗</b></span><span>Environmentálne vedy <b>↗</b></span><span>Informatika <b>↗</b></span></div><section class="section"><div class="section-head"><div><div class="eyebrow">ZAPÍŠTE SI DO KALENDÁRA</div><h2>Veľké veci majú svoj termín.</h2></div><span class="pill">Ukážkový harmonogram</span></div><div class="three-grid"><article class="card shadow lime"><div class="card-top"><span class="eyebrow">01 / REGISTRÁCIA</span>${icon("users")}</div><div class="deadline-day">30. apríl <span class="deadline-month">2027</span></div><h3>Vaše miesto vo vede.</h3><p>Prihláste sa ako rečník, autor posteru alebo účastník.</p></article><article class="card shadow"><div class="card-top"><span class="eyebrow">02 / ABSTRAKTY</span>${icon("file")}</div><div class="deadline-day">15. máj <span class="deadline-month">2027</span></div><h3>Podeľte sa o svoj výskum.</h3><p>Predstavte výsledky, ktoré posúvajú váš odbor vpred.</p></article><article class="card shadow lavender"><div class="card-top"><span class="eyebrow">03 / STRETNUTIE</span>${icon("calendar")}</div><div class="deadline-day">15. september <span class="deadline-month">2027</span></div><h3>Vidíme sa pod Tatrami.</h3><p>Tri dni prednášok, diskusií a nových vedeckých spojení.</p></article></div></section><section class="section"><div class="two-grid"><div><div class="eyebrow">JEDNA KONFERENCIA. MNOHO PERSPEKTÍV.</div><h2 style="font-size:40px;margin:18px 0">Od myšlienky<br>k novým možnostiam.</h2><p class="muted prose">ANS prepája vedeckú komunitu, mladých výskumníkov a ľudí, ktorí chcú prírodné vedy posúvať ďalej. Prineste svoju perspektívu.</p><div class="actions" style="margin-top:24px">${link("Praktické informácie", "information", "flat")}</div></div><div class="card blue shadow"><div class="eyebrow">VAŠA KONFERENCIA NA JEDNOM MIESTE</div><h2 style="font-size:34px;margin:24px 0">Menej administratívy.<br>Viac priestoru pre vedu.</h2><p>Registrácia, príspevky, stav recenzie aj platba. Všetko nájdete vo svojom účastníckom účte.</p><div style="margin-top:25px">${link("Pozrieť môj účet", "account", "lime")}</div></div></div></section>`;
const sessions = [
  [
    "09:00",
    "Registrácia a ranná káva",
    "Zoznámte sa s ostatnými účastníkmi.",
    "Foyer",
    "Networking",
  ],
  [
    "10:00",
    "Otvorenie konferencie ANS 2027",
    "Uvítanie účastníkov a predstavenie programu.",
    "Hlavná sála",
    "Spoločný program",
  ],
  [
    "10:30",
    "Prírodné vedy v prepojenom svete",
    "Plenárna prednáška · ukážkový rečník",
    "Hlavná sála",
    "Keynote",
  ],
  [
    "11:30",
    "Prestávka na kávu",
    "Priestor na diskusiu a nové spojenia.",
    "Foyer",
    "Prestávka",
  ],
  [
    "12:00",
    "Dáta a technológie pre udržateľnú budúcnosť",
    "Odborný blok · informatika a environmentálne vedy",
    "Sála A",
    "Prednášky",
  ],
  [
    "14:00",
    "Nové horizonty biotechnológií",
    "Odborný blok · biológia a biotechnológie",
    "Sála B",
    "Prednášky",
  ],
  [
    "16:00",
    "Posterová sekcia & diskusia",
    "Výsledky výskumu v osobnom rozhovore s autormi.",
    "Foyer",
    "Postery",
  ],
];
export const schedule = (editor = false, day = 0) =>
  `<div class="day-tabs" aria-label="Deň programu">${["Streda / 15. 09.", "Štvrtok / 16. 09.", "Piatok / 17. 09."].map((d, i) => `<button class="${day === i ? "active" : ""}" data-day="${i}" aria-pressed="${day === i}">${d}</button>`).join("")}</div><div class="schedule">${(day ===
  0
    ? sessions
    : day === 1
      ? [
          [
            "09:00",
            "Nové trendy v analytickej chémii",
            "Odborné prezentácie a diskusia",
            "Sála A",
            "Prednášky",
          ],
          [
            "10:30",
            "Prestávka na kávu",
            "Stretnutia účastníkov",
            "Foyer",
            "Prestávka",
          ],
          [
            "11:00",
            "Ekosystémy v čase klimatickej zmeny",
            "Odborný blok · environmentálne vedy",
            "Sála B",
            "Prednášky",
          ],
          [
            "14:00",
            "Posterové prezentácie",
            "Diskusia s autormi",
            "Foyer",
            "Postery",
          ],
        ]
      : [
          [
            "09:00",
            "Veda zajtrajška: mladí výskumníci",
            "Doktorandské prezentácie",
            "Hlavná sála",
            "Prednášky",
          ],
          [
            "11:00",
            "Od výskumu k spolupráci",
            "Otvorená panelová diskusia",
            "Hlavná sála",
            "Diskusia",
          ],
          [
            "12:30",
            "Záverečné slovo a poďakovanie",
            "Ukončenie konferencie ANS 2027",
            "Hlavná sála",
            "Spoločný program",
          ],
        ]
  )
    .map(
      (s, i) =>
        `<article class="schedule-row ${s[4] === "Prestávka" ? "break" : ""}"><time>${s[0]}</time><div><span class="room">${s[3]}</span><h3>${s[1]}</h3><p>${s[2]}</p>${editor ? `<div style="margin-top:12px">${button("Upraviť blok", `session-${i}`, "small flat", "edit")}</div>` : ""}</div>${badge(s[4], s[4] === "Keynote" ? "green" : "gray")}</article>`,
    )
    .join("")}</div>`;
export const publicProgram = () =>
  `${intro("STRETNIME SA PRI VEDE", "Program konferencie", "Prednášky, nové perspektívy aj rozhovory medzi nimi. Vyberte si svoj deň.")}${notice("Toto je ukážkový program dizajnového prototypu. Termíny, miesto a obsah ročníka 2027 sú ilustračné.")}<div class="public-content" id="schedule-container">${schedule()}</div>`;
export const information = () =>
  `${intro("DOBRÉ VEDIEŤ", "Na vedu sa oplatí pripraviť.", "Všetko praktické pred vašou cestou na ANS 2027.")}<div class="public-content"><div class="two-grid"><section class="card"><div class="eyebrow">01 / MIESTO KONANIA</div><h2 style="margin:18px 0">Stretnutie pod Tatrami.</h2><p>Štrbské Pleso, Vysoké Tatry, Slovensko</p><p>Miesto a dátumy sú v prototype ilustračné. Presné informácie doplní organizátor.</p><div class="map-placeholder"><div class="map-pin">${icon("pin")}</div><p>Štrbské Pleso / ilustračná schéma</p></div></section><section class="card lavender"><div class="eyebrow">02 / UBYTOVANIE A DOPRAVA</div><h2 style="margin:18px 0">Vy si vyberiete.<br>My vás nasmerujeme.</h2><p>Ubytovanie si účastníci zabezpečujú samostatne. Tu pribudnú odporúčané hotely a priame odkazy na rezerváciu.</p><ul class="check-list"><li>${icon("check")}Príchod vlakom: stanica Štrbské Pleso</li><li>${icon("check")}Odporúčame plánovať cestu vopred</li><li>${icon("check")}Hotelové rezervácie mimo tohto systému</li></ul><a class="btn small flat" href="https://www.zssk.sk/" target="_blank" rel="noreferrer">Vyhľadať vlakové spojenie ${icon("external")}</a></section></div><div class="three-grid" style="margin-top:24px"><article class="card"><h3>Organizačné otázky</h3><p>Registrácia, účasť a praktické informácie. Kontaktné údaje doplní organizačný tím.</p></article><article class="card"><h3>Pokyny pre autorov</h3><p>Odovzdajte abstrakt a súbor vo svojom účte. Šablóny a finálne požiadavky budú doplnené.</p>${link("Moje príspevky", "submissions", "small flat")}</article><article class="card lime"><h3>Už máte svoj účet?</h3><p>Skontrolujte svoju účasť a pripravte sa na konferenciu.</p>${link("Otvoriť účet", "account", "small flat")}</article></div></div>`;
export const privacy = () =>
  `${intro("TRANSPARENTNE A ZROZUMITEĽNE", "Vaše údaje, prehľadne.", "Návrh informačnej stránky pre účastníkov konferencie.")}<section class="card public-content prose">${notice("Textová maketa. Finálne informácie o spracúvaní osobných údajov musí dodať a schváliť UCM. V tomto prototype sa údaje nikam neposielajú.", "yellow")}<h2>Aké informácie tu nájdete?</h2><p>Identifikáciu prevádzkovateľa, účely spracúvania, rozsah údajov, právne základy, dobu uchovávania a kontaktné údaje zodpovednej osoby.</p><h2>Údaje pri registrácii</h2><p>Registračný formulár počíta s kontaktnými a pracovnými údajmi, kategóriou účasti a údajmi potrebnými na organizáciu konferencie. Stravovacie obmedzenia sú dobrovoľné.</p><h2>Vaše práva a kontakt</h2><p>Na tomto mieste budú informácie o právach účastníka a postupe pri ich uplatnení. Finálny text v tejto vizuálnej ukážke ešte nie je pripravený.</p>${link("Späť na registráciu", "register", "flat")}</section>`;
export const auth = (type = "login") => {
  const forgot = type === "forgot-password",
    reset = type === "reset-password";
  return `<section class="auth"><div class="auth-art"><div><div class="eyebrow">UCM CONFERENCES / ANS ’27</div><h2 style="margin-top:30px">Vaše miesto<br>vo svete vedy.</h2></div><span class="orbit" aria-hidden="true">✳</span><p>Od prvej myšlienky až po stretnutie.<br>Celá konferencia vo vašom účte.</p></div><div class="auth-form"><h1>${forgot ? "Zabudnuté heslo" : reset ? "Nový začiatok." : "Vitajte späť."}</h1><p>${forgot ? "Ukážka žiadosti o obnovenie prístupu." : reset ? "Zvoľte si nové heslo pre svoj účet." : "Prihláste sa a pokračujte tam, kde ste skončili."}</p><form data-form="${type}">${!reset ? field("E-mail", "email", "", "email") : ""}${!forgot ? field(reset ? "Nové heslo" : "Heslo", "password", "", "password") : ""}${reset ? field("Zopakujte heslo", "passwordConfirmation", "", "password") : ""}${type === "login" ? `<a href="#/forgot-password" class="link">Zabudli ste heslo?</a>` : ""}<button type="submit" class="btn primary">${forgot ? "Zobraziť potvrdenie" : reset ? "Uložiť nové heslo" : "Vstúpiť do účtu"}${icon("arrow")}</button></form><div class="form-bottom">${type === "login" ? `Ešte nemáte účet? <a class="link" href="#/register">Registrujte sa</a>` : `<a class="link" href="#/login">Späť na prihlásenie</a>`}</div><p style="margin-top:26px;font-size:10px">Dizajnová ukážka. Môžete zadať vymyslené údaje; autentifikácia nie je zapojená.</p></div></section>`;
};
export const register = (step = 0, data = {}) =>
  `${intro("PRVÝ KROK K NOVÝM SPOJENIAM", "Vidíme sa na ANS ’27.", "Vytvorte si účet a zaregistrujte svoju účasť na konferencii.")}<div class="public-content register-layout"><section class="panel"><div class="panel-body"><div class="steps">${["Váš profil", "Vaša účasť", "Rekapitulácia"].map((n, i) => `<div class="step ${step === i ? "active" : ""}"><span>${i + 1}</span>${n}</div>`).join("")}</div><form data-form="register" data-step="${step}">${
    step === 0
      ? `<div class="form-grid">${field("Meno", "firstName", data.firstName || "")}${field("Priezvisko", "lastName", data.lastName || "")}${field("E-mail", "email", data.email || "", "email", "full")}${field("Organizácia / pracovisko", "organization", data.organization || "", "text", "full")}${field("Heslo (aspoň 8 znakov)", "password", "", "password")}${field("Zopakujte heslo", "passwordConfirmation", "", "password")}</div>`
      : step === 1
        ? `<h3 style="margin-bottom:18px">Ako sa chcete zúčastniť?</h3>${[
            ["Rečník", "Prezentácia odborného príspevku", "180 €"],
            ["Autor posteru", "Prezentácia výsledkov formou posteru", "120 €"],
            ["Pasívny účastník", "Prednášky, diskusie a nové spojenia", "90 €"],
          ]
            .map(
              (o, i) =>
                `<label class="choice"><input type="radio" name="category" value="${o[0]}" ${data.category === o[0] || (!data.category && i === 0) ? "checked" : ""}><span>${o[0]}<small>${o[1]}</small></span><b>${o[2]}</b></label>`,
            )
            .join(
              "",
            )}<div class="form-grid" style="margin-top:24px">${field("Fakturačná organizácia", "billingOrganization", data.billingOrganization || "")}${field("Mesto", "city", data.city || "")}${select("Krajina", "country", ["Slovensko", "Česko", "Poľsko", "Iná"])}<label class="field">Stravovacie obmedzenia <small>Dobrovoľné</small><input name="dietaryRequirements" placeholder="Napr. vegetariánska strava"></label></div>`
        : `<h3>Skontrolujte svoju registráciu</h3><dl class="definition" style="margin:25px 0"><dt>Meno</dt><dd>${safe(data.firstName)} ${safe(data.lastName)}</dd><dt>E-mail</dt><dd>${safe(data.email)}</dd><dt>Organizácia</dt><dd>${safe(data.organization)}</dd><dt>Kategória</dt><dd>${safe(data.category)}</dd><dt>Účastnícky poplatok</dt><dd><strong>${data.category === "Autor posteru" ? "120" : data.category === "Pasívny účastník" ? "90" : "180"} €</strong></dd></dl>${notice("Platba bankovým prevodom. Po registrácii nájdete platobné inštrukcie vo svojom účte.")}<label class="checkbox"><input type="checkbox" required> <span>Oboznámil/a som sa s <a class="link" href="#/privacy">informáciami o spracúvaní údajov</a>.</span></label>`
  }<div class="form-actions">${step > 0 ? button("Späť", "register-back", "flat") : ""}<button type="submit" class="btn primary">${step === 2 ? "Dokončiť ukážkovú registráciu" : "Pokračovať"}${icon("arrow")}</button></div></form></div></section><aside class="card lime shadow"><div class="eyebrow">APPLIED NATURAL SCIENCES</div><h2 style="font-size:48px;margin:20px 0">ANS ’27</h2><p style="color:var(--ink)">15. — 17. september 2027<br>Štrbské Pleso, Slovensko</p><hr style="border:0;border-top:1px solid var(--ink);margin:24px 0"><div class="eyebrow">V CENE ÚČASTI / UKÁŽKA</div><ul class="check-list"><li>${icon("check")}Vstup na odborný program</li><li>${icon("check")}Konferenčné materiály</li><li>${icon("check")}Priestor pre nové kontakty</li></ul><small>Ukážkové ceny, miesto a termín. Nejde o záväznú registráciu.</small></aside></div>`;
function safe(value) {
  return String(value || "—").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
}
