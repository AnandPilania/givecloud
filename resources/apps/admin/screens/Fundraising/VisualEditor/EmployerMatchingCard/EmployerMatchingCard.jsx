import { useRecoilValue } from 'recoil'
import { Column, Columns, Toggle, Badge, Text, Box } from '@/aerosol'
import { Link } from '@/components/Link'
import { EmployerMatchingPreview } from '@/screens/Fundraising/LivePreview/EmployerMatchingPreview'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import config from '@/atoms/config'
import styles from './EmployerMatchingCard.scss'

const EmployerMatchingCard = () => {
  const { medium } = useTailwindBreakpoints()
  const { doubleTheDonationValue, setDoubleTheDonationState } = useFundraisingFormState()
  const { clientUrl } = useRecoilValue(config)

  const handleToggle = () => {
    setDoubleTheDonationState({
      ...doubleTheDonationValue,
      doubleTheDonationEnabled: !doubleTheDonationValue.doubleTheDonationEnabled,
    })
  }

  const isPreviewEnabled =
    !doubleTheDonationValue.doubleTheDonationConnected || !doubleTheDonationValue.doubleTheDonationEnabled

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column columnWidth='four' className={styles.background}>
        <EmployerMatchingPreview isDisabled={isPreviewEnabled} />
        <Badge theme='secondary' className={styles.badge}>
          Sample
        </Badge>
      </Column>
    )
  }

  const renderLink = () =>
    doubleTheDonationValue.doubleTheDonationConnected ? null : (
      <Text>
        <Link href={`${clientUrl}/jpanel/settings/double-the-donation`} className={styles.link}>
          Enable the Double the Donation 360 Match integration.
        </Link>{' '}
        Be sure to save your changes before you go.
      </Text>
    )

  const isToggleEnabled =
    doubleTheDonationValue.doubleTheDonationConnected && doubleTheDonationValue.doubleTheDonationEnabled

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns isMarginless className='h-full'>
        {renderPreviewImage()}
        <Column columnWidth='three'>
          <div className={styles.header}>
            <Text isMarginless isBold type='h5'>
              Employer Matching
            </Text>
            <Toggle
              isEnabled={isToggleEnabled}
              isDisabled={!doubleTheDonationValue.doubleTheDonationConnected}
              setIsEnabled={handleToggle}
              name='employer matching'
            />
          </div>
          <Text isSecondaryColour isMarginless className='mb-6'>
            Ask your donors if they want to have their employer match their impact.
          </Text>
          {renderLink()}
        </Column>
      </Columns>
    </Box>
  )
}

export { EmployerMatchingCard }
