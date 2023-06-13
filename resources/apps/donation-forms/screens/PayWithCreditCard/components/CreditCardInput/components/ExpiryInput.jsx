import { forwardRef, memo } from 'react'
import { useRecoilState } from 'recoil'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import useStateWhen from '@/hooks/useStateWhen'
import cardholderDataState from '@/atoms/cardholderData'

const ExpiryInput = forwardRef(
  ({ className, focusCvvInput, focusNumberInput, usingHostedPaymentFields, onChange, ...unhandledProps }, ref) => {
    const t = useLocalization('screens.pay_with_credit_card')
    const [cardholderData, setCardholderData] = useRecoilState(cardholderDataState)

    const usingInput = !usingHostedPaymentFields
    const [input, setInputWhen] = useStateWhen((exp) => setCardholderData({ ...cardholderData, exp }))
    const { errorBag } = useErrorBag()

    const handleOnChange = (e) => {
      const value = e.target.value

      setInputWhen(value, /^[0-1]$/)
      setInputWhen(value, /^[2-9]$/, () => `0${value} / `)
      setInputWhen(value, /^0[1-9]$/, () => `${value} / `)
      setInputWhen(value, /^1[0-2]$/, () => `${value} / `)
      setInputWhen(value, /^1[3]$/, () => `0${value[0]} / ${value[1]}`)
      setInputWhen(value, /^1[4-9]$/, () => `0${value[0]} / 0${value[1]}`)
      setInputWhen(value, /^(?:0[1-9]|1[0-2]) [/] \d?\d?$/, () => value)
      onChange?.(e)

      if (value.match(/^(?:0[1-9]|1[0-2]) [/] \d\d$/)) {
        focusCvvInput()
      }
    }

    const handleOnKeyDown = (e) => {
      const value = e.target.value

      if (e.key === 'Backspace') {
        e.preventDefault()
        setInputWhen(value, /^(?:0[1-9]|1[0-2]) [/] \d\d?$/, () => value.substring(0, value.length - 1))
        setInputWhen(value, /^(0[1-9]|1[0-2]) [/] $/, (matches) => matches[1])
        setInputWhen(value, /^1[0-2]$/, () => '1')
        setInputWhen(value, /^0[1-9]$/, () => '')
        setInputWhen(value, /^[01]$/, () => '')

        if (value === '') {
          focusNumberInput()
        }
      }
    }

    return (
      <div className={className} data-private>
        <div id='inputPaymentExpiry'>
          {usingInput && (
            <input
              ref={ref}
              type='tel'
              name='exp'
              maxLength='7'
              className={classnames(!errorBag.exp && 'valid')}
              placeholder={t('card_exp')}
              x-autocompletetype='cc-exp'
              autocompletetype='cc-exp'
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

ExpiryInput.displayName = 'ExpiryInput'

ExpiryInput.propTypes = {
  className: PropTypes.string,
  focusCvvInput: PropTypes.func,
  focusNumberInput: PropTypes.func,
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  onChange: PropTypes.func,
}

export default memo(ExpiryInput)
