// A button at work: spinning, out of reach, and said to be busy. One place for
// it, so a page that sends a form in the background shows the state the button
// shows on its own for a form that navigates.
export function buttonSetLoading(button: HTMLButtonElement, loading: boolean): void {
  button.classList.toggle('is-loading', loading);
  button.disabled = loading;
  button.setAttribute('aria-busy', loading ? 'true' : 'false');
}
