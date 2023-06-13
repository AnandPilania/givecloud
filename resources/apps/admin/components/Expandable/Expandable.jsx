import { useState, useEffect, useCallback } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import styles from './Expandable.scss'

const Expandable = ({
  className = '',
  toggleElementRef = null,
  handleOnToggleIsExpanded = () => null,
  isExpandedInitially = false,
  isExpanded: isExpandedProp,
  isDisabled = false,
  children,
}) => {
  const [expandableRef, setExpandableRef] = useState()
  const [scrollHeight, setScrollHeight] = useState()
  const [isExpandedState, setIsExpandedState] = useState(isExpandedInitially)

  const computedIsExpanded = isExpandedProp !== undefined ? isExpandedProp : isExpandedState

  const toggleIsExpanded = useCallback(() => {
    const nextIsExpanded = !computedIsExpanded

    setIsExpandedState(nextIsExpanded)
    handleOnToggleIsExpanded(nextIsExpanded)
  }, [computedIsExpanded, setIsExpandedState, handleOnToggleIsExpanded])

  const handleOnToggleElementRefClick = useCallback(() => {
    !isDisabled && toggleIsExpanded()
  }, [isDisabled, toggleIsExpanded])

  useEffect(() => {
    setScrollHeight(expandableRef?.scrollHeight)

    toggleElementRef?.addEventListener?.('click', handleOnToggleElementRefClick)

    return () => toggleElementRef?.removeEventListener?.('click', handleOnToggleElementRefClick)
  }, [expandableRef, toggleElementRef, handleOnToggleElementRefClick])

  return (
    <div
      data-testid='expandable'
      ref={setExpandableRef}
      className={classnames(styles.root, className)}
      style={{ height: `${computedIsExpanded ? scrollHeight : 0}px` }}
      aria-label='Expandable content'
    >
      {children}
    </div>
  )
}

Expandable.propTypes = {
  className: PropTypes.string,
  toggleElementRef: PropTypes.oneOfType([PropTypes.func, PropTypes.shape({ current: PropTypes.instanceOf(Element) })]),
  handleOnToggleIsExpanded: PropTypes.func,
  isExpandedInitially: PropTypes.bool,
  isExpanded: PropTypes.bool,
  isDisabled: PropTypes.bool,
  children: PropTypes.node.isRequired,
}

export { Expandable }
