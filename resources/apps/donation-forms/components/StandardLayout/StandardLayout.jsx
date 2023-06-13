import { motion } from 'framer-motion'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import CloseButton from '@/components/CloseButton/CloseButton'
import FooterLinks from '../FooterNavigation/FooterLinks/FooterLinks'
import LogoFlipper from '@/components/LogoFlipper/LogoFlipper'
import OverflowFadeoutBox from '@/components/OverflowFadeoutBox/OverflowFadeoutBox'
import ExitConfirmModal from '../Screen/components/Header/components/ExitConfirmModal/ExitConfirmModal'
import AnimatedText from './components/AnimatedText'
import useCloseForm from '@/hooks/useCloseForm'
import useFooterLinks from '@/hooks/useFooterLinks'
import getConfig from '@/utilities/config'
import contributionState from '@/atoms/contribution'
import { SlideAnimation } from '@/shared/components/SlideAnimation'
import styles from './StandardLayout.scss'

const StandardLayout = ({ children }) => {
  const config = getConfig()
  const {
    background_url,
    landing_page_headline,
    landing_page_description,
    peer_to_peer: { campaign },
  } = config

  const { isConfirmModalOpen, setIsConfirmModalOpen, closeFundraisingForm } = useCloseForm()
  const { footerLinks } = useFooterLinks()
  const contribution = useRecoilValue(contributionState)

  const handleOnCloseButtonClick = () => {
    if (contribution) {
      closeFundraisingForm()
    } else {
      setIsConfirmModalOpen(true)
    }
  }

  const backgroundImageSrc = () => {
    if (campaign) {
      return campaign.avatar_name === 'custom' && campaign.social_avatar
        ? campaign.social_avatar
        : `https://cdn.givecloud.co/s/assets/avatars/${campaign.avatar_name}.svg`
    }

    return background_url
  }

  const renderContent = () => (
    <>
      <div className={styles.contentText}>
        {(background_url || campaign) && (
          <motion.img
            initial={{ opacity: 0, y: '-30%' }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1 }}
            src={backgroundImageSrc()}
            alt={campaign ? 'your social media avatar' : 'headline thumbnail'}
            className={styles.thumbnail}
          />
        )}
        <OverflowFadeoutBox className={styles.heading}>
          <motion.div
            initial='hidden'
            animate='visible'
            variants={{
              visible: {
                transition: {
                  staggerChildren: 0.01,
                },
              },
            }}
          >
            <AnimatedText as='h1' text={campaign?.title ?? landing_page_headline} className={styles.headline} />
          </motion.div>
          <motion.p
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 1, delay: 1 }}
            className={styles.description}
          >
            {landing_page_description}
          </motion.p>
        </OverflowFadeoutBox>
      </div>

      <SlideAnimation slideInFrom='right' className={styles.contentForm}>
        {children}
      </SlideAnimation>
    </>
  )

  return (
    <>
      <div className={styles.root} role='dialog'>
        <motion.div
          initial={{ opacity: 0.25 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 1 }}
          className={styles.diagonalBackground}
        ></motion.div>

        <header className={styles.header}>
          <LogoFlipper className={styles.logo} />
          <CloseButton onClick={handleOnCloseButtonClick} />
        </header>

        <main className={styles.main}>{renderContent()}</main>

        <FooterLinks links={footerLinks} gap='loose' className={styles.footer} />
      </div>

      <ExitConfirmModal
        isOpen={isConfirmModalOpen}
        dismiss={() => setIsConfirmModalOpen(false)}
        close={closeFundraisingForm}
      />
    </>
  )
}

StandardLayout.propTypes = {
  children: PropTypes.node.isRequired,
}

export default StandardLayout
