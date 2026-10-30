// Code painted with the design system's `--code-*` inks, without a library:
// a tokenizer per language, returning escaped markup. Json only for now; a
// language it does not know comes back escaped, uncoloured.

const ESCAPES: Record<string, string> = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };

function escapeHtml(text: string): string {
  return text.replace(/[&<>"']/g, (character) => ESCAPES[character]);
}

function token(kind: string, text: string): string {
  return `<span class="code--${kind}">${escapeHtml(text)}</span>`;
}

// A string, a number, a literal, or a single punctuation mark; whatever else
// (spaces, stray characters) is kept as it stands.
const JSON_TOKEN = /("(?:[^"\\]|\\.)*")(\s*:)?|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)|\b(true|false|null)\b|([{}[\],:])/g;

function highlightJson(code: string): string {
  let html = '';
  let last = 0;

  for (const match of code.matchAll(JSON_TOKEN)) {
    const [whole, string, colon, number, literal, punctuation] = match;
    html += escapeHtml(code.slice(last, match.index));

    if (string !== undefined) {
      // A string followed by a colon is a key.
      html += colon ? token('property', string) + token('punctuation', colon) : token('string', string);
    } else if (number !== undefined) {
      html += token('number', number);
    } else if (literal !== undefined) {
      html += token('keyword', literal);
    } else {
      html += token('punctuation', punctuation);
    }

    last = (match.index ?? 0) + whole.length;
  }

  return html + escapeHtml(code.slice(last));
}

const HIGHLIGHTERS: Record<string, (code: string) => string> = {
  json: highlightJson
};

// The code as markup: coloured when its language is known, escaped always.
export function codeHighlight(code: string, lang: string): string {
  return (HIGHLIGHTERS[lang] ?? escapeHtml)(code);
}
