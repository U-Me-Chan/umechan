import { formatDuration } from '../../src/utils/duration_formatter.js'
import { describe, it } from 'node:test'
import assert from 'node:assert'

describe('formatDuration', () => {
  it('', () => {
    assert.strictEqual(formatDuration(123), '00:02:03')
  })
})
