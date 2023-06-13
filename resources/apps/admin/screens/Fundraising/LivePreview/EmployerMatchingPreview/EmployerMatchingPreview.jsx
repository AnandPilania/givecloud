import PropTypes from 'prop-types'
import { faSparkles, faBuilding } from '@fortawesome/pro-regular-svg-icons'
import { Box, Icon, Input, Skeleton, Text } from '../components'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './EmployerMatchingPreview.scss'

const EmployerMatchingPreview = ({ isDisabled }) => {
  const { brandingValue } = useFundraisingFormState()

  const css = isDisabled ? 'opacity-30' : 'opacity-100'

  const renderLogo = () =>
    brandingValue.brandingLogo.full ? (
      <img src={brandingValue.brandingLogo.full} alt='' className={styles.logo} />
    ) : null

  return (
    <Box className={css}>
      {renderLogo()}
      <Icon icon={faSparkles} className='mt-24' />
      <div className={styles.headingContainer}>
        <Text className='mt-4' isLarge>
          Double My Impact
        </Text>
      </div>
      <Skeleton className='my-6' />
      <Input icon={faBuilding}>Find my Employer</Input>
    </Box>
  )
}

EmployerMatchingPreview.propTypes = {
  isDisabled: PropTypes.bool,
}

EmployerMatchingPreview.defaultProps = {
  isDisabled: false,
}

export { EmployerMatchingPreview }
