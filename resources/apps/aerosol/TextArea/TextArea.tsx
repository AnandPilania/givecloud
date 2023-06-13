import type { ComponentPropsWithRef, FC, Ref, FocusEvent, ChangeEvent, HTMLProps } from 'react'
import { useEffect, useState, forwardRef, useRef } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import { useCombinedRefs } from '@/shared/hooks'
import { Label } from '@/aerosol/Label'
import styles from './TextArea.styles.scss'

type ErrorsType = string[] | null | undefined
type TextAreaRefType = Ref<HTMLTextAreaElement>
type AdditionalProps = ComponentPropsWithRef<'textarea'> & HTMLProps<HTMLTextAreaElement>

interface Props extends AdditionalProps {
  charCountMax?: number
  errors?: ErrorsType
  isOptional?: boolean
  label?: string
  isAutoGrowing?: boolean
  isReadOnly?: boolean
  isMarginless?: boolean
  isLabelHidden?: boolean
  isDisabled?: boolean
}

const TextArea: FC<Props> = forwardRef(
  (
    {
      isAutoGrowing,
      isReadOnly,
      isMarginless,
      isLabelHidden,
      charCountMax,
      errors,
      isOptional,
      label,
      name,
      isDisabled,
      value,
      onFocus,
      onBlur,
      onChange,
      className,
      rows,
      ...rest
    },
    ref: TextAreaRefType
  ) => {
    const [isFocused, setIsFocused] = useState(false)
    const innerRef = useRef(null)
    const combinedRef = useCombinedRefs(ref, innerRef)
    const [textAreaHeight, setTextAreaHeight] = useState('auto')
    const [characterCount, setCharacterCount] = useState(0)
    const hasErrors = !!errors?.filter((error) => !!error).length

    useEffect(() => {
      if (charCountMax) setCharacterCount(String(value)?.length)
      if (isAutoGrowing) setTextAreaHeight(`${combinedRef?.current?.scrollHeight}px`)
      return () => setCharacterCount(0)
    }, [value])

    const renderErrorMessages = () => {
      if (hasErrors)
        return errors.slice(0, 1).map((error, index) => (
          <p key={`error-item-${error}-${index}`} data-testid={`error-${index}`} className={styles.errorMessage}>
            {error}
          </p>
        ))
    }

    const renderIcon = () => {
      if (hasErrors) {
        return (
          <div className={styles.iconContainer}>
            <FontAwesomeIcon
              title={faExclamationCircle.iconName}
              icon={faExclamationCircle}
              className={styles.errorIcon}
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

    const handleFocus = (e: FocusEvent<HTMLTextAreaElement, Element>) => {
      onFocus?.(e)
      setIsFocused(true)
    }

    const handleBlur = (e: FocusEvent<HTMLTextAreaElement, Element>) => {
      onBlur?.(e)
      setIsFocused(false)
    }

    const handleChange = (e: ChangeEvent<HTMLTextAreaElement>) => {
      setTextAreaHeight('auto')
      onChange?.(e)
    }

    const css = classnames(
      styles.input,
      hasErrors ? styles.error : styles.default,
      isReadOnly && styles.readonly,
      isDisabled && styles.disabled,
      hasErrors && styles.paddingRight,
      isAutoGrowing && styles.autoGrow
    )

    return (
      <div
        className={classnames(
          styles.root,
          isMarginless ? 'm-0' : 'mb-8',
          (isLabelHidden || charCountMax) && 'mt-4',
          className
        )}
      >
        {renderLabel()}
        {renderCharCount()}
        <div
          style={{
            height: textAreaHeight,
          }}
          className={styles.root}
        >
          <textarea
            {...rest}
            rows={isAutoGrowing ? 1 : rows}
            ref={combinedRef}
            value={value}
            readOnly={isReadOnly}
            maxLength={charCountMax}
            onChange={handleChange}
            onFocus={handleFocus}
            onBlur={handleBlur}
            required={!isOptional}
            disabled={isDisabled}
            id={name}
            name={name}
            aria-invalid={!!hasErrors}
            className={css}
          />
          {renderIcon()}
        </div>
        {renderErrorMessages()}
      </div>
    )
  }
)

TextArea.displayName = 'TextArea'

TextArea.defaultProps = {
  errors: [],
  isDisabled: false,
  isOptional: false,
  isMarginless: false,
  isLabelHidden: false,
  isAutoGrowing: false,
}

export { TextArea }
