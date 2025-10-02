import { convertBytesToMegabytes } from '../../src/utils/filesize_formatter.js'
import { describe, it } from 'node:test'
import assert from 'node:assert'

describe('convertBytesToMegabytes', () => {
  it('', () => {
    assert.strictEqual(convertBytesToMegabytes(52428800), 50)
  })
})
