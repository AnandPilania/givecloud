import type { ReactNode, ComponentPropsWithRef, FC } from 'react'
import { forwardRef } from 'react'
import { DropdownButton } from '@/aerosol/Dropdown/DropdownButton'
import styles from './InputDropdownButton.styles.scss'

interface Props extends ComponentPropsWithRef<'button'> {
  children: ReactNode
}

const InputDropdownButton: FC<Props> = forwardRef(({ children, ...rest }, ref) => {
  return (
    <DropdownButton {...rest} className={styles.root} ref={ref}>
      {children}
    </DropdownButton>
  )
})

InputDropdownButton.displayName = 'InputDropdownButton'

export { InputDropdownButton }
