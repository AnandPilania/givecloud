import { RecoilRoot } from 'recoil'
import moment from 'moment-timezone'
import { renderHook } from '@testing-library/react-hooks'
import useTimeOfDay from '@/hooks/useTimeOfDay'
import { setConfig } from '@/utilities/config'

jest.mock('moment-timezone', () =>
  jest.fn(() => ({
    tz: jest.fn(() => ({
      format: jest.fn(() => 1),
    })),
  }))
)

afterEach(() => {
  jest.clearAllMocks()
})

const wrapper = ({ children }) => {
  setConfig({ timezone: 'Eastern Daylight Time' })
  return <RecoilRoot>{children}</RecoilRoot>
}

test('returns "Morning" if hour is 0', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 0) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Morning')
})

test('returns "Morning" if hour is less than 12', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 11) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Morning')
})

test('returns "Afternoon" if hour is 12', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 12) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Afternoon')
})

test('returns "Afternoon" if hour is greater than 11 and less than 17', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 16) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Afternoon')
})

test('returns "Evening" if hour is 17', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 17) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Evening')
})

test('returns "Evening" if hour is greater than 17', () => {
  moment.mockImplementationOnce(jest.fn(() => ({ tz: jest.fn(() => ({ format: jest.fn(() => 23) })) })))

  const { result } = renderHook(() => useTimeOfDay(), { wrapper })

  expect(result.current).toEqual('Evening')
})
