import type { FC, HTMLProps, ReactNode } from 'react'
import type { CheckboxProps } from './Checkbox/Checkbox'
import { isValidElement, Children, cloneElement } from 'react'
import classNames from 'classnames'
import styles from './CheckboxGroup.styles.scss'

interface Props extends HTMLProps<HTMLInputElement> {
  children: ReactNode
  label: string
  isLabelVisible?: boolean
  values: Record<string, boolean>
  isDisabled?: boolean
  name: string
}

interface CheckboxChild extends CheckboxProps {
  name: string
}

const CheckboxGroup: FC<Props> = ({
  children,
  label,
  isLabelVisible = true,
  name,
  onChange,
  values,
  isDisabled,
  className,
}) => (
  <fieldset className={className}>
    <legend className={classNames(isLabelVisible ? styles.label : styles.screenReader)}>{label}</legend>
    {children
      ? Children.map(children, (child) =>
          isValidElement<CheckboxChild>(child) ? cloneElement(child, { onChange, values, name, isDisabled }) : null
        )
      : null}
  </fieldset>
)

export { CheckboxGroup }
