import PropTypes from 'prop-types'
import { faEnvelope } from '@fortawesome/free-solid-svg-icons'
import { Box, Button, Icon, Input, Link, Skeleton, Text } from '../components'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './EmailOptinPreview.scss'

const EmailOptinPreview = ({ isHovered, isMessageInputFocused, messageOnClick, isDisabled }) => {
  const { brandingValue } = useFundraisingFormState()
  const css = isDisabled ? 'opacity-30' : 'opacity-100'
  const isMessageOverlayVisible = (isHovered || isMessageInputFocused) && !isDisabled

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
    ) : null

  return (
    <Box className={css}>
      {renderLogo()}
      <Icon icon={faEnvelope} className='mt-20' />
      <Text className='mt-4' isLarge>
        Can we send you updates?
      </Text>
      <Skeleton isOverlayVisible={isMessageOverlayVisible} onClick={messageOnClick} className='my-6' />
      <Input icon={faEnvelope}>Your Email</Input>
      <Button className='mt-3'>Subscribe</Button>
      <Link className='mt-12'>No Thank You</Link>
    </Box>
  )
}

EmailOptinPreview.propTypes = {
  isHovered: PropTypes.bool,
  isMessageInputFocused: PropTypes.bool,
  messageOnClick: PropTypes.func,
  isConfirmationFocused: PropTypes.bool,
  confirmationOnClick: PropTypes.func,
  isDisabled: PropTypes.bool,
}

export { EmailOptinPreview }
