import type { FC } from 'react'
import { Text } from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState/useFundraisingFormState'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import styles from './MobileVisualEditor.styles.scss'

const mapScreens = {
  0: 'Template & Branding',
  1: 'Layout',
  2: 'Customize Experience',
  3: 'Sharing',
  4: 'Email',
  5: 'Donor Perfect',
}

interface Props {
  activeIndex?: number
}

const SelectedScreenHeader: FC<Props> = () => {
  const { activeIndex = 0 } = useCarouselContext()
  const { getError, isDonationFieldsError, isReminderError, isUpsellError, isEmailOptinError, isEmailThankYouError } =
    useFundraisingFormState()
  const { heading } = getError() ?? {}

  const isOnExperienceScreen = mapScreens[activeIndex] === 'Customize Experience'
  const isTabError = mapScreens[activeIndex] === heading
  const isExperienceError =
    isDonationFieldsError || isReminderError || isUpsellError || isEmailOptinError || isEmailThankYouError

  const isError = isTabError || (isOnExperienceScreen && isExperienceError)

  const renderErrorIcon = () =>
    isError ? (
      <span className={styles.errorIcon}>
        <FontAwesomeIcon className='p-1' aria-hidden='true' icon={faExclamationCircle} />
      </span>
    ) : null

  return (
    <div className='relative'>
      <Text type='h5' isMarginless className='text-center text-white'>
        {mapScreens[activeIndex]}
      </Text>
      {renderErrorIcon()}
    </div>
  )
}

export { SelectedScreenHeader }
