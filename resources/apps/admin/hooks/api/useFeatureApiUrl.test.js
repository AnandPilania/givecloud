import { renderHook } from '@testing-library/react-hooks'
import useFeatureApiUrl from '@/hooks/api/useFeatureApiUrl'

const apiUrl = 'https://www.test.givecloud.com/api/v1'

jest.mock('@/hooks/api/useApiUrl', () => jest.fn(() => apiUrl))

test('returns expected url based on given feature', () => {
  const feature = 'test_feature'

  const { result } = renderHook(() => useFeatureApiUrl({ feature }))

  expect(result.current).toEqual(`${apiUrl}/feature-previews/feature_${feature}`)
})
