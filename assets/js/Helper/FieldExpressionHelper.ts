// Whether a value is an expression, by the pattern the `expression`
// extension names (`extension_expression.schema.json`): read by the
// extension itself and by what posts a form's values, without the registry
// of extensions — which imports the components, which import this.
export function fieldExpressionMatches(pattern: string | null | undefined, value: unknown): boolean {
  if (!pattern || typeof value !== 'string') {
    return false;
  }

  try {
    return new RegExp(pattern).test(value);
  } catch {
    return false;
  }
}
