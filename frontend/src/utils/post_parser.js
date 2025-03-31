import { marked } from 'marked';

export function parse(message) {
  marked.use({
    gfm: true,
    breaks: true,
    extensions: [
      {
        name: 'replier',
        level: 'block',
        tokenizer(src) {
          const rule = /(&gt;){2}([0-9]+)/gmi;
          const match = rule.exec(src);

          if (match) {
            const text = src.replace(match[0], match => {
              return `<a href='#${match.slice('&gt;&gt;'.length)}'>>>${match.slice('&gt;&gt;'.length)}</a>`;
            });

            const token = {
              type: 'replier',
              raw: src,
              text: text,
              tokens: this.lexer.blockTokens(text, [])
            }

            return token;
          }
        },
        renderer(token) {
          return `${this.parser.parse(token.tokens)}`
        }
      },
      {
        name: 'link',
        level: 'inline',
        renderer (token) {
          return `<a href='${token.href}' target='_blank'>${token.href}</a>`;
        }
      }
    ]
  });

  return marked.parse(message);
}
