import { RecoilRoot } from 'recoil'
import { QueryClient, QueryClientProvider } from 'react-query'
import { renderHook } from '@testing-library/react-hooks'
import usePostQuickStartDismissed from '@/hooks/api/usePostQuickStartDismissed'
import { setConfig } from '@/utilities/config'

test('returns expected object when invoked', () => {
  const clientUrl = 'https://test.givecloud.com'
  const queryClient = new QueryClient()

  const wrapper = ({ children }) => {
    setConfig({ clientUrl })

    return (
      <QueryClientProvider client={queryClient}>
        <RecoilRoot>{children}</RecoilRoot>
      </QueryClientProvider>
    )
  }

  const { result } = renderHook(() => usePostQuickStartDismissed(), { wrapper })

  expect(result.current).toMatchObject({ mutate: expect.any(Function) })
})
