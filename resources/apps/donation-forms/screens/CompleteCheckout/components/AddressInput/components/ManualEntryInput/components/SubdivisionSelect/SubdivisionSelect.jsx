import { memo, useCallback, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinnerThird } from '@fortawesome/pro-regular-svg-icons'
import { size as objectLength } from 'lodash'
import Input from '@/components/Input/Input'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import useSubdivisions from '@/hooks/useSubdivisions'
import { isEmpty } from '@/utilities/helpers'
import styles from './SubdivisionSelect.scss'

const SubdivisionSelect = ({ name, countryCode = null, value = '', onChange = () => null }) => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [subdivisions, subdivisionType] = useSubdivisions(countryCode)

  const isLoading = countryCode && !subdivisions
  const placeholderLabel = subdivisionType || t('placeholder_state')

  const showAsTextInput =
    isLoading || !countryCode || objectLength(subdivisions) === 0 || (value && !subdivisions?.[value])

  const { errorBag, setError } = useErrorBag()
  const [shouldValidate, setShouldValidate] = useState(!!value)

  const isValid = shouldValidate && isEmpty(errorBag[name])
  const isInvalid = shouldValidate && Boolean(errorBag[name])
  const backgroundImageColour = isValid ? '#22c55e' : isInvalid ? '#ef4444' : null

  const handleOnBlur = useCallback(
    (event) => {
      setShouldValidate(true)
      setError(name, isEmpty(event.target.value))
    },
    [name, setError]
  )

  const handleOnChange = useCallback(
    (event) => {
      handleOnBlur(event)
      onChange(event, subdivisions)
    },
    [onChange, handleOnBlur, subdivisions]
  )

  if (showAsTextInput) {
    return (
      <div className={styles.root}>
        {isLoading && <FontAwesomeIcon className={styles.loadingIcon} icon={faSpinnerThird} spin />}

        <Input
          name={name}
          defaultValue={value}
          placeholder={placeholderLabel}
          onBlur={handleOnBlur}
          onChange={handleOnChange}
          maxLength={2}
          showErrors={false}
          required
        />
      </div>
    )
  }

  return (
    <div className={styles.root}>
      <select
        className={classnames(
          'form-select',
          styles.select,
          isLoading && styles.loading,
          isValid && styles.valid,
          isInvalid && styles.invalid
        )}
        style={{
          ...(backgroundImageColour && {
            backgroundImage: `url("data:image/svg+xml,${encodeURIComponent(
              `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'><path stroke='${backgroundImageColour}' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/></svg>`
            )}")`,
          }),
        }}
        onBlur={handleOnBlur}
        onChange={handleOnChange}
        name={name}
        value={value}
        required
      >
        <option value=''>{placeholderLabel}</option>

        {Object.keys(subdivisions)?.map((subdivisionCode) => (
          <option key={subdivisionCode} value={subdivisionCode}>
            {subdivisions[subdivisionCode]}
          </option>
        ))}
      </select>
    </div>
  )
}

SubdivisionSelect.propTypes = {
  name: PropTypes.string,
  countryCode: PropTypes.string,
  onChange: PropTypes.func.isRequired,
  value: PropTypes.string,
}

export default memo(SubdivisionSelect)
