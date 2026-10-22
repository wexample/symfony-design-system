export function hashParamDelete(...names: string[]): void {
  const params = new URLSearchParams(window.location.hash.slice(1));
  names.forEach(n => params.delete(n));
  const { pathname, search } = window.location;
  const hash = params.toString();
  window.history.replaceState(
    { manualState: true },
    document.title,
    hash ? `${pathname}${search}#${hash}` : `${pathname}${search}`
  );
}
