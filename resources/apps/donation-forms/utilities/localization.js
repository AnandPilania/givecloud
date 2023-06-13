import { get as dataGet, snakeCase } from 'lodash'
import locales from '../locales'
import { substitute } from './string'

const fallbackLocale = 'en-US'

export const getLocalizationValue = (locale, key, substitutions = {}) => {
  key = String(key)
    .split('.')
    .map((part) => snakeCase(part))
    .join('.')

  let value = dataGet(locales[locale], key) || dataGet(locales[fallbackLocale], key, key)

  if (key.endsWith('_count') && typeof value === 'object') {
    const count = Array.isArray(substitutions['count']) ? substitutions['count'].length : 0

    if (count === 0 && value['zero']) {
      value = value['zero']
    } else if (count < 2 && value['one']) {
      value = value['one']
    } else if (count < 3 && value['two']) {
      value = value['two']
    } else {
      value = value['other'] || key
    }
  }

  return value
}

export const trans = (locale, key, substitutions = {}) => {
  let input = getLocalizationValue(locale, key, substitutions)

  return substitute(input, substitutions, key.endsWith('_html'))
}

export default trans
