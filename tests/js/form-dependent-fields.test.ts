// Fields that depend on others: one shown only for some values of another
// (`visibleWhen`), an address reading another's value (`__field:<name>__`).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { register } from 'node:module';

register('./hooks.mjs', import.meta.url);

const { formDefinitionVisible, formDefinitionUrlFields, formDefinitionResolveUrl } = await import('../../assets/js/Helper/FormDefinitionHelper.ts');

test('a field is shown while the one it follows holds one of its values', () => {
  const field = { visibleWhen: { field: 'scope', in: ['named'] } };

  assert.equal(formDefinitionVisible(field, { scope: 'named' }), true);
  assert.equal(formDefinitionVisible(field, { scope: 'session' }), false);
  assert.equal(formDefinitionVisible(field, {}), false);
  // Following nothing, always.
  assert.equal(formDefinitionVisible({}, {}), true);
});

test('an address takes the values of the fields it names, and none while one is empty', () => {
  const url = '/_api/versions?pipeline=__field:pipeline__&kind=__field:kind__';

  assert.deepEqual(formDefinitionUrlFields(url), ['pipeline', 'kind']);
  assert.equal(formDefinitionResolveUrl(url, { pipeline: 'a b', kind: 'x' }), '/_api/versions?pipeline=a%20b&kind=x');
  assert.equal(formDefinitionResolveUrl(url, { pipeline: '', kind: 'x' }), null);
  assert.equal(formDefinitionResolveUrl('/plain', {}), '/plain');
  assert.equal(formDefinitionResolveUrl(null, {}), null);
});
