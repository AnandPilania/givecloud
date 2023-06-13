import axios from 'axios'
import { isArray, isObject, camelCase, mapValues, mapKeys, snakeCase } from 'lodash'
import getConfig from '@/utilities/config'
import { API_V1 } from '@/constants/apiConstants'
import { LOGIN_PATH, FUNDRAISING_FORMS_PATH } from '@/constants/pathConstants'

const mapAllKeys = (data, callback) => {
  if (isArray(data)) return data.map((array) => mapAllKeys(array, callback))
  if (isObject(data)) return mapValues(mapKeys(data, callback), (value) => mapAllKeys(value, callback))
  return data
}
const mapKeysToCamelCase = (data) => mapAllKeys(data, (_, key) => camelCase(key))
const mapKeysToSnakeCase = (data) => mapAllKeys(data, (_, key) => snakeCase(key))

const createAxios = (config = {}) => {
  const { clientUrl } = getConfig()
  const baseURL = [clientUrl, API_V1].join('/')
  const { disableCamelCase, errorRedirect, ...rest } = config

  const axiosApi = axios.create({
    baseURL,
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    ...rest,
  })
  axiosApi.interceptors.request.use((response) => {
    const { data } = response
    return { ...response, data: disableCamelCase ? data : mapKeysToSnakeCase(data) }
  })
  axiosApi.interceptors.response.use(
    (response) => {
      const { data } = response
      return { ...response, data: disableCamelCase ? data : mapKeysToCamelCase(data) }
    },
    (error) => {
      if (error.response.status >= 400 && error.response.status < 500)
        window.location.assign(`${LOGIN_PATH}?back=${errorRedirect ? errorRedirect : FUNDRAISING_FORMS_PATH}`)
    }
  )

  const { get, post, patch, delete: destroy } = axiosApi
  return {
    get,
    patch,
    post,
    destroy,
  }
}

export { createAxios }
