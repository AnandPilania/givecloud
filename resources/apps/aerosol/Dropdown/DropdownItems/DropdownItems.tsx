import type { FC, ReactNode, ComponentPropsWithRef } from 'react'
import { forwardRef } from 'react'
import classnames from 'classnames'
import { Menu } from '@headlessui/react'
import { useDropdownContext } from '@/aerosol/Dropdown/DropdownContext'
import styles from './DropdownItems.styles.scss'

interface Props extends ComponentPropsWithRef<'div'> {
  children: ReactNode
}

const DropdownItems: FC<Props> = forwardRef(({ children, className, ...rest }, ref) => {
  const { isFullWidth } = useDropdownContext()
  const css = classnames(styles.root, isFullWidth && styles.fullWidth, className)

  return (
    <Menu.Items {...rest} ref={ref} className={css}>
      {children}
    </Menu.Items>
  )
})

export { DropdownItems }
