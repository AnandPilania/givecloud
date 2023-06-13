import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import styles from '@/components/CreditCard/CreditCardLogo.scss'

const CreditCardLogo = () => {
  const { payment, creditCards } = useContext(StoreContext)
  const logo = payment.card.details.type ? creditCards.logos[payment.card.details.type] : ''

  if (!logo) {
    return null
  }

  return <img className={styles.root} src={logo} />
}

export default memo(CreditCardLogo)
