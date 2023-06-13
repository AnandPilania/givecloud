import { escape as escapeHtml, get as dataGet, toString } from 'lodash'
import pluralizeLib from 'pluralize'

export { escapeHtml, toString }

export const firstName = (value) => toString(value).split(' ')[0]
export const lastName = (value) => toString(value).split(' ').splice(1).join(' ') // prettier-ignore

export const friendlyUrl = (value) => String(value || '').replace(/^https?:\/\//, '')

export const pluralize = (value, count = 2) => {
  const singularValue = singularize(value)

  const wordMap = {}

  if (wordMap[singularValue] && count !== 1) {
    return wordMap[singularValue]
  }

  return pluralizeLib(value, count)
}

export const singularize = (value) => pluralizeLib.singular(value)

export const substitute = (input, substitutions = {}, html = false) => {
  const value = String(input).replace(/{{([\s\S]+?)}}/g, (match, interpolateValue) => {
    interpolateValue = dataGet(substitutions, interpolateValue.trim())

    return typeof interpolateValue === 'undefined' ? match : interpolateValue
  })

  if (html) {
    return { __html: value }
  }

  return value
}

export const stripTags = (value) => (value || '').replace(/(<([^>]+)>)/gi, '')
