import { useHistory } from 'react-router'
import { motion } from 'framer-motion'
import { CHOOSE_PAYMENT_METHOD } from '@/constants/pathConstants'
import Banner from './Banner/Banner'
import Button from '@/components/Button/Button'
import ExitConfirmModal from '@/components/Screen/components/Header/components/ExitConfirmModal/ExitConfirmModal'
import LogoFlipper from '@/components/LogoFlipper/LogoFlipper'
import OverflowFadeoutBox from '@/components/OverflowFadeoutBox/OverflowFadeoutBox'
import Screen from '@/components/Screen/Screen'
import useSlideVariants from '@/hooks/useSlideVariants'
import useLocalization from '@/hooks/useLocalization'
import useCloseForm from '@/hooks/useCloseForm'
import getConfig from '@/utilities/config'
import styles from './Landing.scss'

const Landing = () => {
  const config = getConfig()
  const history = useHistory()
  const t = useLocalization('screens.landing')
  const { isConfirmModalOpen, setIsConfirmModalOpen, closeFundraisingForm } = useCloseForm()

  const hasPopAction = history.action === 'POP'

  const scrollingVariants = useSlideVariants(hasPopAction)

  return (
    <div className={styles.root}>
      <motion.div
        variants={scrollingVariants}
        initial='hide'
        animate='show'
        exit='exit'
        transition={{ duration: 0.3 }}
        className={styles.banner}
      >
        <Banner image={config.background_url} onClose={() => setIsConfirmModalOpen(true)} />
      </motion.div>
      <Screen showHeader={false} className={styles.screen}>
        <div className={styles.content}>
          <LogoFlipper className={styles.logo} />
          <OverflowFadeoutBox className={styles.heading}>
            <h1 className={styles.headline}>{config.landing_page_headline}</h1>
            <p className={styles.description}>{config.landing_page_description}</p>
          </OverflowFadeoutBox>
          <Button className={styles.donateBtn} onClick={() => history.push(CHOOSE_PAYMENT_METHOD)}>
            {t('donate')}
          </Button>
        </div>
      </Screen>

      <ExitConfirmModal
        isOpen={isConfirmModalOpen}
        dismiss={() => setIsConfirmModalOpen(false)}
        close={closeFundraisingForm}
      />
    </div>
  )
}

export default Landing
