import { memo, useState } from 'react'
import { useRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { AnimatePresence, motion } from 'framer-motion'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'
import CurrencySelector from '@/components/CurrencySelector/CurrencySelector'
import Portal from '@/components/Portal/Portal'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useCurrencySymbol from '@/hooks/useCurrencySymbol'
import useCurrencySymbolPlacement from '@/hooks/useCurrencySymbolPlacement'
import useLocalization from '@/hooks/useLocalization'
import formInputState from '@/atoms/formInput'
import { RIGHT_HAND_SIDE_PLACEMENT } from '@/utilities/currency'
import styles from './AmountInput.scss'
import { FocusTrap } from '@headlessui/react'

const AmountInput = ({ show, setShow, handleAmount }) => {
  const t = useLocalization('components.amount_selector')

  const currencySymbol = useCurrencySymbol()
  const currencySymbolPlacement = useCurrencySymbolPlacement()

  const formatCurrency = useCurrencyFormatter({
    autoFractionDigits: false,
    showCurrencyCode: false,
    showCurrencySymbol: false,
    useGrouping: true,
  })

  const [amount, setAmount] = useState('')
  const [formInput, setFormInput] = useRecoilState(formInputState)

  const getFloatFromFormattedValue = (formattedValue) => {
    const value = parseInt(formattedValue.replace(/[^0-9]/g, ''), 10) / 100
    return value || 0
  }

  const handleOnChange = (e) => {
    const value = getFloatFromFormattedValue(e.target.value)
    setAmount(value ? formatCurrency(value) : '')
  }

  const handleOnKeyDown = (e) => {
    if (e.key === 'Enter') {
      saveAndClose()
    } else if (e.key === 'Escape') {
      setShow(false)
    }
  }

  const handleOnFocus = (e) => e.target.select()

  const saveAndClose = () => {
    if (amount) {
      const formattedAmount = getFloatFromFormattedValue(amount)

      setFormInput({ ...formInput, item: { ...formInput.item, amt: formattedAmount } })
      handleAmount && handleAmount(formattedAmount)
    }

    setShow(false)
  }

  const rhsCurrencySymbolPlacement = currencySymbolPlacement === RIGHT_HAND_SIDE_PLACEMENT

  return (
    <Portal>
      <AnimatePresence>
        {show && (
          <motion.div
            className={styles.root}
            initial={{ opacity: 0, scale: 0 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0 }}
            transition={{ duration: 0.2, ease: 'easeInOut' }}
          >
            <FocusTrap>
              <div className={classnames(styles.inputWrap, rhsCurrencySymbolPlacement && styles.rightHandSide)}>
                <div className={styles.currencySymbol}>{currencySymbol}</div>
                <input
                  type='tel'
                  autoFocus
                  value={amount}
                  onChange={handleOnChange}
                  onKeyDown={handleOnKeyDown}
                  onFocus={handleOnFocus}
                  placeholder={formatCurrency(0)}
                />
                <CurrencySelector className={styles.currencySelector} clean />
              </div>

              <button className={styles.doneButton} onClick={saveAndClose}>
                <FontAwesomeIcon className={styles.icon} icon={faCheck} /> {t('done_button')}
              </button>
            </FocusTrap>
          </motion.div>
        )}
      </AnimatePresence>
    </Portal>
  )
}

AmountInput.propTypes = {
  show: PropTypes.bool.isRequired,
  setShow: PropTypes.func.isRequired,
  handleAmount: PropTypes.func,
}

export default memo(AmountInput)
