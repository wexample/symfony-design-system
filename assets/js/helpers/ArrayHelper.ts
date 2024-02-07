export function unique(
  array: unknown[]
): unknown[] {
  return array.filter((value, index: number) => {
    return array.indexOf(value) === index;
  });
}
