import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import styles from '@/components/CreditCard/CreditCardSupportedCards.scss'

const CreditCardSupportedCards = () => {
  const { creditCards } = useContext(StoreContext)

  if (creditCards.supported.length === 0) {
    return null
  }

  return (
    <div className={styles.root}>
      <Label title='We Accept'>
        {creditCards.supported.map((card) => (
          <img key={card} className={styles.cardLogo} src={creditCards.logos[card]} />
        ))}
      </Label>
    </div>
  )
}

export default memo(CreditCardSupportedCards)
