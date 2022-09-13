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
        breaks: true,
        gfm: true,
        xhtml: true
      });

      marked.use({
        extensions: [
          {
            name: 'replier',
            level: 'block',
            tokenizer(src) {
              const rule = />>\d{1,10}/g;
              const match = rule.exec(src);

              if (match) {
                return {
                  type: 'replier',
                  raw: match[0],
                  text: match[0].replace(rule, match => {
                    return `<a href='#${match.slice('>>'.length)}'>${match}</a>`;
                  })
                };
              }
            },
            renderer(token) {
              return `${token.text}<br>`;
            }
          },
          {
            name: 'audio-linker',
            level: 'block',
            tokenizer(src) {
              const rule = /(https?):\/\/[a-z./0-9-_]+(\.(ogg|mp3)$)/gmi;
              const match = rule.exec(src);

              if (match) {
                return {
                  type: 'audio-linker',
                  raw: match[0],
                  text: match[0].replace(rule, match => {
                    return `<br><audio controls=true src='${match}'><br>`;
                  })
                };
              }
            },
            renderer(token) {
              return `${token.text}`;
            }
          }
        ]
      });

      return marked.parse(this.message);
    }
  },
  methods: {
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
}

blockquote {
    margin-left: 0px;
    border-left: 5px solid #ddd;
    padding-left: 2px;
    color: #083;
}
</style>
  
