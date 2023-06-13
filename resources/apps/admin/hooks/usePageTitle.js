import { useEffect } from 'react'
import { PAGE_TITLE_SUFFIX } from '@/constants/documentConstants'

const usePageTitle = (title) => {
  useEffect(() => {
    document.title = (title ? title + ' | ' : '') + PAGE_TITLE_SUFFIX
  }, [title])
  return null
}

export default usePageTitle
