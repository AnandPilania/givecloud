import type { FC, HTMLProps, ReactNode } from 'react'
import type { RadioGroupProps } from './RadioGroupContext'
import classNames from 'classnames'
import { RadioGroupContext } from './RadioGroupContext'
import styles from './RadioGroup.styles.scss'

type AdditionalProps = Pick<HTMLProps<HTMLFieldSetElement>, 'className'> & RadioGroupProps

interface Props extends AdditionalProps {
  children: ReactNode
  label: string
  isLabelVisible?: boolean
}

const RadioGroup: FC<Props> = ({
  children,
  label,
  isLabelVisible,
  name,
  onChange,
  checkedValue,
  isDisabled = false,
  showInput = true,
  className,
}) => (
  <RadioGroupContext.Provider
    value={{
      name,
      onChange,
      checkedValue,
      isDisabled,
      showInput,
    }}
  >
    <fieldset className={className}>
      <legend className={classNames(isLabelVisible ? styles.label : styles.screenReader)}>{label}</legend>
      {children}
    </fieldset>
  </RadioGroupContext.Provider>
)

RadioGroup.defaultProps = {
  isDisabled: false,
  isLabelVisible: true,
  onChange: () => {},
  showInput: true,
}

export { RadioGroup }
