<template>
<div class="post-message" v-html="formattedMessage">
</div>
</template>

<script>
import { marked }  from 'marked';

export default {
  name: 'Message',
  data: function () {
    return {
      isMessageFull: false
    }
  },
  props: {
    message: {
      type: String,
      default: ''
    }
  },
  computed: {
    headMessage: function () {
      return this.message.slice(0, 600);
    },
    isLongMessage: function () {
      return this.message.lenght > 600 ? true : false;
    },
    formattedMessage: function () {
      marked.use({
        gfm: true,
        breaks: true,
        mangle: true,
        xhtml: true,
        smartList: true,
        smartypants: true,
        extensions: [
          {
            name: 'replier',
            level: 'block',
            tokenizer(src) {
              const rule = />{2}([0-9]+)/gmi;
              const match = rule.exec(src);

              if (match) {
                const text = src.replace(match[0], match => {
                  return `<a href='#${match.slice('>>'.length)}'>&gt;&gt;${match.slice('>>'.length)}</a>`;
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
          }
        ]
      });

      return marked.parse(this.message);
    }
  }
}
</script>

<style>
.post-message {
    margin: 10px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    font-size: 14px;
}

pre {
    background-color: #f5f2f0;
    max-width: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

blockquote {
    margin-left: 0px;
    border-left: 5px solid #ddd;
    padding-left: 2px;
    color: #083;
}

ol {
    padding-left: 30px;
}

table, tr, th, td {
    border: 1px solid #ddd;
}

ul {
    list-style: inside;
}
</style>
  
