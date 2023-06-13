import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart } from '@fortawesome/free-solid-svg-icons'
import CalculationBasedPromise from './components/CalculationBasedPromise'
import StatementBasedPromise from './components/StatementBasedPromise'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import styles from './TransparencyPromise.scss'

const TransparencyPromise = () => {
  const t = useLocalization('screens.choose_payment_method.transparency_promise')
  const { transparency_promise: transparencyPromise } = useRecoilValue(configState)

  if (!transparencyPromise.enabled) {
    return null
  }

  const usingCalculationBasedPromise = transparencyPromise.type === 'calculation'

  return (
    <div className={styles.root}>
      <p>
        <strong>
          {t('title')} <FontAwesomeIcon icon={faHeart} />
        </strong>
      </p>

      <div className={styles.content}>
        {usingCalculationBasedPromise ? <CalculationBasedPromise /> : <StatementBasedPromise />}
      </div>
    </div>
  )
}

export default memo(TransparencyPromise)
