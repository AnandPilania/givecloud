import { forwardRef, memo } from 'react'
import { useRecoilState } from 'recoil'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import useErrorBag from '@/hooks/useErrorBag'
import useLocalization from '@/hooks/useLocalization'
import useStateWhen from '@/hooks/useStateWhen'
import cardholderDataState from '@/atoms/cardholderData'

const CvvInput = forwardRef(
  ({ className, focusExpiryInput, usingHostedPaymentFields, onChange, ...unhandledProps }, ref) => {
    const t = useLocalization('screens.pay_with_credit_card')
    const [cardholderData, setCardholderData] = useRecoilState(cardholderDataState)

    const usingInput = !usingHostedPaymentFields
    const [input, setInputWhen] = useStateWhen((cvv) => setCardholderData({ ...cardholderData, cvv }))
    const { errorBag, shouldValidateBag, setShouldValidate } = useErrorBag()

    const handleOnChange = (e) => {
      const value = e.target.value || ''

      setInputWhen(value, /^\d*$/)
      onChange?.(e)

      if (value.length >= 3 && !shouldValidateBag.card_cvv) {
        setShouldValidate('card_cvv')
      }
    }

    const handleOnKeyDown = (e) => {
      if (e.key === 'Backspace' && e.target.value === '') {
        focusExpiryInput()
      }
    }

    return (
      <div className={className} data-private>
        <div id='inputPaymentCVV'>
          {usingInput && (
            <input
              ref={ref}
              type='tel'
              name='cvv'
              maxLength='4'
              className={classnames(!errorBag.cvv && 'valid')}
              placeholder={t('card_cvv')}
              x-autocompletetype='cc-csc'
              autocompletetype='cc-csc'
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

CvvInput.displayName = 'CvvInput'

CvvInput.propTypes = {
  className: PropTypes.string,
  focusExpiryInput: PropTypes.func,
  usingHostedPaymentFields: PropTypes.bool.isRequired,
  onChange: PropTypes.func,
}

export default memo(CvvInput)
