import type App from '@wexample/symfony-loader/js/Class/App';

// The wave a press leaves (`shapes/_ripple.scss`), from the point pressed or
// from the element's middle. Resolves once it has spread, so what the press
// does next — leaving the page — waits to be seen.
const RIPPLE_MS = 500;

export function rippleAt(
  el: HTMLElement,
  point?: { x: number; y: number },
  agent: boolean = false,
): Promise<void> {
  const rect = el.getBoundingClientRect();
  const wave = document.createElement('span');

  wave.className = `ripple--wave${agent ? ' ripple--wave--agent' : ''}`;
  wave.style.setProperty('--ripple-x', `${point?.x ?? rect.width / 2}px`);
  wave.style.setProperty('--ripple-y', `${point?.y ?? rect.height / 2}px`);
  wave.style.setProperty('--ripple-size', `${Math.max(rect.width, rect.height) * 2}px`);

  // Clipped by a layer of its own over the element, not by the element:
  // nothing of its layout changes while the wave runs.
  const clip = document.createElement('span');
  clip.className = 'ripple--clip';
  clip.setAttribute('aria-hidden', 'true');
  clip.appendChild(wave);

  el.classList.add('ripple');
  el.appendChild(clip);

  return new Promise((resolve) => {
    window.setTimeout(() => {
      clip.remove();

      if (!el.querySelector(':scope > .ripple--clip')) {
        el.classList.remove('ripple');
      }

      resolve();
    }, RIPPLE_MS);
  });
}

// Every press of the element answered by a wave, unless the element says
// otherwise (`data-ripple="off"`). Returns the way to stop.
export function rippleOnPress(el: HTMLElement | null | undefined): () => void {
  if (!el || el.dataset.ripple === 'off') {
    return () => {};
  }

  const onPress = (event: PointerEvent) => {
    const rect = el.getBoundingClientRect();

    void rippleAt(el, { x: event.clientX - rect.left, y: event.clientY - rect.top });
  };

  el.addEventListener('pointerdown', onPress);

  return () => el.removeEventListener('pointerdown', onPress);
}

// An agent pressing the element: its hand going there and pinching
// (AgentCursor), the wave in the agent's colour.
export async function rippleAgentPress(app: App, el: HTMLElement | null | undefined): Promise<void> {
  if (!el) {
    return;
  }

  const agentTools = app.services.agentTools;

  // The agent's hand goes there and presses: the wave starts under it. No
  // frame besides — the hand and the wave say the press; a frame says a
  // piece shown (`focus`, `point`).
  await agentTools?.cursor.moveTo(el);
  const press = agentTools?.cursor.press();

  await rippleAt(el, undefined, true);
  await press;
}
