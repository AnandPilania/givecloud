import useApiUrl from '@/hooks/api/useApiUrl'
import { useQuery } from 'react-query'
import axios from 'axios'

const fetcher = (url, params = {}) => {
  return () => {
    return axios.get(url, { params: { ...params } })
  }
}

const useFetcherQuery = (queryKey, url, options, params) => {
  const apiUrl = useApiUrl()
  const { data, isLoading, isError } = useQuery(queryKey, fetcher(`${apiUrl}/${url}`, params), { ...options })
  return { data, isLoading, isError }
}

export default useFetcherQuery
