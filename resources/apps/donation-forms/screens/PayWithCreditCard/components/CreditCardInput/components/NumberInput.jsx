import { memo, forwardRef } from 'react'
import { useRecoilState } from 'recoil'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import Givecloud from 'givecloud'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import useStateWhen from '@/hooks/useStateWhen'
import cardholderDataState from '@/atoms/cardholderData'

const NumberInput = forwardRef(
  ({ className, setCardBrand, focusExpiryInput, usingHostedPaymentFields, onChange, ...unhandledProps }, ref) => {
    const t = useLocalization('screens.pay_with_credit_card')
    const [cardholderData, setCardholderData] = useRecoilState(cardholderDataState)

    const usingInput = !usingHostedPaymentFields
    const { errorBag } = useErrorBag()

    const [input, setInputWhen] = useStateWhen((number) =>
      setCardholderData({ ...cardholderData, number: number.replace(/ /g, '') })
    )

    const handleOnChange = (e) => {
      const value = e.target.value

      setInputWhen(value, /^\d{0,3}$/)
      setInputWhen(value, /^\d{4}$/, `${value} `)
      setInputWhen(value, /^(\d{4})(\d)$/, (m) => `${m[1]} ${m[2]}`)
      setInputWhen(value, /^\d{4} \d{0,3}$/)
      setInputWhen(value, /^\d{4} \d{4}$/, `${value} `)
      setInputWhen(value, /^(\d{4} \d{4})(\d)$/, (m) => `${m[1]} ${m[2]}`)
      setInputWhen(value, /^\d{4} \d{4} \d{0,3}$/)
      setInputWhen(value, /^\d{4} \d{4} \d{4}$/, `${value} `)
      setInputWhen(value, /^(\d{4} \d{4} \d{4})(\d)$/, (m) => `${m[1]} ${m[2]}`)
      setInputWhen(value, /^\d{4} \d{4} \d{4} \d{0,4}$/)
      setInputWhen(value, /^(\d{4} \d{4} \d{4} \d{4})(\d)$/, (m) => `${m[1]} ${m[2]}`)
      setInputWhen(value, /^\d{4} \d{4} \d{4} \d{4} \d+$/)
      setInputWhen(value, /^(\d{4})(\d{4})(\d{4})(\d{4})(\d*)$/, (m) =>
        `${m[1]} ${m[2]} ${m[3]} ${m[4]} ${m[5]}`.trim()
      )
      onChange?.(e)

      setCardBrand(Givecloud.CardholderData.getNumberType(value))

      if (Givecloud.CardholderData.validNumber(value)) {
        focusExpiryInput()
      }
    }

    const handleOnKeyDown = (e) => {
      const value = e.target.value

      if (e.key === 'Backspace') {
        e.preventDefault()

        const selectedText = String(window.getSelection?.())

        if (selectedText) {
          setInputWhen(value.replace(selectedText, '').replace(/[ ]+/, ' '))
        } else {
          setInputWhen(value, /^(.*?)\s?\d?$/, (m) => m[1])
        }
      }
    }

    return (
      <div className={className} data-private>
        <div id='inputPaymentNumber'>
          {usingInput && (
            <input
              ref={ref}
              type='tel'
              name='number'
              maxLength='19'
              className={classnames(!errorBag.number && 'valid')}
              placeholder={t('card_number')}
              x-autocompletetype='cc-number'
              autocompletetype='cc-number'
              autoCapitalize='off'
              autoCorrect='off'
              spellCheck='off'
              onChange={handleOnChange}
              onKeyDown={handleOnKeyDown}
              value={input}
              {...unhandledProps}
            />
          )}
        </div>
      </div>
    )
  }
)

NumberInput.displayName = 'NumberInput'

NumberInput.propTypes = {
  className: PropTypes.string,
  focusExpiryInput: PropTypes.func,
  setCardBrand: PropTypes.func,
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  onChange: PropTypes.func,
}

export default memo(NumberInput)
