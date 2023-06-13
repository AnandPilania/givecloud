import omit from '@/utilities/omit'

test('removes top level property when given string of top level property', () => {
  const obj = {
    one: 1,
    two: 2,
    three: 3,
  }

  const result = omit(obj, 'three')

  expect(result).toEqual({
    one: 1,
    two: 2,
  })
})

test('removes nested property when given string of nested property', () => {
  const obj = {
    top: 'top',
    nested: {
      deepNested: {
        one: 1,
        two: 2,
        three: 3,
      },
    },
  }

  const result = omit(obj, 'nested.deepNested.three')

  expect(result).toEqual({
    top: 'top',
    nested: {
      deepNested: {
        one: 1,
        two: 2,
      },
    },
  })
})

test('removes top level properties when given an array of top level properties', () => {
  const obj = {
    one: 1,
    two: 2,
    three: 3,
  }

  const result = omit(obj, ['one', 'two'])

  expect(result).toEqual({ three: 3 })
})

test('removes nested properties when given an array of nested properties', () => {
  const obj = {
    top: 'top',
    nested: {
      deepNested: {
        one: 1,
        two: 2,
        three: 3,
      },
    },
  }

  const result = omit(obj, ['nested.deepNested.one', 'nested.deepNested.two'])

  expect(result).toEqual({
    top: 'top',
    nested: {
      deepNested: {
        three: 3,
      },
    },
  })
})

test('removes top level and nested properties when given a mix of top level and nested properties', () => {
  const obj = {
    top: 'top',
    nested: {
      deepNested: {
        one: 1,
        two: 2,
        three: 3,
      },
    },
  }

  const result = omit(obj, ['top', 'nested.deepNested.one', 'nested.deepNested.two'])

  expect(result).toEqual({
    nested: {
      deepNested: {
        three: 3,
      },
    },
  })
})

test('returns an empty object when not given anything', () => {
  const result = omit()

  expect(result).toEqual({})
})

test('returns the same object when not given any keys', () => {
  const obj = {
    one: 1,
    two: 2,
    three: 3,
  }

  const result = omit(obj)

  expect(result).toEqual(obj)
})

test('returns the same object when given keys that do not exist', () => {
  const obj = {
    top: 'top',
    nested: {
      one: 1,
      two: 2,
    },
  }

  const result = omit(obj, ['fake', 'nested.wrong'])

  expect(result).toEqual(obj)
})
