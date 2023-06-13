import type { FC, FocusEvent, ComponentPropsWithRef, LegacyRef, HTMLProps } from 'react'
import type { IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { useEffect, useState, forwardRef } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import { Label } from '@/aerosol/Label'
import styles from './Input.styles.scss'

type InputRefType = LegacyRef<HTMLInputElement> | undefined
type CharCountType = number | undefined
type ErrorsType = string[] | null | undefined
type AdditionalProps = ComponentPropsWithRef<'input'> & HTMLProps<HTMLInputElement>

export interface Props extends AdditionalProps {
  icon?: IconDefinition | undefined
  charCountMax?: CharCountType
  errors?: ErrorsType
  isOptional?: boolean
  isDisabled?: boolean
  isLabelHidden?: boolean
  isMarginless?: boolean
  isReadOnly?: boolean
  addOn?: string
}

const Input: FC<Props> = forwardRef(
  (
    {
      addOn,
      isReadOnly,
      isMarginless,
      icon,
      isLabelHidden,
      charCountMax,
      errors,
      isOptional,
      label,
      name,
      isDisabled,
      value,
      className,
      onBlur,
      onFocus,
      ...rest
    },
    ref: InputRefType
  ) => {
    const [isFocused, setIsFocused] = useState(false)
    const [characterCount, setCharacterCount] = useState(0)
    const hasErrors = !!errors?.filter((error) => !!error).length

    useEffect(() => {
      setCharacterCount(String(value)?.length)
      return () => setCharacterCount(0)
    }, [value])

    const renderErrorMessages = () => {
      if (hasErrors)
        return errors?.slice(0, 1).map((error, index) => (
          <p
            id={name}
            aria-live='polite'
            key={`error-item-${error}-${index}`}
            data-testid={`error-${index}`}
            className={styles.errorMessage}
          >
            {error}
          </p>
        ))
    }

    const renderIcon = () => {
      if (hasErrors || icon) {
        return (
          <div className={styles.iconContainer}>
            <FontAwesomeIcon
              title={hasErrors ? faExclamationCircle.iconName : icon!.iconName}
              icon={hasErrors ? faExclamationCircle : icon!}
              className={hasErrors ? styles.errorIcon : styles.icon}
              aria-hidden='true'
            />
          </div>
        )
      }
      return null
    }

    const renderLabel = () => {
      if (!label) return null
      if (label && !isLabelHidden)
        return (
          <Label isError={hasErrors} isDisabled={isDisabled} isOptional={isOptional} htmlFor={name}>
            {label}
          </Label>
        )
      return (
        <label htmlFor={name} className={classnames(styles.hidden)}>
          {label}
        </label>
      )
    }

    const renderCharCount = () => {
      if (charCountMax && isFocused)
        return (
          <span className={classnames(styles.charCount, isLabelHidden ? styles.noLabel : styles.default)}>
            {characterCount}/{charCountMax}
          </span>
        )
      return null
    }

    const renderAddOn = () => (addOn ? <span className={styles.addOn}>{addOn}</span> : null)

    const handleFocus = (e: FocusEvent<HTMLInputElement>) => {
      onFocus?.(e)
      setIsFocused(true)
    }

    const handleBlur = (e: FocusEvent<HTMLInputElement>) => {
      onBlur?.(e)
      setIsFocused(false)
    }

    const css = classnames(
      styles.input,
      addOn ? styles.addOnBorder : styles.defaultBorder,
      hasErrors ? styles.error : isReadOnly ? styles.readonly : styles.default,
      isDisabled && styles.disabled,
      (hasErrors || icon) && styles.paddingRight,
      className
    )

    return (
      <div className={classnames(styles.root, isMarginless ? 'm-0' : 'mb-6', className)}>
        {renderLabel()}
        {renderCharCount()}
        <div className={styles.root}>
          <div className={styles.inputWrapper}>
            {renderAddOn()}
            <input
              {...rest}
              ref={ref}
              readOnly={isReadOnly}
              value={value}
              maxLength={charCountMax}
              onFocus={handleFocus}
              onBlur={handleBlur}
              required={!isOptional}
              disabled={isDisabled}
              id={name}
              name={name}
              aria-invalid={!!hasErrors}
              className={css}
            />
          </div>
          {renderErrorMessages()}
          {renderIcon()}
        </div>
      </div>
    )
  }
)

Input.displayName = 'Input'

Input.defaultProps = {
  errors: [],
  isDisabled: false,
  isOptional: false,
  isMarginless: false,
  isLabelHidden: false,
}

export { Input }
export { Props as InputProps }
