import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import ErrorBox from '@/components/ErrorBox/ErrorBox'
import styles from '@/components/ReturnError/ReturnError.scss'

const ReturnError = () => {
  const { returnError } = useContext(StoreContext)

  if (!returnError.show) {
    return null
  }

  return (
    <div className={styles.root}>
      <ErrorBox>{returnError.message}</ErrorBox>
    </div>
  )
}

export default memo(ReturnError)
