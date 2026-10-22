import App from '@wexample/symfony-loader/js/Class/App';

// What the interface remembers, read from the copy the layout handed to the page
// and written through the app, which is where an app changes where it is kept.
//
// The copy is updated on every write: a component mounted later in the same
// page — a panel, a branch loaded on demand — reads what was just decided and
// not what the page was served with.

export function uiStateGet<T>(app: App, key: string, defaultValue: T): T {
  const state = app.layout.vars.uiState as Record<string, unknown> | undefined;

  return state && key in state ? (state[key] as T) : defaultValue;
}

export function uiStateHas(app: App, key: string): boolean {
  const state = app.layout.vars.uiState as Record<string, unknown> | undefined;

  return !!state && key in state;
}

export function uiStateSet(app: App, key: string, value: unknown): void {
  app.layout.vars.uiState = { ...(app.layout.vars.uiState ?? {}), [key]: value };
  app.persistUiState(key, value);
}
