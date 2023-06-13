import PropTypes from 'prop-types'
import Emoji from 'react-emoji-render'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEnvelope } from '@fortawesome/pro-regular-svg-icons'
import { Box, Text } from '../components'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './EmailPreview.scss'

const EmailPreview = ({ isOverlayVisible, messageOnClick }) => {
  const { brandingValue } = useFundraisingFormState()

  const renderOverlay = () => (isOverlayVisible ? <div onClick={messageOnClick} className={styles.overlay} /> : null)

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <div className={styles.logoWrapper}>
        <div className={styles.logoContainer}>
          <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
        </div>
      </div>
    ) : null

  return (
    <div className={styles.root}>
      <Box className={styles.box}>
        <FontAwesomeIcon icon={faEnvelope} size='lg' />
        <Text className={styles.heading}>
          Thank you for your donation
          <Emoji text=':heart:' />
        </Text>
        <div className={styles.logoBox}>{renderLogo()}</div>
        <div className={styles.messageBox}>
          <div className='mb-6'>
            <div className={classnames(styles.skeleton, styles.xsmall)} />
            <div className={classnames(styles.skeleton, styles.large, 'my-1')} />
            <div className={classnames(styles.skeleton, styles.small)} />
          </div>
          <div className='relative'>
            <div className={classnames(styles.skeleton, styles.large)} />
            <div className={classnames(styles.skeleton, styles.medium, 'mt-1')} />
            <div className={classnames(styles.skeleton, styles.large, 'my-1')} />
            <div className={classnames(styles.skeleton, styles.small)} />
            {renderOverlay()}
          </div>
        </div>
        <div className={styles.footer}>
          <div className={classnames(styles.skeleton, styles.small, 'mb-1')} />
        </div>
      </Box>
    </div>
  )
}

EmailPreview.propTypes = {
  isOverlayVisible: PropTypes.bool,
  messageOnClick: PropTypes.func,
}

EmailPreview.defaultProps = {
  isOverlayVisible: false,
}

export { EmailPreview }
