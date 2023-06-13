// very simple deep clone, watch out!!
export const cloneDeep = (value) => JSON.parse(JSON.stringify(value))

export const arraySample = (array) => arrayShuffle(array)?.[0]

export const arrayShuffle = (array) => {
  for (let currentIndex = array.length - 1; currentIndex >= 1; currentIndex--) {
    const randomIndexAfterCurrent = Math.floor(Math.random() * (currentIndex + 1))
    const valueAtRandomIndexAfterCurrent = array[randomIndexAfterCurrent]

    // swap value at current index with value random index after the current one
    array[randomIndexAfterCurrent] = array[currentIndex]
    array[currentIndex] = valueAtRandomIndexAfterCurrent
  }

  return array
}

export const arrayIncludes = (array, searchElement, fromIndex) => {
  const o = Object(array)
  const len = o.length >>> 0

  if (len === 0) {
    return false
  }

  const n = fromIndex | 0
  let k = Math.max(n >= 0 ? n : len - Math.abs(n), 0)

  function sameValueZero(x, y) {
    return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y))
  }

  while (k < len) {
    if (sameValueZero(o[k], searchElement)) {
      return true
    }

    k++
  }

  return false
}

export const dateAddDays = (date, days) => {
  let res = new Date(date)
  res.setDate(res.getDate() + days)

  return res
}

export const round = (number, precision = 0) => {
  const multiplier = Math.pow(10, precision)

  return Math.round(number * multiplier) / multiplier
}

export const objectFilterNulls = (src) => {
  const dst = {}

  Object.keys(src).forEach((prop) => {
    if (src[prop] !== null) {
      dst[prop] = src[prop]
    }
  })

  return Object.keys(dst).length ? dst : null
}

export const getUrlParameter = (name) => {
  name = name.replace(/[[]/, '\\[').replace(/[\]]/, '\\]')

  const regex = new RegExp('[\\?&]' + name + '=([^&#]*)')
  const results = regex.exec(location.search)

  return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '))
}

export const setAttributes = (el, attributes) => {
  Object.keys(attributes).forEach((key) => el.setAttribute(key, attributes[key]))
}

export const setStyles = (el, styles) => {
  Object.keys(styles).forEach((key) => el.style.setProperty(key, styles[key]))
}
