import { memo, useMemo, useState } from 'react'
import { useRecoilState, useRecoilValue, useSetRecoilState } from 'recoil'
import { uniqueId } from 'lodash'
import PropTypes from 'prop-types'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'
import pendingContributionState from '@/atoms/pendingContribution'
import amountSelectorState from '@/atoms/amountSelector'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import AmountInput from '@/components/AmountInput/AmountInput'
import AmountTile from './components/AmountTile'
import styles from './AmountTiles.scss'

const CUSTOM_INPUT_ID = 'CustomAmount'

const AmountTiles = ({ showInput, setShowInput }) => {
  const [formInput, setFormInput] = useRecoilState(formInputState)
  const setAmountSelector = useSetRecoilState(amountSelectorState)
  const [otherTile, setOtherTile] = useState({ id: CUSTOM_INPUT_ID, value: 0, label: 'Other', isSelected: false })

  const pendingContribution = useRecoilValue(pendingContributionState)
  const config = useRecoilValue(configState)
  const formatCurrency = useCurrencyFormatter({ showCurrencyCode: false })

  const defaultTiles = useMemo(
    () =>
      config.default_amounts.map((amount) => {
        return {
          id: uniqueId('AmountTile'),
          value: amount,
          label: formatCurrency(amount, pendingContribution.currency_code),
          isSelected: amount === pendingContribution.amount,
        }
      }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [pendingContribution.amount, pendingContribution.currency_code]
  )

  const updateAmountTiles = (amount) => {
    const allTiles = [...defaultTiles, otherTile]
    const amountValues = allTiles.map((tile) => tile.value)

    setOtherTile((prev) => {
      return { ...prev, value: amount, isSelected: !amountValues.includes(amount) }
    })
    setAmountSelector({ amountChanged: true })
  }

  const handleAmountTile = (amount, id) => {
    if (id === CUSTOM_INPUT_ID) {
      setShowInput(true)
    } else {
      setFormInput({ ...formInput, item: { ...formInput.item, amt: amount } })
      updateAmountTiles(amount)
    }
  }

  return (
    <div className={styles.root}>
      {[...defaultTiles, otherTile]?.map((tile) => (
        <AmountTile key={tile.id} handleAmountTile={handleAmountTile} {...tile} />
      ))}

      <AmountInput show={showInput} setShow={setShowInput} handleAmount={updateAmountTiles} />
    </div>
  )
}

AmountTiles.propTypes = {
  showInput: PropTypes.bool.isRequired,
  setShowInput: PropTypes.func.isRequired,
}

export default memo(AmountTiles)
