const omit = (obj = {}, keys = []) => {
  const clone = Object.assign({}, obj)

  if (typeof keys === 'string') {
    keys = [keys]
  }

  keys.forEach((key) => {
    const keys = key.split('.')

    keys.reduce((acc, key, index) => {
      if (index === keys.length - 1) {
        delete acc[key]

        return true
      }

      return acc[key]
    }, clone)
  })

  return clone
}

export default omit
