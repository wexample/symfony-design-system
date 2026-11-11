// A link of a page held somewhere — an embed, a panel, the dock — going on
// where the page is held (`data-target-embedded="self"`), without the page
// knowing what holds it.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const { targetEmbeddedClick, TARGET_EMBEDDED_SELF } = await import('../../assets/js/Helper/TargetHelper.ts');
const PanelService = (await import('@wexample/symfony-loader/js/Services/PanelService')).default;
const EmbedService = (await import('@wexample/symfony-loader/js/Services/EmbedService')).default;
const DockService = (await import('@wexample/symfony-loader/js/Services/DockService')).default;

function app(calls: string[]) {
  const services = new Map<unknown, unknown>([
    [PanelService, { get: (href: string) => { calls.push(`panel ${href}`); return Promise.resolve(); } }],
    [DockService, { get: (href: string) => { calls.push(`dock ${href}`); return Promise.resolve(); } }],
    [EmbedService, { has: (name: string) => name === 'conversations', load: (name: string, href: string) => { calls.push(`embed ${name} ${href}`); return Promise.resolve(); } }]
  ]);

  return { getServiceOrFail: (service: unknown) => services.get(service) } as any;
}

function link(target: string, href = '/conversations/1') {
  return {
    href,
    getAttribute: (name: string) => (name === 'href' ? href : null),
    closest: (selector: string) => (selector === '[data-target-embedded]' ? { getAttribute: () => target } : null)
  } as any;
}

test('`self` goes on where the page is held: the embed by its name, an overlay by its kind', () => {
  const calls: string[] = [];
  const event = { preventDefault() {} } as any;

  assert.equal(TARGET_EMBEDDED_SELF, 'self');
  assert.equal(targetEmbeddedClick(app(calls), event, link('self'), 'conversations'), true);
  targetEmbeddedClick(app(calls), event, link('self'), 'dock');
  targetEmbeddedClick(app(calls), event, link('self'), 'panel');
  // Held by nothing that says so: a panel, as any embedded link.
  targetEmbeddedClick(app(calls), event, link('self'));

  assert.deepEqual(calls, ['embed conversations /conversations/1', 'dock /conversations/1', 'panel /conversations/1', 'panel /conversations/1']);
});
