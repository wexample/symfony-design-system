// The window held while a request that leaves the page is on its way — a
// form sent the native way, its answer a whole new page: dimmed under a
// spinner, nothing to click twice, until the next page replaces it. Nothing
// to take away: the page it covers is about to go.
export function pageBusyShow(label = ''): void {
  if (document.querySelector('.page-busy')) {
    return;
  }

  const veil = document.createElement('div');
  veil.className = 'page-busy';
  veil.setAttribute('role', 'status');
  veil.setAttribute('aria-live', 'polite');
  veil.innerHTML = `<div class="spinner spinner--lg" aria-hidden="true">
      <svg class="spinner--circle" viewBox="0 0 50 50" focusable="false">
        <circle class="spinner--circle-path" cx="25" cy="25" r="20" fill="none"></circle>
      </svg>
    </div>`;

  if (label) {
    const text = document.createElement('p');
    text.className = 'page-busy--label';
    text.textContent = label;
    veil.appendChild(text);
  }

  document.body.appendChild(veil);
}
