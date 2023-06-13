import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classnames from 'classnames'
import styles from './RadioTile.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLDivElement>> {
  isChecked?: boolean
}

const RadioTile: FC<Props> = ({ isChecked, className, children }) => {
  const css = classnames(styles.root, isChecked && styles.checked, className)
  const textCss = classnames(styles.text, isChecked && styles.checked)

  return (
    <div className={css}>
      <div className={classnames(textCss)}>{children}</div>
    </div>
  )
}

RadioTile.defaultProps = {
  isChecked: false,
}

export { RadioTile }
