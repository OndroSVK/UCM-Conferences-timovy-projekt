/* UI enhancements only. Identity, records, prices and workflow are server-owned. */
document.querySelectorAll('nav a.active').forEach((link) => link.setAttribute('aria-current', 'page'));

document.querySelector('[data-menu-toggle]')?.addEventListener('click', (event) => {
  const open = document.getElementById('workspace').classList.toggle('menu-open');
  event.currentTarget.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('[data-copy-value]').forEach((button) => {
  button.hidden = !navigator.clipboard;
  button.addEventListener('click', async () => {
    const status = button.parentElement.querySelector('[data-copy-status]');
    try {
      await navigator.clipboard.writeText(button.dataset.copyValue);
      if (status) status.textContent = button.dataset.success;
    } catch {
      if (status) status.textContent = button.dataset.failure;
    }
  });
});

const registration = document.querySelector('[data-step-form]');
if (registration) {
  const steps = [...registration.querySelectorAll('[data-step]')];
  const labels = [...registration.querySelectorAll('[data-step-label]')];
  const back = registration.querySelector('[data-step-back]');
  const next = registration.querySelector('[data-step-next]');
  const submit = registration.querySelector('[data-step-submit]');
  let current = 0;
  registration.noValidate = true;
  registration.querySelector('[data-progress]').hidden = false;
  registration.querySelector('[data-registration-summary]').hidden = false;

  const summarize = () => {
    registration.querySelectorAll('[data-summary]').forEach((element) => {
      const field = registration.elements.namedItem(element.dataset.summary);
      let value = field?.value || element.dataset.fallback || '';
      if (field instanceof HTMLSelectElement) value = field.selectedOptions[0]?.textContent || '';
      if (element.dataset.summary === 'category') value = registration.querySelector('[name="category"]:checked')?.dataset.label || '';
      element.textContent = value || '—';
    });
    registration.querySelector('[data-summary-price]').textContent = registration.querySelector('[name="category"]:checked')?.dataset.price || '—';
  };
  const show = (index, focus = true) => {
    current = index;
    steps.forEach((step, i) => { step.hidden = i !== current; });
    labels.forEach((label, i) => {
      label.classList.toggle('active', i === current);
      if (i === current) label.setAttribute('aria-current', 'step');
      else label.removeAttribute('aria-current');
    });
    back.hidden = current === 0;
    next.hidden = current === steps.length - 1;
    submit.hidden = current !== steps.length - 1;
    summarize();
    if (focus) {
      const target = steps[current].querySelector('h2, input:not([type="hidden"]), select');
      target?.setAttribute('tabindex', '-1');
      target?.focus();
    }
  };
  const invalidIn = (step) => [...step.querySelectorAll('input,select,textarea')].find((field) => !field.checkValidity());
  next.addEventListener('click', () => {
    const invalid = invalidIn(steps[current]);
    if (invalid) { invalid.reportValidity(); return; }
    show(current + 1);
  });
  back.addEventListener('click', () => show(Math.max(0, current - 1)));
  registration.addEventListener('submit', (event) => {
    for (let i = 0; i < steps.length; i++) {
      const invalid = invalidIn(steps[i]);
      if (invalid) {
        event.preventDefault();
        show(i, false);
        invalid.reportValidity();
        return;
      }
    }
    // Enter in an earlier step advances to review; it never creates a registration early.
    if (current !== steps.length - 1) { event.preventDefault(); show(current + 1); }
  });
  const errors = JSON.parse(registration.dataset.errors || '[]');
  const firstErrorStep = steps.findIndex((step) => [...step.querySelectorAll('[name]')].some((field) => errors.includes(field.name)));
  show(firstErrorStep < 0 ? 0 : firstErrorStep, false);
}

const email = document.getElementById('register-email');
if (email) {
  let pending;
  email.addEventListener('input', () => {
    pending?.abort();
    document.getElementById('email-exists').hidden = true;
  });
  email.addEventListener('change', async () => {
    const hint = document.getElementById('email-exists');
    pending?.abort();
    hint.hidden = true;
    if (!email.value || !email.checkValidity()) return;
    pending = new AbortController();
    try {
      const response = await fetch(`${email.dataset.checkUrl}?email=${encodeURIComponent(email.value)}`, {
        signal: pending.signal, headers: { Accept: 'application/json' },
      });
      if (response.ok) hint.hidden = (await response.json()).registered !== true;
    } catch { /* The final server validation remains authoritative. */ }
  });
}

// Native validation and registration-step checks run before locking a successful submit.
document.querySelectorAll('form[method="post"]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (event.defaultPrevented || form.hasAttribute('data-download')) return;
    if (form.dataset.submitting) { event.preventDefault(); return; }
    form.dataset.submitting = 'true';
    // Do not disable submitters: their name/value may be required by status forms.
    form.querySelectorAll('button[type="submit"],button:not([type])').forEach((button) => {
      button.setAttribute('aria-disabled', 'true');
    });
  });
});
window.addEventListener('pageshow', () => {
  document.querySelectorAll('form[data-submitting]').forEach((form) => {
    delete form.dataset.submitting;
    form.querySelectorAll('[aria-disabled="true"]').forEach((button) => button.removeAttribute('aria-disabled'));
  });
});
