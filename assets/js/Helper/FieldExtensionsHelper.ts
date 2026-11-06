// What is attached to a field whatever its kind (`field_base.schema.json`,
// `extensions`): each extension declared once here, by its name — its
// component, where it stands around the control, in what order — and drawn
// by the form field's base for every field, which knows nothing of it. An
// application declares its own by adding to the registry before its fields
// are drawn (`fieldExtensionsRegister`).
export { fieldExpressionMatches } from './FieldExpressionHelper';
import ExtensionVersions from '../../components/form/extension/versions/versions.vue';
import ExtensionExpression from '../../components/form/extension/expression/expression.vue';

export type FieldExtensionDefinition = {
  component: unknown;
  // Drawn under the control (`footer`) or above it (`header`); an extension
  // standing in for the control says so itself, by its `control` event.
  slot: 'header' | 'footer';
  // Among several on one field, the lower first.
  order: number;
};

export const FIELD_EXTENSIONS: Record<string, FieldExtensionDefinition> = {
  expression: { component: ExtensionExpression, slot: 'footer', order: 10 },
  versions: { component: ExtensionVersions, slot: 'footer', order: 20 }
};

export function fieldExtensionsRegister(name: string, definition: FieldExtensionDefinition): void {
  FIELD_EXTENSIONS[name] = definition;
}

export type FieldExtensionDrawn = { name: string; component: unknown; slot: string; options: Record<string, unknown> };

// What a field draws of its extensions: the ones it names that the registry
// knows, in the registry's order, each with what the field says of it.
export function fieldExtensionsList(extensions: Record<string, unknown> | null | undefined): FieldExtensionDrawn[] {
  return Object.entries(extensions ?? {})
    .filter(([name, options]) => name in FIELD_EXTENSIONS && options && typeof options === 'object')
    .map(([name, options]) => ({ name, component: FIELD_EXTENSIONS[name].component, slot: FIELD_EXTENSIONS[name].slot, options: options as Record<string, unknown> }))
    .sort((a, b) => FIELD_EXTENSIONS[a.name].order - FIELD_EXTENSIONS[b.name].order);
}
