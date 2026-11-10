// markdown-it stood in for: a test of a thread reads its lines, not the
// markdown they are drawn from.
export default class MarkdownIt {
  renderer = { rules: {}, renderToken: () => '' };

  render(text) {
    return String(text ?? '');
  }
}
