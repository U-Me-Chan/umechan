<template>
<div class="post-message" v-html="formattedMessage">
</div>
</template>

<script>
import { parse } from '../../utils/post_parser'

export default {
  name: 'Message',
  props: {
    message: {
      type: String,
      default: ''
    }
  },
  computed: {
    formattedMessage: function () {
      return parse(this.message)
    }
  }
}
</script>

<style>
.post-message {
    margin: 10px;
    display: flex;
    flex-direction: column;
    font-size: 14px;
}

pre {
    background-color: var(--color-code-bg);
    max-width: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.blockquote {
    margin-left: 0px;
    padding-left: 5px;
    color: var(--color-blockquote);
}

.hashtag {
    padding-left: 5px;
    color: var(--color-hashtag-color);
}

ol {
    padding-left: 30px;
}

table, tr, th, td {
    border: 1px solid var(--color-table-border);
}

ul {
    list-style: inside;
}

.no-overlay .modal-background {
    background-color: transparent !important;
}
</style>
  
