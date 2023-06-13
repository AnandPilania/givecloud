import { useState, useEffect } from 'react'

// breakpoints based on https://tailwindcss.com/docs/screens
export const breakpointsMap = {
  extraSmall: {
    min: 420,
    max: 640,
  },
  small: {
    min: 640,
    max: 768,
  },
  medium: {
    min: 768,
    max: 1024,
  },
  large: {
    min: 1024,
    max: 1280,
  },
  extraLarge: {
    min: 1280,
    max: 1536,
  },
} as const

type TailwindBreakpoint = keyof typeof breakpointsMap

interface BreakPoint {
  min: number
  max: number
}

type BreakPointsMap = {
  [key in TailwindBreakpoint]: BreakPoint
}

type BreakPointResult = ReturnType<typeof getBreakpoint>

type BreakPointResultsMap = {
  [key in TailwindBreakpoint]: BreakPointResult
}

const breakpointLessThan = (width: number, breakpoint: keyof BreakPointsMap) => width < breakpointsMap[breakpoint].min
const breakpointGreaterThan = (width: number, breakpoint: keyof BreakPointsMap) =>
  width > breakpointsMap[breakpoint].max

const getBreakpoint = (width: number, breakpoint: keyof BreakPointsMap) => ({
  greaterThan: breakpointGreaterThan(width, breakpoint),
  lessThan: breakpointLessThan(width, breakpoint),
})

const useTailwindBreakpoints = () => {
  const [width, setWidth] = useState(window.innerWidth)

  useEffect(() => {
    const handleWindowResize = () => {
      setWidth(window.innerWidth)
    }

    window.addEventListener('resize', handleWindowResize)

    return () => window.removeEventListener('resize', handleWindowResize)
  }, [])

  const breakpointKeys = Object.keys(breakpointsMap) as Array<TailwindBreakpoint>

  const breakpoints = breakpointKeys.reduce((map, breakpoint) => {
    map[breakpoint] = getBreakpoint(width, breakpoint)
    return map
  }, {}) as BreakPointResultsMap

  return { ...breakpoints }
}

export { useTailwindBreakpoints }
