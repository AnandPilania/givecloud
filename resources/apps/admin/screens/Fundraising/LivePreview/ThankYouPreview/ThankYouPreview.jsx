import { faCheck } from '@fortawesome/free-solid-svg-icons'
import { Box, Icon, Skeleton, Text } from '../components'
import PropTypes from 'prop-types'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './ThankYouPreview.scss'

const ThankYouPreview = ({ isHovered, isMessageFocused, messageOnClick }) => {
  const { brandingValue } = useFundraisingFormState()

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
    ) : null

  return (
    <Box>
      {renderLogo()}
      <Icon icon={faCheck} className='mt-24' />
      <div className={styles.headingContainer}>
        <Text className='mt-4' isLarge>
          Thank You &#123;Name&#125;
        </Text>
      </div>
      <Skeleton isOverlayVisible={isHovered || isMessageFocused} onClick={messageOnClick} className='my-6' />
      <Text>Share</Text>
      <div className={styles.iconSkeletonContainer}>
        <span className={styles.iconSkeleton} />
        <span className={styles.iconSkeleton} />
        <span className={styles.iconSkeleton} />
        <span className={styles.iconSkeleton} />
      </div>
    </Box>
  )
}

ThankYouPreview.propTypes = {
  isHovered: PropTypes.bool,
  isMessageFocused: PropTypes.bool,
  messageOnClick: PropTypes.func,
}

export { ThankYouPreview }
