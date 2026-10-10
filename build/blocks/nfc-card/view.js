/******/ (() => { // webpackBootstrap
const FEEDBACK_MS = 2000;
function flash(el, message) {
  if (el.dataset.ucFlashing) {
    return;
  }
  const original = el.textContent;
  el.dataset.ucFlashing = '1';
  el.textContent = message;
  window.setTimeout(() => {
    el.textContent = original;
    delete el.dataset.ucFlashing;
  }, FEEDBACK_MS);
}
async function share(el) {
  const url = el.href;
  const title = el.closest('.ghostlabs-u-card')?.querySelector('.ghostlabs-u-card__name')?.textContent?.trim();
  if (navigator.share) {
    try {
      await navigator.share({
        title,
        url
      });
      return true;
    } catch (error) {
      if (error?.name === 'AbortError') {
        return true;
      }
    }
  }
  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      flash(el, el.dataset.ucCopied || 'Link copied');
      return true;
    } catch {}
  }
  return false;
}
function expandCode(chip) {
  const card = chip.closest('.ghostlabs-u-card');
  const panel = card?.querySelector('.ghostlabs-u-card__qr-panel');
  if (!panel || typeof document.startViewTransition !== 'function') {
    return false;
  }
  const open = card.dataset.ucQr === 'open';
  card.dataset.ucQrActive = '1';
  chip.setAttribute('aria-expanded', open ? 'false' : 'true');
  const transition = document.startViewTransition(() => {
    if (open) {
      delete card.dataset.ucQr;
      panel.hidden = true;
    } else {
      card.dataset.ucQr = 'open';
      panel.hidden = false;
    }
  });
  transition.finished.finally(() => {
    delete card.dataset.ucQrActive;
  });
  return true;
}
function openCode(chip) {
  const dialog = chip.parentElement?.querySelector('.ghostlabs-u-card__qr-dialog');
  if (!dialog || typeof dialog.showModal !== 'function') {
    return false;
  }
  chip.setAttribute('aria-haspopup', 'dialog');
  chip.removeAttribute('aria-expanded');
  chip.removeAttribute('aria-controls');
  dialog.showModal();
  return true;
}
document.addEventListener('click', event => {
  const target = typeof event.target?.closest === 'function' ? event.target : null;
  if (!target) {
    return;
  }
  const closer = target.closest('[data-uc-action="qr-close"]');
  if (closer) {
    closer.closest('dialog')?.close();
    return;
  }
  const chip = target.closest('[data-uc-action="qr"]');
  if (chip) {
    if (expandCode(chip) || openCode(chip)) {
      event.preventDefault();
    }
    return;
  }
  const el = target.closest('[data-uc-action="share"]');
  if (!el) {
    return;
  }
  event.preventDefault();
  share(el).then(handled => {
    if (!handled) {
      window.location.href = el.href;
    }
  });
});
document.addEventListener('click', event => {
  const dialog = event.target;
  if (dialog?.nodeName !== 'DIALOG' || !dialog.open) {
    return;
  }
  const box = dialog.getBoundingClientRect();
  const inside = event.clientX >= box.left && event.clientX <= box.right && event.clientY >= box.top && event.clientY <= box.bottom;
  if (!inside) {
    dialog.close();
  }
});
document.addEventListener('keydown', event => {
  if (event.key !== 'Escape') {
    return;
  }
  const card = document.querySelector('.ghostlabs-u-card[data-uc-qr="open"]');
  card?.querySelector('[data-uc-action="qr"]')?.click();
});
/******/ })()
;