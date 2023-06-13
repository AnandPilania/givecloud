import useApiUrl from '@/hooks/api/useApiUrl'

const useFeatureApiUrl = ({ feature = '' }) => {
  const apiUrl = useApiUrl()

  return `${apiUrl}/feature-previews/feature_${feature}`
}

export default useFeatureApiUrl
