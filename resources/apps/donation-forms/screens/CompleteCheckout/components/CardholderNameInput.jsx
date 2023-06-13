import { memo } from 'react'
import { useRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import { faUserCircle } from '@fortawesome/pro-regular-svg-icons'
import Input from '@/components/Input/Input'
import useLocalization from '@/hooks/useLocalization'
import cardholderNameState from '@/atoms/cardholderName'
import { isEmpty } from '@/utilities/helpers'
import { lastName } from '@/utilities/string'

const CardholderNameInput = ({ placeholder }) => {
  const t = useLocalization('screens.pay_with_credit_card')
  const [cardholderName, setCardholderName] = useRecoilState(cardholderNameState)

  const checkValidity = (value, e, shouldValidate) => {
    if (isEmpty(value)) {
      throw t('cardholder_name_required')
    }

    if (shouldValidate && isEmpty(lastName(value))) {
      throw t('cardholder_last_name_required')
    }
  }

  const handleOnChange = (e) => {
    setCardholderName(e.target.value.trim())
  }

  return (
    <Input
      icon={faUserCircle}
      name='cardholder_name'
      placeholder={placeholder || t('cardholder_name')}
      defaultValue={cardholderName}
      onChange={handleOnChange}
      validator={checkValidity}
    />
  )
}

CardholderNameInput.propTypes = {
  placeholder: PropTypes.string,
}

export default memo(CardholderNameInput)
