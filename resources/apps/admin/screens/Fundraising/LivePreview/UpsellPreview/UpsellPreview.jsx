import PropTypes from 'prop-types'
import classNames from 'classnames'
import { faArrowUp } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Icon, Link, Skeleton, Text } from '../components'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './UpsellPreview.scss'

const UpsellPreview = ({ isHovered, isMessageInputFocused, messageOnClick, isDisabled }) => {
  const { brandingValue } = useFundraisingFormState()
  const isOverlayVisible = (isHovered || isMessageInputFocused) && !isDisabled

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
    ) : null

  return (
    <Box className={classNames(styles.root, isDisabled ? 'opacity-30' : 'opacity-100')}>
      {renderLogo()}
      <Icon icon={faArrowUp} className='mt-20' />
      <Text className='mt-4' isLarge>
        Upgrade Your Impact
      </Text>
      <Skeleton isOverlayVisible={isOverlayVisible} onClick={messageOnClick} className='my-6' />
      <Button>$99/mon</Button>
      <div className={styles.buttonContainer}>
        <Button className='mr-4'>$9/mon</Button>
        <Button>$9/mon</Button>
      </div>
      <Link className='mt-12'>No Thank You</Link>
    </Box>
  )
}

UpsellPreview.propTypes = {
  isHovered: PropTypes.bool,
  isMessageInputFocused: PropTypes.bool,
  messageOnClick: PropTypes.func,
  isDisabled: PropTypes.bool,
}

export { UpsellPreview }
