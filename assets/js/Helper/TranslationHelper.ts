type TranslatedMap = Record<string, [propName: string, defaultKey: string]>;

function buildTranslatedProps(map: TranslatedMap) {
  const props: Record<string, { type: StringConstructor; default: string }> = {};

  for (const [, [propName]] of Object.entries(map)) {
    props[propName] = {
      type: String,
      default: ''
    };
  }

  return props;
}

function buildTranslatedComputed(map: TranslatedMap) {
  return Object.fromEntries(
    Object.entries(map).map(([computedName, [propName, defaultKey]]) => [
      computedName,
      function resolvedTranslatedProp(this: { [key: string]: unknown; trans: (key: string) => string }) {
        return this.trans((this[propName] as string) || defaultKey);
      }
    ])
  );
}

export default function buildTranslatedBindings(map: TranslatedMap) {
  return {
    props: buildTranslatedProps(map),
    computed: buildTranslatedComputed(map)
  };
}
