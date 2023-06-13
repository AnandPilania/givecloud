import { memo, useContext } from 'react'
import ScaleLoader from 'react-spinners/ScaleLoader'
import { StoreContext } from '@/root/store'
import { supportedPrimaryColors } from '@/constants/styleConstants'
import styles from '@/components/ProcessingWaitScreen/ProcessingWaitScreen.scss'

const ProcessingWaitScreen = () => {
  const { primaryColor } = useContext(StoreContext)
  const { scaleLoaderColor } = supportedPrimaryColors[primaryColor] || {}

  return (
    <div className={styles.root}>
      <ScaleLoader size={150} color={scaleLoaderColor} loading />

      <p>Processing Payment</p>
    </div>
  )
}

export default memo(ProcessingWaitScreen)
