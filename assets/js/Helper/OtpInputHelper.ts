export type OtpInputCell = {
  char: string;
  active: boolean;
};

/**
 * Where a code field stands: one character per cell, '' for a cell not filled
 * — a digit cleared in the middle leaves its cell empty rather than pulling
 * the ones after it back —, and the cell being written in.
 */
export type OtpInputState = {
  chars: string[];
  active: number;
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
 * A code given whole — the field's value, a paste, the code the phone
 * offers —, laid in the cells from the first, and written on from the cell
 * after it.
 */
export function otpInputFrom(value: string, length: number): OtpInputState {
  const chars = Array.from({ length }, (_, index) => value.charAt(index));

  return { chars, active: Math.min(value.length, length - 1) };
}

/**
 * What the field submits: the characters in their order. Complete only when
 * no cell is left empty.
 */
export function otpInputValue(state: OtpInputState): string {
  return state.chars.join('');
}

export function otpInputComplete(state: OtpInputState): boolean {
  return state.chars.every((char) => char !== '');
}

/**
 * Typed into the active cell: it replaces what the cell held — nothing moves
 * aside —, and the next cell becomes the active one. Several at once (a code
 * typed fast, dictated) go on from cell to cell, what does not fit dropped.
 */
export function otpInputWrite(state: OtpInputState, text: string): OtpInputState {
  const chars = [...state.chars];
  let index = state.active;

  for (const char of text) {
    if (index >= chars.length) {
      break;
    }

    chars[index] = char;
    index++;
  }

  return { chars, active: Math.min(index, chars.length - 1) };
}

/**
 * Erased: the active cell when it holds something, its place kept. Backwards
 * from an empty cell, the one before it, which becomes the active one — the
 * way backspace walks back through a code.
 */
export function otpInputErase(state: OtpInputState, backward: boolean): OtpInputState {
  const chars = [...state.chars];

  if (chars[state.active] !== '') {
    chars[state.active] = '';

    return { chars, active: state.active };
  }

  if (backward && state.active > 0) {
    chars[state.active - 1] = '';

    return { chars, active: state.active - 1 };
  }

  return { chars, active: state.active };
}

/**
 * The active cell moved to another one, any of them: clicked, or reached by
 * the arrows.
 */
export function otpInputMove(state: OtpInputState, index: number): OtpInputState {
  return { chars: state.chars, active: Math.max(0, Math.min(index, state.chars.length - 1)) };
}

/**
 * The cells a code is drawn in, the active one marked while the field is
 * written in — `focused` false, none is.
 */
export function otpInputCells(state: OtpInputState, focused: boolean): OtpInputCell[] {
  return state.chars.map((char, index) => ({
    char,
    active: focused && index === state.active,
  }));
}
