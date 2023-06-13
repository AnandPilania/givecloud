import { renderHook } from '@testing-library/react-hooks'
import { RecoilRoot } from 'recoil'
import useApiUrl from '@/hooks/api/useApiUrl'
import { API_V1 } from '@/constants/apiConstants'
import { setConfig } from '@/utilities/config'

test('returns expected url based on clientUrl', () => {
  const clientUrl = 'https://www.test.givecloud.com'

  const wrapper = ({ children }) => {
    setConfig({ clientUrl })
    return <RecoilRoot>{children}</RecoilRoot>
  }

  const { result } = renderHook(() => useApiUrl(), { wrapper })

  expect(result.current).toEqual(`${clientUrl}/${API_V1}`)
})
