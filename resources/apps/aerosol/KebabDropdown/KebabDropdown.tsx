import type { FC, ReactNode } from 'react'
import { Fragment } from 'react'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEllipsisV } from '@fortawesome/pro-regular-svg-icons'
import { Menu, Transition } from '@headlessui/react'
import { LEFT, RIGHT } from '@/shared/constants/popper'
import styles from './KebabDropdown.styles.scss'

type PlacementType = typeof LEFT | typeof RIGHT

interface Props {
  children: ReactNode
  placement?: PlacementType
}

const KebabDropdown: FC<Props> = ({ children, placement = LEFT }) => {
  return (
    <Menu as='div' className={styles.root}>
      {({ open }) => (
        <>
          <Menu.Button className={classNames(styles.button, open && styles.open)}>
            <span className='sr-only'>Open dropdown</span>
            <FontAwesomeIcon className='sm' icon={faEllipsisV} aria-hidden='true' />
          </Menu.Button>
          <Transition
            as={Fragment}
            enter='transition ease-out duration-100'
            enterFrom='transform opacity-0 scale-95'
            enterTo='transform opacity-100 scale-100'
            leave='transition ease-in duration-75'
            leaveFrom='transform opacity-100 scale-100'
            leaveTo='transform opacity-0 scale-95'
          >
            <Menu.Items className={classNames(styles.menu, styles[placement])}>{children}</Menu.Items>
          </Transition>
        </>
      )}
    </Menu>
  )
}

KebabDropdown.defaultProps = {
  placement: LEFT,
}

export { KebabDropdown }
