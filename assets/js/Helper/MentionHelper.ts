// Someone named in a message — `@maya` —, written once for the composer
// offering who to name and for whatever draws the message sent.

export type MentionPerson = { id?: string | number; label: string; handle?: string | null; src?: string | null; emoji?: string | null };

// What follows the `@` of a mention: no space, no second `@`, a few words' length.
const MENTION_TYPED = /(^|\s)@([^\s@]{0,32})$/u;

// A name as it is compared: without case, without accents.
function mentionFold(text: string): string {
  return text.normalize('NFD').replace(/\p{M}/gu, '').toLowerCase();
}

// What a person is named by in a message: their handle, else their name in
// one word.
export function mentionHandle(person: MentionPerson): string {
  return person.handle || person.label.replace(/\s+/g, '');
}

// The mention being typed, the caret at its end: where its `@` stands and
// what follows it; null when the caret is not in one.
export function mentionQuery(text: string, caret: number): { start: number; query: string } | null {
  const found = MENTION_TYPED.exec(text.slice(0, caret));

  if (!found) {
    return null;
  }

  return { start: caret - found[2].length - 1, query: found[2] };
}

// The people a query names, at most `max`: those whose name or handle starts
// with it first, then those who hold it elsewhere.
export function mentionMatches<T extends MentionPerson>(people: T[], query: string, max: number = 6): T[] {
  const wanted = mentionFold(query);
  const starts: T[] = [];
  const holds: T[] = [];

  for (const person of people) {
    const names = [person.label, mentionHandle(person)].map(mentionFold);

    if (names.some((name) => name.startsWith(wanted))) {
      starts.push(person);
    } else if (names.some((name) => name.includes(wanted))) {
      holds.push(person);
    }
  }

  return [...starts, ...holds].slice(0, max);
}

// The text with a person put in place of the mention typed — from its `@`
// to the caret —, a space after; and where the caret goes.
export function mentionInsert(text: string, start: number, caret: number, person: MentionPerson): { text: string; caret: number } {
  const inserted = `@${mentionHandle(person)} `;
  const after = text.slice(caret).replace(/^\s/, '');

  return { text: text.slice(0, start) + inserted + after, caret: start + inserted.length };
}

// The people a text names, by their handle, each once.
export function mentionFound<T extends MentionPerson>(text: string, people: T[]): T[] {
  const handles = new Set([...text.matchAll(/(^|\s)@([^\s@]+)/gu)].map((found) => mentionFold(found[2].replace(/[.,;:!?)]+$/u, ''))));

  return people.filter((person) => handles.has(mentionFold(mentionHandle(person))));
}

// Html with the people it names set apart (`.mention`), outside its tags
// only — inside a link's text too, never in an attribute.
export function mentionMark(html: string, people: MentionPerson[]): string {
  const byHandle = new Map(people.map((person) => [mentionFold(mentionHandle(person)), person]));

  return html.replace(/(<[^>]*>)|((?:^|(?<=[\s>(]))@([^\s@<]+?))(?=[\s.,;:!?)<]|$)/gu, (whole, tag, mention, handle) => {
    if (tag) {
      return tag;
    }

    const person = byHandle.get(mentionFold(handle));

    return person ? `<span class="mention" title="${person.label.replace(/"/g, '&quot;')}">${mention}</span>` : whole;
  });
}
