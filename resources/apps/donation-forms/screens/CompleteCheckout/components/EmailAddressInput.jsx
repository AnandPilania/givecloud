import { forwardRef, memo } from 'react'
import { useRecoilState } from 'recoil'
import { faEnvelopeOpen } from '@fortawesome/pro-regular-svg-icons'
import Input from '@/components/Input/Input'
import useLocalization from '@/hooks/useLocalization'
import formInputState from '@/atoms/formInput'
import { isEmpty } from '@/utilities/helpers'

const isInvalidEmailAddress = (email) => {
  const reg =
    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

  return !reg.test(String(email).toLowerCase().trim())
}

const EmailAddressInput = forwardRef((props, ref) => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [formInput, setFormInput] = useRecoilState(formInputState)

  const checkValidity = (value) => {
    if (isEmpty(value)) {
      throw t('email_address_required')
    } else if (isInvalidEmailAddress(value)) {
      throw t('email_address_invalid')
    }
  }

  const handleOnChange = (e) => {
    setFormInput({
      ...formInput,
      billing_email: e.target.value.trim(),
    })
  }

  return (
    <Input
      ref={ref}
      icon={faEnvelopeOpen}
      name='email_address'
      placeholder={t('email_address')}
      defaultValue={formInput.billing_email}
      onChange={handleOnChange}
      validator={checkValidity}
    />
  )
})

EmailAddressInput.displayName = 'EmailAddressInput'

export default memo(EmailAddressInput)
