import { memo, useState } from 'react'
import { useRecoilValue, useSetRecoilState } from 'recoil'
import PlacesAutocomplete from 'react-places-autocomplete'
import { faMapMarkerAlt } from '@fortawesome/pro-regular-svg-icons'
import PropTypes from 'prop-types'
import google from 'google'
import { geocodeByAddress } from 'react-places-autocomplete'
import Input from '@/components/Input/Input'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import billingAddressState from '@/atoms/billingAddress'
import configState from '@/atoms/config'
import PlacesAutocompleteInput from './components/PlacesAutocompleteInput/PlacesAutocompleteInput'

const AutoCompleteInput = ({ className }) => {
  const t = useLocalization('screens.pay_with_credit_card')

  const config = useRecoilValue(configState)
  const setBillingAddress = useSetRecoilState(billingAddressState)

  const [input, setInput] = useState('')
  const { errorBag, setError, setShouldValidate } = useErrorBag()
  const [suggestionSelected, setSuggestionSelected] = useState(false)
  const shouldFetchSuggestions = input.length > 1

  const searchOptions = {
    strictBounds: false,
  }

  if (config.local_geolocation) {
    searchOptions.location = new google.maps.LatLng(...config.local_geolocation)
    searchOptions.radius = 50000
  }

  const getAddressComponents = async (address) => {
    const places = await geocodeByAddress(address)

    const resolve = (type, name) => {
      const match = places?.[0]?.address_components?.filter((component) => component.types.indexOf(type) > -1)
      return match?.[0]?.[name || 'long_name'] || null
    }

    const addressComponents = {
      billing_address1: [resolve('street_number'), resolve('route')].filter((value) => !!value).join(' ') || null,
      billing_address2: resolve('subpremise'),
      billing_city: resolve('sublocality') || resolve('locality') || resolve('postal_town'),
      billing_province_code: resolve('administrative_area_level_1', 'short_name'),
      billing_zip: resolve('postal_code', 'short_name'),
      billing_country_code: resolve('country', 'short_name'),
    }

    if (addressComponents.billing_country_code === 'GU') {
      addressComponents.billing_province_code = 'GU'
      addressComponents.billing_country_code = 'US'
    }

    return addressComponents
  }

  const handleOnChange = (address) => {
    setInput(address)
    setSuggestionSelected(false)
    setError('address_autocomplete')
  }

  const handleOnSelect = async (address) => {
    try {
      const addressComponents = await getAddressComponents(address)

      setInput(address)
      setBillingAddress(addressComponents)
      setSuggestionSelected(true)
      setError('address_autocomplete')

      setShouldValidate([
        'address_autocomplete',
        'billing_address1',
        'billing_address2',
        'billing_city',
        'billing_province_code',
        'billing_zip',
        'billing_country_code',
      ])
    } catch (error) {
      console.log(error)
    }
  }

  const clearFormInputAddress = () => {
    setInput('')
    setBillingAddress({
      billing_address1: null,
      billing_address2: null,
      billing_city: null,
      billing_province_code: null,
      billing_zip: null,
      billing_country_code: config.local_country,
    })

    setError('address_autocomplete', t('billing_address_required'))
  }

  const handleOnBlur = () => {
    if (!suggestionSelected) {
      clearFormInputAddress()
    }
  }

  const handleOnKeyDown = (e) => {
    if (e.key === 'Escape') {
      clearFormInputAddress()
    }
  }

  const handleOnError = (error, clearSuggestions) => {
    if (error !== 'ZERO_RESULTS') {
      console.error('Address autocomplete error:', error)
    }

    clearSuggestions()
  }

  return (
    <div onBlur={handleOnBlur}>
      <Input
        className={className}
        icon={faMapMarkerAlt}
        name='address_autocomplete'
        defaultValue={input}
        fauxPlaceholder={t('billing_address_placeholder')}
        error={errorBag.address_autocomplete}
      >
        <PlacesAutocomplete
          value={input}
          onChange={handleOnChange}
          onSelect={handleOnSelect}
          onError={handleOnError}
          searchOptions={searchOptions}
          shouldFetchSuggestions={shouldFetchSuggestions}
        >
          {({ getInputProps, suggestions, getSuggestionItemProps, loading }) => {
            return (
              <PlacesAutocompleteInput
                {...{ getInputProps, getSuggestionItemProps, loading, handleOnKeyDown, suggestions }}
              />
            )
          }}
        </PlacesAutocomplete>
      </Input>
    </div>
  )
}

AutoCompleteInput.propTypes = {
  className: PropTypes.string,
}

export default memo(AutoCompleteInput)
