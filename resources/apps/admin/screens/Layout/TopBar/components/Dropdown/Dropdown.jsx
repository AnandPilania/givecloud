import { useState, useCallback, useEffect, isValidElement, cloneElement } from 'react'
import PropTypes from 'prop-types'
import { Popover, Transition } from '@headlessui/react'
import { usePopper } from 'react-popper'
import { POPPER_PLACEMENTS, BOTTOM_END } from '@/shared/constants/popper'
import { useOnClickOutside } from '@/shared/hooks'
import styles from './Dropdown.scss'

const Dropdown = ({
  'data-testid': dataTestid = null,
  toggleElement,
  menuContent,
  menuPlacement = BOTTOM_END,
  onToggle = () => {},
}) => {
  const [dropdownRef, setDropdownRef] = useState()
  const [toggleElementRef, setToggleElementRef] = useState()
  const [popperElementRef, setPopperElementRef] = useState()
  const [isOpen, setIsOpen] = useState(false)

  const { styles: popperStyles, attributes: popperAttributes } = usePopper(toggleElementRef, popperElementRef, {
    placement: menuPlacement,
  })

  const toggleMenu = useCallback(() => {
    setIsOpen(!isOpen)
    onToggle(!isOpen)
  }, [setIsOpen, isOpen, onToggle])

  const onClickOutside = useCallback(() => {
    isOpen && setIsOpen(false)
  }, [isOpen, setIsOpen])

  useOnClickOutside({ ref: dropdownRef, onClickOutside })

  useEffect(() => {
    toggleElementRef?.addEventListener?.('click', toggleMenu)

    return () => {
      toggleElementRef?.removeEventListener?.('click', toggleMenu)
    }
  }, [toggleElementRef, toggleMenu])

  return (
    <div data-testid={dataTestid} ref={setDropdownRef} className={styles.root}>
      <Popover>
        <Popover.Button as='div' ref={setToggleElementRef} aria-label='Dropdown toggle container'>
          {toggleElement}
        </Popover.Button>

        <Transition
          show={isOpen}
          enter='transition-opacity duration-200'
          enterFrom='opacity-0'
          enterTo='opacity-100'
          leave='transition-opacity duration-200'
          leaveFrom='opacity-100'
          leaveTo='opacity-0'
        >
          <Popover.Panel
            ref={setPopperElementRef}
            style={popperStyles.popper}
            className={styles.content}
            aria-label='Dropdown menu content container'
            static
            {...popperAttributes?.popper}
          >
            {isValidElement(menuContent) && cloneElement(menuContent, { toggleMenu })}
          </Popover.Panel>
        </Transition>
      </Popover>
    </div>
  )
}

Dropdown.propTypes = {
  'data-testid': PropTypes.string,
  toggleElement: PropTypes.node.isRequired,
  menuContent: PropTypes.node.isRequired,
  menuPlacement: PropTypes.oneOf(POPPER_PLACEMENTS),
  onToggle: PropTypes.func,
}

export { Dropdown }
