import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Badge, Box, Button, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './SupporterTrackingPanel.scss'

const SupporterTrackingPanel = ({ hasUpgraded }) => {
  const { large } = useTailwindBreakpoints()

  return hasUpgraded ? (
    <Box isMarginless isFullHeight className={styles.root}>
      <div>
        <Text type='h4' isSecondaryColour={!hasUpgraded} isBold>
          Supporter Tracking
        </Text>
        <Text isSecondaryColour>
          Edit your supporter preferences to customize the giving experience for your donors.
        </Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button
          aria-label='manage supporter tracking'
          isFullWidth={large.lessThan}
          href='/jpanel/settings/supporters'
          size='small'
          isOutlined
        >
          Manage <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  ) : (
    <Box isFullHeight isMarginless className={styles.root}>
      <div>
        <div className={styles.textContainer}>
          <Text className='mr-2' type='h4' isSecondaryColour={!hasUpgraded} isBold>
            Supporter Tracking
          </Text>
          <Badge className='mb-2' theme='gradient'>
            Upgrade
          </Badge>
        </div>
        <Text isSecondaryColour>Add members from your organization to Givecloud to contribute to your success.</Text>
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

SupporterTrackingPanel.propTypes = {
  hasUpgraded: PropTypes.bool,
}

SupporterTrackingPanel.defaultProps = {
  hasUpgraded: false,
}

export { SupporterTrackingPanel }
