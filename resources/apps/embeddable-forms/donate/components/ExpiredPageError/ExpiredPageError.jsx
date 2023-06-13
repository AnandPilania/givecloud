import { memo } from 'react'
import styles from '@/components/ExpiredPageError/ExpiredPageError.scss'

const ExpiredPageError = () => {
  const handleClick = () => {
    window.location.reload()
  }

  return (
    <div className={styles.root}>
      <div>Your page has expired.</div>
      <div>
        <a onClick={handleClick}>Click here to refresh the form</a>
      </div>
    </div>
  )
}

export default memo(ExpiredPageError)
