import MarkdownIt from 'markdown-it';

// Markdown written by someone the page does not vouch for — an agent repeating
// what it read — drawn as html without letting any through: raw html is shown
// as the text it is (`html: false`), and a link to a `javascript:` address is
// refused, so nothing in the text can run in the reader's session.
const markdown = new MarkdownIt({
  html: false,
  linkify: true,
  breaks: true,
});

// A link leads out of the page it is read in.
const renderLinkOpen = markdown.renderer.rules.link_open
  ?? ((tokens, index, options, env, self) => self.renderToken(tokens, index, options));

markdown.renderer.rules.link_open = (tokens, index, options, env, self) => {
  tokens[index].attrSet('target', '_blank');
  tokens[index].attrSet('rel', 'noopener noreferrer');

  return renderLinkOpen(tokens, index, options, env, self);
};

export function markdownRender(text: string): string {
  return markdown.render(text ?? '');
}
