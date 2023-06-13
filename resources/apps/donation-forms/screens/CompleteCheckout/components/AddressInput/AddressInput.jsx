import { memo, useState } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import useLocalization from '@/hooks/useLocalization'
import AutoCompleteInput from './components/AutoCompleteInput/AutoCompleteInput'
import ManualEntryInput from './components/ManualEntryInput/ManualEntryInput'
import useAnalytics from '@/hooks/useAnalytics'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'
import styles from './AddressInput.scss'

const AddressInput = () => {
  const t = useLocalization('screens.pay_with_credit_card')

  const config = useRecoilValue(configState)
  const collectEvent = useAnalytics({ collectOnce: true })
  const [formInput, setFormInput] = useRecoilState(formInputState)

  const [useAutoComplete, setUseAutoComplete] = useState(true)

  const buttonLabel = useAutoComplete ? t('edit_manually') : t('search_for_address')
  const InputComponent = useAutoComplete ? AutoCompleteInput : ManualEntryInput

  const switchToAutocomplete = () => {
    setUseAutoComplete(true)

    setFormInput({
      ...formInput,
      ...{
        billing_address1: null,
        billing_address2: null,
        billing_city: null,
        billing_province_code: null,
        billing_zip: null,
        billing_country_code: config.local_country,
      },
    })

    collectEvent({ event_name: 'autocomplete_address_click' })
  }

  const switchToManual = () => {
    setUseAutoComplete(false)

    collectEvent({ event_name: 'manual_address_click' })
  }

  const switchModes = () => {
    useAutoComplete ? switchToManual() : switchToAutocomplete()
  }

  return (
    <div className={styles.root}>
      <InputComponent className={styles.input} />

      <button id='addressInputSwitchMode' className={styles.button} onClick={switchModes}>
        {buttonLabel}
      </button>
    </div>
  )
}

export default memo(AddressInput)
