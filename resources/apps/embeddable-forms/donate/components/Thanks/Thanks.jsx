import { memo, useContext } from 'react'
import classnames from 'classnames'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/components/Thanks/Thanks.scss'

const Thanks = () => {
  const { giveAgain, primaryColor } = useContext(StoreContext)
  const { textColor } = supportedPrimaryColors[primaryColor] || {}

  return (
    <div className={styles.root}>
      <p className={styles.party}>🎉🎉🎉</p>

      <p className={styles.title}>Thank you for your donation.</p>

      <a onClick={giveAgain} className={classnames(styles.donateAgainLink, textColor)}>
        Click here to give again.
      </a>
    </div>
  )
}

export default memo(Thanks)
