import { marked } from 'marked';

export function parse(message) {
  marked.use({
    gfm: true,
    breaks: true,
    extensions: [
      {
        name: 'quote',
        level: 'inline',
        tokenizer(src) {
          const rule = /&gt;{1,}[\w\s\S]+/gmi;
          const match = rule.exec(src);

          if (match) {
            const text = src.replace(match[0], match => {
              return `<span class="blockquote">${match}</span>`;
            });

            const token = {
              type: 'quote',
              raw: src,
              text: text
            }

            return token;
          }
        },
        renderer (token) {
          return `${token.text}`
        }
      },
      {
        name: 'replier',
        level: 'block',
        tokenizer(src) {
          const rule = /(&gt;){2}([0-9]+)/gmi;
          const match = rule.exec(src);

          if (match) {
            const text = src.replace(match[0], match => {
              return `<a class='reply-link' data-parent-id='${match.slice('&gt;&gt;'.length)}' href='/thread/${match.slice('&gt;&gt;'.length)}'>>>${match.slice('&gt;&gt;'.length)}</a>`;
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
      },
      {
        name: 'hashtag',
        level: 'inline',
        tokenizer(src) {
          const rule = /^#{1}[\w\S]+/;
          const match = rule.exec(src);

          if (match) {
            const text = src.replace(match[0], match => {
              return `<a class="hashtag">${match}</a>`;
            })

            const token = {
              type: 'quote',
              raw: src,
              text: text
            }

            return token;
          }
        },
        renderer (token) {
          return `${token.text}`
        }
      }
    ]
  });

  return marked.parse(message);
}
