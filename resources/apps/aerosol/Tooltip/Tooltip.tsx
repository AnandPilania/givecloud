import type { Modifier } from 'react-popper'
import type { TooltipPlacement } from '@/shared/constants/popper'
import type { ThemeType } from '@/shared/constants/theme'
import type { ReactNode, FC, KeyboardEvent, HTMLProps } from 'react'
import { useState, useMemo } from 'react'
import { usePopper } from 'react-popper'
import classnames from 'classnames'
import { TOP } from '@/shared/constants/popper'
import { INFO } from '@/shared/constants/theme'
import { useOnClickOutside } from '@/shared/hooks/useOnClickOutside'
import styles from './Tooltip.styles.scss'

export interface Props extends Pick<HTMLProps<HTMLDivElement>, 'aria-label' | 'children' | 'className'> {
  theme?: ThemeType
  isHidden?: boolean
  isAutoShowing?: boolean
  isTriggeredOnClick?: boolean
  tooltipContent: ReactNode
  placement?: TooltipPlacement
  hasTabIndex?: boolean
  modifiers?: ReadonlyArray<Modifier<unknown>>
}

const Tooltip: FC<Props> = ({
  tooltipContent,
  children,
  placement = 'top',
  isHidden,
  isAutoShowing,
  isTriggeredOnClick,
  modifiers = [],
  theme = 'info',
  hasTabIndex = true,
  'aria-label': ariaLabel,
  className,
}) => {
  const [isVisible, setIsVisible] = useState(isAutoShowing)
  const [referenceElement, setReferenceElement] = useState<HTMLDivElement | null>(null)
  const [popperElement, setPopperElement] = useState<HTMLDivElement | null>(null)
  const [arrowElement, setArrowElement] = useState<HTMLDivElement | null>(null)

  const { styles: popperStyles, attributes } = usePopper(referenceElement, popperElement, {
    placement,
    modifiers: [
      {
        name: 'offset',
        options: {
          offset: [0, 16],
        },
      },
      {
        name: 'arrow',
        options: {
          element: arrowElement,
        },
      },
      ...modifiers,
    ],
  })

  const onClickOutside = () => {
    if (isVisible) {
      setIsVisible(false)
    }
  }

  useOnClickOutside<HTMLDivElement>({ ref: referenceElement as HTMLDivElement, onClickOutside })

  const toggleVisibleState = () => setIsVisible((prevState) => !prevState)

  const handleBlur = () => {
    if (!isTriggeredOnClick) {
      setIsVisible(false)
    }
  }

  const handleVisibility = () => (isAutoShowing || isTriggeredOnClick ? null : toggleVisibleState())

  const handleFocus = () => {
    if (!isTriggeredOnClick) {
      setIsVisible(false)
    }
    handleVisibility()
  }

  const renderChildren = () =>
    isTriggeredOnClick ? (
      <div role='button' onClick={toggleVisibleState}>
        {children}
      </div>
    ) : (
      children
    )

  const renderTooltip = () => {
    if (isVisible) {
      return (
        <div
          ref={setPopperElement}
          style={popperStyles.popper}
          {...attributes.popper}
          className={classnames(styles.root, styles[theme], className)}
          aria-live='polite'
        >
          {tooltipContent}
          <div
            ref={setArrowElement}
            style={popperStyles.arrow}
            className={classnames(styles[theme], styles.tooltip, styles[placement])}
          >
            <div className={classnames(styles[theme], styles.arrow)} />
          </div>
        </div>
      )
    }
    return null
  }

  const onKeyAction = useMemo(
    () => ({
      Enter: () => toggleVisibleState(),
      Escape: () => setIsVisible(false),
      Space: () => toggleVisibleState(),
    }),
    []
  )

  const handleKeyDown = ({ code }: KeyboardEvent<HTMLDivElement>) => onKeyAction?.[code]?.()

  if (isHidden) return <>{children}</>

  return (
    <div
      role='tooltip'
      ref={setReferenceElement}
      tabIndex={hasTabIndex ? 0 : -1}
      onFocus={handleFocus}
      onBlur={handleBlur}
      onKeyDown={handleKeyDown}
      onMouseEnter={handleVisibility}
      onMouseLeave={handleVisibility}
      aria-label={ariaLabel}
    >
      {renderChildren()}
      {renderTooltip()}
    </div>
  )
}

Tooltip.defaultProps = {
  theme: INFO,
  placement: TOP,
  isHidden: false,
  isAutoShowing: false,
  hasTabIndex: true,
  modifiers: [],
}

export { Tooltip }
