export type OtpInputCell = {
  char: string;
  active: boolean;
};

/**
 * What a code field keeps of what it was given: the characters a code is made
 * of, and no more of them than it holds. A code pasted as "123 456" or
 * "abc-def" lands whole rather than cut at the separator.
 */
export function otpInputClean(value: string, length: number, alphanumeric: boolean): string {
  const kept = alphanumeric
    ? value.replace(/[^A-Za-z0-9]/g, '').toUpperCase()
    : value.replace(/\D/g, '');

  return kept.slice(0, length);
}

/**
 * The cells a code is drawn in. The active one is where the caret stands, and
 * the last one once the code is complete; none while the field is not written in.
 */
export function otpInputCells(value: string, length: number, caret: number | null): OtpInputCell[] {
  const active = caret === null ? -1 : Math.min(caret, length - 1);

  return Array.from({ length }, (_, index) => ({
    char: value.charAt(index),
    active: index === active,
  }));
}
