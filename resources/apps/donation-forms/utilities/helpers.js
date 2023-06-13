import { isArrayLike, isEmpty as collectionIsEmpty } from 'lodash'

export const isEmpty = (value) => {
  return !value || (isArrayLike(value) && collectionIsEmpty(value))
}

export const isNotEmpty = (value) => !isEmpty(value)

export const noop = () => {}
