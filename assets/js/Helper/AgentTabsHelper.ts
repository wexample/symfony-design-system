import type App from '@wexample/symfony-loader/js/Class/App';
import AgentNavigation from '@wexample/symfony-loader/js/Class/AgentNavigation';
import { rippleAgentPress } from './RippleHelper';

// What an agent reads and does of a bar of tabs drawn by the server — a bar of
// views (tab), a page's bar (page-tabs), the faces of the conversations
// (avatar-tabs) —, written once: the tabs read as the reader reads them, one
// found by its name or its key, one opened as the reader presses it.
export type AgentTab = { tab: string; current: boolean; href?: string };

// A tab's name: its words, else what it says to whoever cannot see it.
export function agentTabName(el: HTMLElement): string {
  const label = el.querySelector('.tabs--item-label')?.textContent
    ?? el.getAttribute('aria-label')
    ?? el.getAttribute('data-tooltip')
    ?? el.textContent;

  return (label ?? '').replace(/\s+/g, ' ').trim();
}

// A link leading to a page, not a view shown in place (`data-tab`).
function agentTabHref(el: HTMLElement): string | null {
  if (!(el instanceof HTMLAnchorElement) || el.hasAttribute('data-tab')) {
    return null;
  }

  const href = el.getAttribute('href');

  return href && href !== '#' ? el.href : null;
}

export function agentTabRead(el: HTMLElement, current: boolean): AgentTab {
  const href = agentTabHref(el);

  return { tab: agentTabName(el), current, ...(href ? { href } : {}) };
}

// The tab named — its words or its key, whatever the case —, or an error
// naming the ones there are.
export function agentTabFind(tabs: HTMLElement[], name: string): HTMLElement {
  const wanted = String(name ?? '').trim().toLowerCase();
  const found = tabs.find((el) => agentTabName(el).toLowerCase() === wanted
    || (el.getAttribute('data-tab') ?? '').toLowerCase() === wanted);

  if (!found) {
    throw new Error(`No tab "${name}": ${tabs.map(agentTabName).join(', ')}.`);
  }

  return found;
}

// Pressed as the reader presses it — on what shows it, when the tab itself
// is folded away (`shownBy`) —: a page reached is answered from that page.
export async function agentTabOpen(app: App, el: HTMLElement, read: AgentTab, shownBy?: HTMLElement | null): Promise<AgentTab | AgentNavigation> {
  await rippleAgentPress(app, shownBy ?? el);

  if (agentTabHref(el) && (el as HTMLAnchorElement).target !== '_blank') {
    return new AgentNavigation(() => el.click(), { ...read, current: true });
  }

  el.click();

  return { ...read, current: true };
}
