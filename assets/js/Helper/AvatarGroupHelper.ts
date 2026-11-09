// The faces of a group as a pile shows them: the first ones, and how many
// more there are, said as one more face. Written once for the twig face and
// the vue one (src/Helper/AvatarGroupHelper.php holds the same rule).

export type AvatarGroupItem = { label?: string | null; src?: string | null };

// The faces shown and the count of the rest: `max` faces at most, the last
// one given up to the count when there are more.
export function avatarGroupSplit<T>(items: T[], max: number): { shown: T[]; more: number } {
  const limit = Math.max(1, Math.floor(max));

  if (items.length <= limit) {
    return { shown: items, more: 0 };
  }

  return { shown: items.slice(0, limit - 1), more: items.length - (limit - 1) };
}

// Two letters for a face with no picture: the first of the first two words,
// one word giving its first two.
export function avatarInitials(label: string | null | undefined): string {
  const words = String(label ?? '').trim().split(/\s+/).filter(Boolean);

  if (!words.length) {
    return '';
  }

  const letters = words.length > 1 ? words[0][0] + words[1][0] : words[0].slice(0, 2);

  return letters.toUpperCase();
}
