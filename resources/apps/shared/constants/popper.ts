export const TOP_START = 'top-start'
export const TOP = 'top'
export const TOP_END = 'top-end'
export const RIGHT_START = 'right-start'
export const RIGHT = 'right'
export const RIGHT_END = 'right-end'
export const BOTTOM_START = 'bottom-start'
export const BOTTOM = 'bottom'
export const BOTTOM_END = 'bottom-end'
export const LEFT_START = 'left-start'
export const LEFT = 'left'
export const LEFT_END = 'left-end'

export const POPPER_PLACEMENTS = [
  TOP_START,
  TOP,
  TOP_END,
  RIGHT_START,
  RIGHT,
  RIGHT_END,
  BOTTOM_START,
  BOTTOM,
  BOTTOM_END,
  LEFT_START,
  LEFT,
  LEFT_END,
]

export const TOOLTIP_PLACEMENTS = [TOP, BOTTOM, LEFT, RIGHT] as const
export type TooltipPlacement = typeof TOOLTIP_PLACEMENTS[number]
