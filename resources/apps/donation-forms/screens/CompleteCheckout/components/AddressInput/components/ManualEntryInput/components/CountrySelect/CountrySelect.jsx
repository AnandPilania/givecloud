import { memo, useCallback, useState } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinnerThird } from '@fortawesome/pro-regular-svg-icons'
import useCountries from '@/hooks/useCountries'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import { isEmpty } from '@/utilities/helpers'
import styles from './CountrySelect.scss'

const CountrySelect = ({ name, value = '', onChange = () => null }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const countries = useCountries()
  const isLoading = !countries

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
      onChange(event, countries)
    },
    [countries, onChange, handleOnBlur]
  )

  return (
    <div className={styles.root}>
      {isLoading && <FontAwesomeIcon className={styles.loadingIcon} icon={faSpinnerThird} spin />}

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
        onBlur={handleOnChange}
        onChange={handleOnChange}
        name={name}
        value={value}
        required
      >
        <option value=''>{t('placeholder_country')}</option>

        {!!countries &&
          Object.keys(countries)?.map((countryCode) => (
            <option key={countryCode} value={countryCode}>
              {countries[countryCode]}
            </option>
          ))}
      </select>
    </div>
  )
}

CountrySelect.propTypes = {
  name: PropTypes.string,
  onChange: PropTypes.func.isRequired,
  value: PropTypes.string,
}

export default memo(CountrySelect)
