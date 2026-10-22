// A vue component's options run on a bare instance: props at their defaults
// then the ones given, data, computed as getters, methods bound. `bases` are
// the components it extends, first the farthest, since the hooks leave an
// `extends` empty. No template, no reactivity: what a test reads is computed
// again on each read.
export function vueInstance(component: any, props: Record<string, unknown> = {}, bases: any[] = []) {
  const emitted: Array<[string, unknown]> = [];
  const vm: any = {};
  const chain = [...bases, component];

  chain.forEach((options) => {
    Object.entries(options.props ?? {}).forEach(([name, definition]: [string, any]) => {
      vm[name] = typeof definition.default === 'function' && definition.type !== Function
        ? definition.default()
        : definition.default;
    });
  });
  Object.assign(vm, props);
  chain.forEach((options) => Object.assign(vm, options.data?.call(vm) ?? {}));
  chain.forEach((options) => {
    Object.entries(options.computed ?? {}).forEach(([name, getter]: [string, any]) => {
      Object.defineProperty(vm, name, { get: () => getter.call(vm), configurable: true });
    });
    Object.entries(options.methods ?? {}).forEach(([name, method]: [string, any]) => {
      vm[name] = method.bind(vm);
    });
  });
  vm.$emit = (event: string, payload: unknown) => emitted.push([event, payload]);
  vm.trans = (key: string, args: Record<string, unknown> = {}) => `${key.split('.').pop()}${Object.keys(args).length ? ' ' + JSON.stringify(args) : ''}`;

  return { vm, emitted };
}
