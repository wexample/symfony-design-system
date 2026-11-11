import { countApply } from './CountHelper';

// The count a tool of a dock hangs on its icon (tool_dock(), `count`) given a
// new value, by the tool's name: every bar showing the tool — the page's, the
// one standing over a veil — follows.
export function toolDockCountApply(name: string, value: number): void {
  document
    .querySelectorAll<HTMLElement>(`[data-tool-count="${CSS.escape(name)}"] > .count`)
    .forEach((el) => countApply(el, value));
}
