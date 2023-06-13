import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinnerThird } from '@fortawesome/pro-regular-svg-icons'
import classnames from 'classnames'
import { isNotEmpty } from '@/utilities/helpers'
import inputStyles from '@/components/Input/Input.scss'
import styles from './PlacesAutocompleteInput.scss'

const PlacesAutocompleteInput = ({ getInputProps, getSuggestionItemProps, loading, handleOnKeyDown, suggestions }) => {
  const suggestionsAvailable = isNotEmpty(suggestions)
  const showLoading = loading && !suggestionsAvailable
  const showDropdown = showLoading || suggestionsAvailable

  return (
    <>
      <input
        id='placesAutocompleteInput'
        data-private='lipsum'
        {...getInputProps({
          className: classnames(styles.input, inputStyles.input),
          onKeyDown: handleOnKeyDown,
        })}
      />

      {showDropdown && (
        <div className={styles.dropdown}>
          {showLoading && (
            <div className={styles.loading}>
              <FontAwesomeIcon icon={faSpinnerThird} spin />
            </div>
          )}

          {suggestionsAvailable && (
            <ul className={styles.suggestions}>
              {suggestions.slice(0, 4).map((suggestion) => {
                const className = classnames(suggestion.active && styles.activeSuggestion)
                return (
                  <li key={suggestion.placeId} {...getSuggestionItemProps(suggestion, { className })}>
                    <span data-private>{suggestion.description}</span>
                  </li>
                )
              })}
            </ul>
          )}
        </div>
      )}
    </>
  )
}

PlacesAutocompleteInput.propTypes = {
  getInputProps: PropTypes.func,
  getSuggestionItemProps: PropTypes.func,
  loading: PropTypes.bool,
  handleOnKeyDown: PropTypes.func,
  suggestions: PropTypes.array,
}

export default PlacesAutocompleteInput
