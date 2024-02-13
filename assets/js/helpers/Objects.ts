export function callPrototypeMethodIfExists(self, methodName: string, args = {}) {
  const method = Object.getPrototypeOf(self)[methodName];

  if (method) {
    return method.apply(self, args);
  }

  return undefined;
}