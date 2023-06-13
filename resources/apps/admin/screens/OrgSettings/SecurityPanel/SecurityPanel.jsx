import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Badge, Box, Button, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './SecurityPanel.scss'

const SecurityPanel = ({ hasUpgraded }) => {
  const { large } = useTailwindBreakpoints()

  return hasUpgraded ? (
    <Box isMarginless isFullHeight className={styles.box}>
      <div>
        <Text type='h4' isBold isSecondaryColour={!hasUpgraded}>
          Security
        </Text>
        <Text isSecondaryColour>Configure your security preferences.</Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button
          aria-label='manage security preferences'
          isFullWidth={large.lessThan}
          href='/jpanel/settings/security'
          size='small'
          isOutlined
        >
          Manage <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  ) : (
    <Box isMarginless isFullHeight className={styles.root}>
      <div>
        <div className={styles.textContainer}>
          <Text className={styles.heading} type='h4' isSecondaryColour={!hasUpgraded} isBold>
            Security
          </Text>
          <Badge className='mb-2' theme='gradient'>
            Upgrade
          </Badge>
        </div>
        <Text isSecondaryColour>
          Edit and review your Givecloud security preferences.
          <span className='sr-only'>Upgrade your account to access this feature</span>
        </Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button
          aria-label='upgrade your givecloud subscription'
          isFullWidth={large.lessThan}
          href='https://calendly.com/givecloud-sales/givecloud-upgrade-call'
          size='small'
          isOutlined
        >
          Upgrade <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  )
}

SecurityPanel.propTypes = {
  hasUpgraded: PropTypes.bool,
}

SecurityPanel.defaultProps = {
  hasUpgraded: false,
}

export { SecurityPanel }
