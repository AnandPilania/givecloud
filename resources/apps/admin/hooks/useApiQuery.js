import { useCallback } from 'react'
import axios from 'axios'
import useApiUrl from '@/hooks/api/useApiUrl'

const useApiQuery = () => {
  const apiUrl = useApiUrl()
  const makeRequest = useCallback(
    async (method, uri, data = {}, config = {}) => {
      const configObject = {
        ...config,
        withCredentials: true,
      }

      if (['get', 'delete', 'head', 'options'].includes(method)) {
        return axios[method](`${apiUrl}/${uri}`, configObject)
      } else {
        return axios[method](`${apiUrl}/${uri}`, data, configObject)
      }
    },
    [apiUrl]
  )

  const get = useCallback(
    async (uri, config = {}) => {
      return await makeRequest('get', uri, {}, config)
    },
    [makeRequest]
  )

  const put = useCallback(
    async (uri, data, config = {}) => {
      return await makeRequest('put', uri, data, config)
    },
    [makeRequest]
  )

  const post = useCallback(
    async (uri, data, config = {}) => {
      return await makeRequest('post', uri, data, config)
    },
    [makeRequest]
  )

  const destroy = useCallback(
    async (uri, config = {}) => {
      return await makeRequest('delete', uri, {}, config)
    },
    [makeRequest]
  )

  return {
    get,
    put,
    post,
    destroy,
  }
}

export default useApiQuery
