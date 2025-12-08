import { parse } from '../../src/utils/post_parser.js'
import { describe, it} from 'node:test'
import assert from 'node:assert'

describe('post_parser', () => {
  it('test', () => {
    assert.strictEqual(parse('*test*'), '<p><em>test</em></p>\n')
    assert.strictEqual(parse('#test'), '<p><a class="hashtag">#test</a></p>\n')
    assert.strictEqual(parse('**test**'), '<p><strong>test</strong></p>\n')
    assert.strictEqual(parse('&gt;&gt;123'), "<p><a class='reply-link' data-parent-id='123' href='/thread/123'>&gt;&gt;123</a></p>\n")
    assert.strictEqual(parse('&gt;test\n'), '<p><span class="blockquote">&gt;test</span></p>\n')
  })
})
