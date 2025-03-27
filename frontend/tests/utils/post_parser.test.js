import { parse } from '../../src/utils/post_parser.js'
import { describe, it} from 'node:test'
import assert from 'node:assert'

describe('post_parser', () => {
  it('test cursive', () => {
    assert.strictEqual(parse('*test*'), '<p><em>test</em></p>\n')
  })
  it('test bold', () => {
    assert.strictEqual(parse('**test**'), '<p><strong>test</strong></p>\n')
  })
  it('test replier', () => {
    assert.strictEqual(parse('&gt;&gt;123'), "<p><a href='#123'>&gt;&gt;123</a></p>\n")
  })
})
