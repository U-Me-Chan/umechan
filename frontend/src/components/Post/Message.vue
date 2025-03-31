<template>
<div class="post-message" v-html="formattedMessage"></div>
</template>

<script>
import { parse } from '../..//utils/post_parser'

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
  
