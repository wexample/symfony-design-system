// What a count shows: the figure, `99+` past its maximum.
export function countLabel(value: number, max: number | null = 99): string {
  return max && value > max ? `${max}+` : String(value);
}

// A count drawn by count_badge() given a new value, as the thing it counts
// changes on the page: its figure, hidden at zero — a dot shown or not.
export function countApply(el: HTMLElement, value: number): void {
  const max = Number(el.dataset.countMax) || null;

  if (!el.classList.contains('count--dot')) {
    el.textContent = countLabel(value, max);
  }

  el.hidden = !value;
}
