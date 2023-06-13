import type { FC, HTMLProps, ReactNode } from 'react'
import classnames from 'classnames'
import { DropdownDivider } from '@/aerosol/Dropdown/DropdownDivider'
import styles from './DropdownHeader.styles.scss'

interface Props extends HTMLProps<HTMLDivElement> {
  children: ReactNode
}

const DropdownHeader: FC<Props> = ({ children, className, ...rest }) => {
  return (
    <>
      <div {...rest} className={classnames(styles.root, className)}>
        {children}
      </div>
      <DropdownDivider isMarginless />
    </>
  )
}

export { DropdownHeader }
