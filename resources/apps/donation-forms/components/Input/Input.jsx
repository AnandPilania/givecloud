import { forwardRef, memo, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import useErrorBag from '@/hooks/useErrorBag'
import { except } from '@/utilities/object'
import { isEmpty, noop } from '@/utilities/helpers'
import styles from './Input.scss'

const Input = forwardRef((props, ref) => {
  let {
    className,
    type = 'text',
    defaultValue,
    icon,
    name,
    onBlur = noop,
    onChange = noop,
    required,
    validator,
    integerOnly,
    error,
    showErrors = true,
    children,
    fauxPlaceholder,
    ...unhandledProps
  } = props

  const [value, setValue] = useState(defaultValue || '')

  const { errorBag, setError, shouldValidateBag, setShouldValidate } = useErrorBag()
  const shouldValidate = Boolean(shouldValidateBag[name])
  const errorMessage = errorBag[name] || null

  const setErrorForNamedInput = (error) => {
    name && setError(name, error)
  }

  const setShouldValidateNamedInput = (shouldValidate) => {
    name && setShouldValidate(name, shouldValidate)
  }

  const isValid = shouldValidate && isEmpty(errorMessage)
  const isInvalid = (shouldValidate && Boolean(errorMessage)) || Boolean(error)
  const hasIcon = Boolean(icon)
  const hasNoChildren = Boolean(children) === false

  const showFauxPlaceholder = fauxPlaceholder && isEmpty(defaultValue)

  if (required && !validator) {
    validator = isEmpty
  }

  const validate = (e, shouldValidate) => {
    if (validator && shouldValidate) {
      try {
        setErrorForNamedInput(validator(e.target.value, e, shouldValidate))
      } catch (err) {
        setErrorForNamedInput(err)
      }
    }
  }

  const handleOnBlur = (e) => {
    if (validator) {
      validate(e, true)
    }

    setShouldValidateNamedInput(true)
    onBlur(e)
  }

  const handleOnChange = (e) => {
    setValue(e.target.value)
    validate(e, shouldValidate)
    onChange(e)
  }

  const handleOnKeyPress = (e) => {
    const keyCode = String.fromCharCode(e.which || e.keyCode)

    if (integerOnly && !/\d/.test(keyCode)) {
      e.preventDefault()
    }
  }

  return (
    <div
      className={classnames(
        styles.root,
        className,
        hasIcon && styles.hasIcon,
        isValid && styles.valid,
        isInvalid && styles.invalid
      )}
    >
      {hasIcon && (
        <div className={styles.iconContainer}>
          <FontAwesomeIcon className={styles.icon} icon={icon} />
        </div>
      )}

      {hasNoChildren && (
        <input
          ref={ref}
          className={styles.input}
          name={name}
          type={type}
          value={value}
          onBlur={handleOnBlur}
          onChange={handleOnChange}
          onKeyPress={handleOnKeyPress}
          {...except(unhandledProps, ['value'])}
        />
      )}

      {showFauxPlaceholder && (
        <div className={styles.fauxPlaceholder}>
          {fauxPlaceholder}
          {/*
            this hidden input is required in order to prevent 1password from attaching it self to the
            input actual input if the fauxPlaceholder has trigger words like "email" or "address" in it.
            because this hidden input is an immediate sibling 1password will associate with trigger words
            to the hidden input and not the actual input
          */}
          <input type='hidden' />
        </div>
      )}

      {typeof children === 'function'
        ? children({ isInvalid, errorMessage: error || errorMessage, handleOnBlur, handleOnChange })
        : children}

      {isInvalid && showErrors && <span className={styles.errorMessage}>{error || errorMessage}</span>}
    </div>
  )
})

Input.displayName = 'Input'

Input.propTypes = {
  className: PropTypes.string,
  type: PropTypes.string,
  defaultValue: PropTypes.string,
  icon: PropTypes.any,
  name: PropTypes.string,
  onBlur: PropTypes.func,
  onChange: PropTypes.func,
  required: PropTypes.bool,
  validator: PropTypes.func,
  integerOnly: PropTypes.bool,
  error: PropTypes.string,
  showErrors: PropTypes.bool,
  children: PropTypes.oneOfType([PropTypes.node, PropTypes.func]),
  fauxPlaceholder: PropTypes.string,
}

export default memo(Input)
