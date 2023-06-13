import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classNames from 'classnames'
import styles from './Label.styles.scss'

interface Props extends PropsWithChildren<HTMLProps<HTMLLabelElement>> {
  isOptional?: boolean
  isDisabled?: boolean
  isError?: boolean
}

const Label: FC<Props> = ({ htmlFor, children, isOptional, isDisabled, isError, className, ...rest }) => {
  const css = classNames(styles.root, isError && styles.error, isDisabled && styles.disabled, className)

  const renderOptional = () => (isOptional ? <span className={classNames(styles.optional)}>optional</span> : null)

  if (htmlFor) {
    return (
      <label className={css} htmlFor={htmlFor} {...rest}>
        {children}
        {renderOptional()}
      </label>
    )
  }

  return (
    <span className={css}>
      {children}
      {renderOptional()}
    </span>
  )
}

export { Label }
