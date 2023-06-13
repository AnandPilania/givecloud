import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/free-solid-svg-icons'
import { Button, Text } from '@/aerosol'
import { Box } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './TeammatesPanel.scss'

const TeammatesPanel = () => {
  const { large } = useTailwindBreakpoints()
  return (
    <Box isMarginless isFullHeight className={styles.root}>
      <div>
        <Text type='h4' isBold>
          Teammates
        </Text>
        <Text isSecondaryColour>Add members from your organization to Givecloud to contribute to your success.</Text>
      </div>
      <div className={styles.buttonContainer}>
        <Button aria-label='manage teammates' isFullWidth={large.lessThan} href='/jpanel/users' size='small' isOutlined>
          Manage <FontAwesomeIcon icon={faArrowRight} className='ml-2' />
        </Button>
      </div>
    </Box>
  )
}
export { TeammatesPanel }
