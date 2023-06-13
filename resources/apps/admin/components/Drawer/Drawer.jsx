import { useState, useEffect, useCallback, Children, isValidElement, cloneElement } from 'react'

import PropTypes from 'prop-types'
import { Dialog, Transition } from '@headlessui/react'
import classnames from 'classnames'
import styles from './Drawer.scss'

const Drawer = ({ toggleElementRef = null, isOpen: isOpenProp, isFullScreen = false, from = 'left', children }) => {
  const [isOpenState, setIsOpenState] = useState(false)
  const computedIsOpen = isOpenProp !== undefined ? isOpenProp : isOpenState

  const toggleIsOpen = useCallback(() => {
    setIsOpenState(!computedIsOpen)
  }, [setIsOpenState, computedIsOpen])

  useEffect(() => {
    toggleElementRef?.addEventListener?.('click', toggleIsOpen)

    return () => toggleElementRef?.removeEventListener?.('click', toggleIsOpen)
  }, [toggleElementRef, toggleIsOpen])

  return (
    <Transition.Root show={computedIsOpen}>
      <Dialog
        className={classnames(styles.root, styles[from], isFullScreen && styles.fullScreen)}
        static
        open={computedIsOpen}
        onClose={toggleIsOpen}
      >
        <Transition.Child
          enter='ease-in-out duration-300'
          enterFrom='opacity-0'
          enterTo='opacity-100'
          leave='ease-in-out duration-300'
          leaveFrom='opacity-100'
          leaveTo='opacity-0'
        >
          <Dialog.Overlay className={styles.overlay} />
        </Transition.Child>

        <Transition.Child
          className={styles.main}
          enter='transition ease-in-out duration-300'
          enterFrom={from === 'left' ? '-translate-x-full' : 'translate-x-full'}
          enterTo='translate-x-0'
          leave='transition ease-in-out duration-300'
          leaveFrom='translate-x-0'
          leaveTo={from === 'left' ? '-translate-x-full' : 'translate-x-full'}
        >
          <div className={styles.mainContent}>
            {Children.map(children, (child) =>
              isValidElement(child) ? cloneElement(child, { toggleDrawer: toggleIsOpen }) : null
            )}
          </div>
        </Transition.Child>
      </Dialog>
    </Transition.Root>
  )
}

Drawer.propTypes = {
  toggleElementRef: PropTypes.oneOfType([PropTypes.func, PropTypes.shape({ current: PropTypes.instanceOf(Element) })]),
  isOpen: PropTypes.bool,
  isFullScreen: PropTypes.bool,
  from: PropTypes.oneOf(['left', 'right']),
  children: PropTypes.node.isRequired,
}

export { Drawer }
