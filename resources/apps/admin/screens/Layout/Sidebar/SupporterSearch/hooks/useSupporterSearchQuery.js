import { useEffect, useState } from 'react'
import { useQuery } from 'react-query'
import { useRecoilValue } from 'recoil'
import axios from 'axios'
import useApiUrl from '@/hooks/api/useApiUrl'
import queryState from '@/screens/Layout/Sidebar/SupporterSearch/atoms/queryState'

const useSupporterSearchQuery = () => {
  const apiUrl = useApiUrl()

  const query = useRecoilValue(queryState)
  const [debouncedQuery, setDebouncedQuery] = useState(query)
  const queryIsDebouncing = debouncedQuery !== query

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (queryIsDebouncing) setDebouncedQuery(query)
    }, 250)

    return () => clearTimeout(timeout)
  }, [query, queryIsDebouncing])

  const { data, isLoading } = useQuery(
    ['supporter-search', debouncedQuery],
    async ({ signal }) => {
      if (!debouncedQuery) {
        return { supporters: true, term: '', results: [] }
      }

      try {
        const response = await axios.post(`${apiUrl}/search`, { query: debouncedQuery }, { signal })
        return response.data
      } catch (err) {
        return { supporters: true, term: debouncedQuery, results: [] }
      }
    },
    { staleTime: Infinity }
  )

  if (queryIsDebouncing) {
    return [null, true]
  }

  return [data, isLoading]
}

export default useSupporterSearchQuery
