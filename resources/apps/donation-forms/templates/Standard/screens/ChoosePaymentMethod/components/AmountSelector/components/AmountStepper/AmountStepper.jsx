import { memo, useState } from 'react'
import { useRecoilState, useRecoilValue, useSetRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { uniqueId } from 'lodash'
import AmountInput from '@/components/AmountInput/AmountInput'
import AmountFlipperContainer from '@/components/AmountFlipperContainer/AmountFlipperContainer'
import FloatingIcons from '@/components/FloatingIcons/FloatingIcons'
import useAnalytics from '@/hooks/useAnalytics'
import amountSelectorState from '@/atoms/amountSelector'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'
import { closestIndexOf } from '@/utilities/array'
import { isPrimaryColourDark } from '@/utilities/theme'
import PlusIcon from './images/Plus.svg?react'
import MinusIcon from './images/Minus.svg?react'
import styles from './AmountStepper.scss'

const AmountStepper = ({ small }) => {
  const config = useRecoilValue(configState)
  const collectEvent = useAnalytics({ collectOnce: true })
  const [formInput, setFormInput] = useRecoilState(formInputState)
  const [showAmountInput, setShowAmountInput] = useState(false)
  const [showTabToChange, setShowTabToChange] = useState(false)
  const setAmountSelector = useSetRecoilState(amountSelectorState)

  const [prevFloatingIcons, setPrevFloatingIcons] = useState(null)
  const [nextFloatingIcons, setNextFloatingIcons] = useState(null)

  const amount = formInput.item.amt
  const presetAmounts = config.preset_amounts

  const selectedIndex = closestIndexOf(presetAmounts, amount)

  const canGoBack = selectedIndex > 0
  const canGoForward = selectedIndex < presetAmounts.length - 1

  const setAmountUsingPreset = (index) => {
    setFormInput({ ...formInput, item: { ...formInput.item, amt: presetAmounts[index] } })
  }

  const selectPrevAmount = () => {
    if (selectedIndex > 0) {
      setAmountSelector({ amountChanged: true, minusClicked: true })
      setAmountUsingPreset(selectedIndex - 1)

      if (config.floating_icons.amount_stepper.prev) {
        setPrevFloatingIcons(uniqueId('AmountStepperFloatingIcons'))
      }

      collectEvent({ event_name: 'decrease_amount_click' })
    }
  }

  const selectNextAmount = () => {
    if (selectedIndex < presetAmounts.length - 1) {
      setAmountSelector({ amountChanged: true, plusClicked: true })
      setAmountUsingPreset(selectedIndex + 1)

      if (config.floating_icons.amount_stepper.next) {
        setNextFloatingIcons(uniqueId('AmountStepperFloatingIcons'))
      }

      collectEvent({ event_name: 'increase_amount_click' })
    }
  }

  return (
    <div className={styles.root}>
      <div
        className={styles.amountStepper}
        onMouseEnter={() => setShowTabToChange(true)}
        onMouseLeave={() => setShowTabToChange(false)}
      >
        <button
          className={classnames(styles.prevButton, canGoBack && styles.clickable)}
          onClick={selectPrevAmount}
          aria-label='decrease donation amount'
        >
          <div className={classnames(styles.icon, isPrimaryColourDark && styles.darkPrimaryColour)}>
            <MinusIcon />
          </div>
          {prevFloatingIcons && <FloatingIcons iconKey={prevFloatingIcons} condition={true} />}
        </button>

        <AmountFlipperContainer showTabToChange={showTabToChange} setShow={setShowAmountInput} small={small} />

        <button
          className={classnames(styles.nextButton, canGoForward && styles.clickable)}
          onClick={selectNextAmount}
          aria-label='increase donation amount'
        >
          <div
            className={classnames(
              styles.icon,
              isPrimaryColourDark && styles.darkPrimaryColour,
              amount < 50 && styles.pulseAnimation
            )}
          >
            <PlusIcon />
          </div>
          {nextFloatingIcons && <FloatingIcons iconKey={nextFloatingIcons} condition={true} large />}
        </button>
      </div>

      <AmountInput show={showAmountInput} setShow={setShowAmountInput} />
    </div>
  )
}

AmountStepper.propTypes = {
  small: PropTypes.bool,
}

export default memo(AmountStepper)
