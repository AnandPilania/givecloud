import type { FC, HTMLProps, PropsWithChildren } from 'react'
import classNames from 'classnames'
import styles from './Checkbox.styles.scss'

interface Props extends PropsWithChildren<Omit<HTMLProps<HTMLInputElement>, 'value'>> {
  isMarginless?: boolean
  isDisabled?: boolean
  values?: Record<string, boolean>
  value: string
}

const Checkbox: FC<Props> = ({
  children,
  id,
  isDisabled,
  disabled,
  onChange,
  name,
  value,
  values,
  className,
  ...rest
}) => {
  const inputCss = classNames('form-checkbox', styles.input, (disabled || isDisabled) && styles.isDisabled)
  const isChecked = !!values?.[value]

  return (
    <label htmlFor={id} className={classNames(styles.root, className)}>
      <input
        {...rest}
        id={id}
        value={value}
        name={name}
        type='checkbox'
        disabled={isDisabled || disabled}
        checked={isChecked}
        className={inputCss}
        onChange={onChange}
      />
      {children}
    </label>
  )
}

export { Checkbox }
export { Props as CheckboxProps }
