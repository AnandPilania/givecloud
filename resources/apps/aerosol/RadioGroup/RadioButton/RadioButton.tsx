import type { FC, ReactNode, ComponentPropsWithRef } from 'react'
import { cloneElement, Children, isValidElement, forwardRef } from 'react'
import classNames from 'classnames'
import { Text } from '@/aerosol/Text'
import { useRadioGroupContext } from '@/aerosol/RadioGroup/RadioGroupContext'
import styles from './RadioButton.styles.scss'

interface Props extends ComponentPropsWithRef<'input'> {
  label?: string
  description?: string
  children?: ReactNode
  isMarginless?: boolean
}

export interface RadioButtonChild {
  isChecked?: boolean
  disabled?: boolean
}

const RadioButton: FC<Props> = forwardRef(
  ({ label, description, children, isMarginless, value, id, className, disabled, ...rest }, ref) => {
    const { name, onChange, checkedValue, isDisabled, showInput } = useRadioGroupContext()
    const isChecked = value === checkedValue
    const defaultInputCss = classNames('form-radio', styles.inputDefault)
    const disabledCss = isDisabled || (disabled && styles.disabled)

    const renderDescription = () => {
      if (description) return <Text className={classNames(styles.description, disabledCss)}>{description}</Text>
      return null
    }

    const renderContent = () => {
      if (label) {
        return (
          <div className='h-full'>
            <Text isBold className={classNames(styles.label, disabledCss)}>
              {label}
            </Text>
            {renderDescription()}
            {children
              ? Children.map(children, (child) =>
                  isValidElement<RadioButtonChild>(child) ? cloneElement(child, { isChecked, disabled }) : null
                )
              : null}
          </div>
        )
      }
      return Children.map(children, (child) =>
        isValidElement<RadioButtonChild>(child) ? cloneElement(child, { isChecked, disabled }) : null
      )
    }

    return (
      <label htmlFor={id} className={classNames(styles.root, !isMarginless && 'mb-4', className)}>
        <input
          {...rest}
          id={id}
          value={value}
          ref={ref}
          className={showInput ? defaultInputCss : 'sr-only'}
          name={name}
          type='radio'
          disabled={isDisabled || disabled}
          checked={isChecked}
          onChange={({ target }) => onChange(target.value)}
        />
        {renderContent()}
      </label>
    )
  }
)

RadioButton.displayName = 'RadioButton'

export { RadioButton }
