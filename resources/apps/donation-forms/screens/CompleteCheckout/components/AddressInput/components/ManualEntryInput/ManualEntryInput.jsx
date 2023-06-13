import { memo } from 'react'
import { useRecoilState } from 'recoil'
import Input from '@/components/Input/Input'
import useLocalization from '@/hooks/useLocalization'
import CountrySelect from './components/CountrySelect/CountrySelect'
import SubdivisionSelect from './components/SubdivisionSelect/SubdivisionSelect'
import billingAddressState from '@/atoms/billingAddress'
import styles from './ManualEntryInput.scss'

const ManualEntryInput = () => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [billingAddress, setBillingAddress] = useRecoilState(billingAddressState)

  const handleOnChange = (e) => {
    setBillingAddress({
      ...billingAddress,
      [e.target.name]: e.target.value,
    })
  }

  return (
    <div className={styles.root}>
      <div className={styles.row}>
        <Input
          name='billing_address1'
          defaultValue={billingAddress.billing_address1}
          placeholder={t('placeholder_address')}
          onChange={handleOnChange}
          showErrors={false}
          required
          data-private='lipsum'
        />
        <Input
          name='billing_city'
          defaultValue={billingAddress.billing_city}
          placeholder={t('placeholder_city')}
          showErrors={false}
          required
          onChange={handleOnChange}
        />
      </div>

      <div className={styles.row}>
        <SubdivisionSelect
          countryCode={billingAddress.billing_country_code}
          name='billing_province_code'
          value={billingAddress.billing_province_code || ''}
          onChange={handleOnChange}
        />
        <CountrySelect
          name='billing_country_code'
          value={billingAddress.billing_country_code || ''}
          onChange={handleOnChange}
        />
      </div>

      <div className={styles.row}>
        <Input
          name='billing_zip'
          defaultValue={billingAddress.billing_zip}
          placeholder={t('placeholder_zip')}
          onChange={handleOnChange}
          showErrors={false}
          required
        />
      </div>
    </div>
  )
}

export default memo(ManualEntryInput)
