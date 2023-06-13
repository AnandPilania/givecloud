import type { FC, MouseEvent } from 'react'
import type { NavigationType } from '@/screens/Fundraising/VisualEditor/types'
import { SCREEN } from '@/screens/Fundraising/VisualEditor/types'
import { useHistory, useLocation } from 'react-router-dom'
import { useState } from 'react'
import { faArrowLeft, faArrowRight, faTimes } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import {
  Column,
  Columns,
  Carousel,
  CarouselNextButton,
  CarouselPreviousButton,
  CarouselItem,
  CarouselItems,
  Dropdown,
  DropdownContent,
  DropdownButton,
  DropdownItem,
  DropdownItems,
  DropdownLabel,
  Text,
  Button,
} from '@/aerosol'
import { DonationCard } from '@/screens/Fundraising/VisualEditor/DonationCard'
import { EmailCard } from '@/screens/Fundraising/VisualEditor/EmailCard'
import { EmailOptinCard } from '@/screens/Fundraising/VisualEditor/EmailOptinCard'
import { EmployerMatchingCard } from '@/screens/Fundraising/VisualEditor/EmployerMatchingCard'
import { LayoutCard } from '@/screens/Fundraising/VisualEditor/LayoutCard'
import { RemindersCard } from '@/screens/Fundraising/VisualEditor/RemindersCard'
import { SelectedScreenHeader } from './SelectedScreenHeader'
import { SharingCard } from '@/screens/Fundraising/VisualEditor/SharingCard'
import { TemplateBrandingCard } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard'
import { ThankYouCard } from '@/screens/Fundraising/VisualEditor/ThankYouCard'
import { UpsellCard } from '@/screens/Fundraising/VisualEditor/UpsellCard'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './MobileVisualEditor.styles.scss'

const mapScreens = {
  '0': {
    title: 'Donation',
    component: <DonationCard />,
  },
  '1': {
    title: 'Reminders',
    component: <RemindersCard />,
  },
  '2': {
    title: 'Upsell',
    component: <UpsellCard />,
  },
  '3': {
    title: 'Employer Matching',
    component: <EmployerMatchingCard />,
  },
  '4': {
    title: 'Email Opt-in',
    component: <EmailOptinCard />,
  },
  '5': {
    title: 'Thank You',
    component: <ThankYouCard />,
  },
} as const

interface Props {
  onClose: () => void
  isLoading: boolean
}

type ScreenMapKeys = keyof typeof mapScreens
type ScreenMap = typeof mapScreens
type ScreenKey = keyof ScreenMap[ScreenMapKeys]

const MobileVisualEditor: FC<Props> = ({ onClose, isLoading }) => {
  const history = useHistory()
  const { pathname, search } = useLocation()
  const params = new URLSearchParams(search)
  const { isUpsellError, isEmailOptinError, isEmailThankYouError, isDonationFieldsError, isReminderError, getError } =
    useFundraisingFormState()

  const getActiveIndex = (navigation: NavigationType) => Number(params.get(navigation))
  const [selectedScreen, setSelectedScreen] = useState(() => getActiveIndex('screen'))
  const renderSelectedScreen = (key: ScreenKey) => mapScreens?.[selectedScreen]?.[key]

  const errors = Object.values(getError()).filter((value) => !!value && typeof value === 'string')

  const handleNextButtonClick = () => {
    const nextIndex = getActiveIndex('tab') < 3 ? getActiveIndex('tab') + 1 : 0
    params.set('tab', nextIndex.toString())
    history.replace({ pathname, search: params.toString() })
  }

  const handlePreviousButtonClick = () => {
    const prevIndex = getActiveIndex('tab') === 0 ? 3 : getActiveIndex('tab') - 1
    params.set('tab', prevIndex.toString())
    history.replace({ pathname, search: params.toString() })
  }

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { value } = e.target as HTMLButtonElement
    params.set('screen', value)
    history.replace({ pathname, search: params.toString() })
    setSelectedScreen(Number(value))
  }

  return (
    <>
      <Columns isMarginless>
        <Column columnWidth='six' className={styles.end}>
          <Button type='submit' isOutlined isLoading={isLoading}>
            Save
          </Button>
          <Button aria-label='Close visual editor' className='block h-11 ml-4' onClick={onClose}>
            <FontAwesomeIcon icon={faTimes} />
          </Button>
        </Column>
      </Columns>
      <Carousel initialIndex={getActiveIndex('tab')} isLooping name='fundraising screens'>
        <Columns isResponsive={false} isStackingOnMobile={false} isMarginless>
          <Column className='items-center justify-center'>
            <CarouselPreviousButton onClick={handlePreviousButtonClick} isClean isFullyRounded className='w-min'>
              <span className='sr-only'>previous</span>
              <FontAwesomeIcon icon={faArrowLeft} />
            </CarouselPreviousButton>
          </Column>
          <Column isPaddingless className={styles.center}>
            <SelectedScreenHeader />
          </Column>
          <Column className={styles.center}>
            <CarouselNextButton onClick={handleNextButtonClick} isClean isFullyRounded className='w-min'>
              <span className='sr-only'>next</span>
              <FontAwesomeIcon icon={faArrowRight} />
            </CarouselNextButton>
          </Column>
        </Columns>
        <CarouselItems>
          <CarouselItem>
            <TemplateBrandingCard />
          </CarouselItem>
          <CarouselItem>
            <LayoutCard />
          </CarouselItem>
          <CarouselItem className='pt-0'>
            <Columns isMarginless>
              <Column columnWidth='six' className={styles.center}>
                <Dropdown isFullWidth aria-label='Select Screen' value={selectedScreen.toString()} errors={errors}>
                  <DropdownLabel>
                    <Text type='h5' isBold className='text-white text-center'>
                      Select Screen
                    </Text>
                  </DropdownLabel>
                  <DropdownContent>
                    <DropdownButton isClean>{renderSelectedScreen('title')}</DropdownButton>
                    <DropdownItems>
                      <DropdownItem isError={isDonationFieldsError} onClick={handleClick} value={SCREEN.DONATION}>
                        Donation
                      </DropdownItem>
                      <DropdownItem isError={isReminderError} onClick={handleClick} value={SCREEN.REMINDER}>
                        Reminders
                      </DropdownItem>
                      <DropdownItem isError={isUpsellError} onClick={handleClick} value={SCREEN.UPSELL}>
                        Upsell
                      </DropdownItem>
                      <DropdownItem onClick={handleClick} value={SCREEN.EMPLOYER}>
                        Employer Matching
                      </DropdownItem>
                      <DropdownItem isError={isEmailOptinError} onClick={handleClick} value={SCREEN.EMAIL_OPT_IN}>
                        Email Opt-In
                      </DropdownItem>
                      <DropdownItem isError={isEmailThankYouError} onClick={handleClick} value={SCREEN.THANK_YOU}>
                        Thank You
                      </DropdownItem>
                    </DropdownItems>
                  </DropdownContent>
                </Dropdown>
              </Column>
            </Columns>
            <Columns>
              <Column columnWidth='six'>{renderSelectedScreen('component')}</Column>
            </Columns>
          </CarouselItem>
          <CarouselItem>
            <SharingCard />
          </CarouselItem>
          <CarouselItem>
            <EmailCard />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
    </>
  )
}

export { MobileVisualEditor }
