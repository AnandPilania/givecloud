import { renderHook } from '@testing-library/react-hooks'
import useTailwindBreakpoints from './useTailwindBreakpoints'
import { breakpointsMap } from './useTailwindBreakpoints'

describe('useTailwindBreakpoints', () => {
  const setWidth = (width) => (window.innerWidth = width)
  const breakpoints = Object.keys(breakpointsMap)
  let results
  beforeEach(() => {
    results = undefined
  })

  const callHook = () => renderHook(() => (results = useTailwindBreakpoints()))

  it('should resize the window based on its values', () => {
    setWidth(500)

    expect(window.innerWidth).toBe(500)
  })

  it.each(breakpoints)('should return true if viewport width is less than %s', (breakpoint) => {
    setWidth(breakpointsMap[breakpoint].min - 1)

    callHook()

    expect(results[breakpoint].lessThan).toBeTruthy()
  })

  it.each(breakpoints)('should return false if viewport width is more  than %s', (breakpoint) => {
    setWidth(breakpointsMap[breakpoint].min + 1)

    callHook()

    expect(results[breakpoint].lessThan).toBeFalsy()
  })

  it.each(breakpoints)('should return true if viewport width is greater than %s', (breakpoint) => {
    setWidth(breakpointsMap[breakpoint].max + 1)

    callHook()

    expect(results[breakpoint].greaterThan).toBeTruthy()
  })

  it.each(breakpoints)('should return false if viewport width is less than %s', (breakpoint) => {
    setWidth(breakpointsMap[breakpoint].max - 1)

    callHook()

    expect(results[breakpoint].greaterThan).toBeFalsy()
  })
})
