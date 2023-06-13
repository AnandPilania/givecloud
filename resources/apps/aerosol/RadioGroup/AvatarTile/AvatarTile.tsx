import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { RadioButtonChild } from '@/aerosol/RadioGroup/RadioButton'
import classNames from 'classnames'
import styles from './AvatarTile.styles.scss'

type Props = Pick<HTMLProps<HTMLDivElement>, 'className'> & RadioButtonChild & PropsWithChildren

const AvatarTile: FC<Props> = ({ className, children, isChecked }) => {
  const css = classNames(styles.root, isChecked && styles.checked, className)

  return <div className={css}>{children}</div>
}

export { AvatarTile }
