import type { FC, SyntheticEvent } from 'react'
import type { NavigationType } from '@/screens/Fundraising/VisualEditor/types'
import { TAB, SCREEN } from '@/screens/Fundraising/VisualEditor/types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTimes, faArrowLeft, faArrowRight, faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import {
  Button,
  Carousel,
  CarouselButton,
  CarouselItem,
  CarouselItems,
  CarouselNextButton,
  CarouselPreviousButton,
  Tabs,
  TabsNav,
  TabsNavItem,
  TabsPanel,
  TabsPanels,
} from '@/aerosol'
import { DonationCard } from '@/screens/Fundraising/VisualEditor/DonationCard'
import { EmailCard } from '@/screens/Fundraising/VisualEditor/EmailCard'
import { EmailOptinCard } from '@/screens/Fundraising/VisualEditor/EmailOptinCard'
import { EmployerMatchingCard } from '@/screens/Fundraising/VisualEditor/EmployerMatchingCard'
import { LayoutCard } from '@/screens/Fundraising/VisualEditor/LayoutCard'
import { RemindersCard } from '@/screens/Fundraising/VisualEditor/RemindersCard'
import { SharingCard } from '@/screens/Fundraising/VisualEditor/SharingCard'
import { TemplateBrandingCard } from '@/screens/Fundraising/VisualEditor/TemplateBrandingCard'
import { ThankYouCard } from '@/screens/Fundraising/VisualEditor/ThankYouCard'
import { UpsellCard } from '@/screens/Fundraising/VisualEditor/UpsellCard'
import { TemplatesCard } from '@/screens/Fundraising/VisualEditor/TemplatesCard'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './DesktopVisualEditor.styles.scss'
import { useHistory, useLocation } from 'react-router-dom'

interface Props {
  onClose: () => void
  isLoading: boolean
}

const DesktopVisualEditor: FC<Props> = ({ onClose, isLoading }) => {
  const {
    isNameError,
    isColourError,
    isUpsellError,
    isEmailOptinError,
    isLayoutError,
    isEmailThankYouError,
    isDonationFieldsError,
    isCustomAmountValuesError,
    isReminderError,
  } = useFundraisingFormState()
  const { large, medium } = useTailwindBreakpoints()
  const history = useHistory()
  const { pathname, search } = useLocation()
  const params = new URLSearchParams(search)

  const getActiveIndex = (navigation: NavigationType) => Number(params.get(navigation))

  const handleClick = ({ target }: SyntheticEvent) => {
    const { name, value } = target as HTMLButtonElement
    params.set(value, name)
    history.replace({ pathname, search: params.toString() })
  }

  const handleNextButtonClick = () => {
    const nextIndex = getActiveIndex('screen') < 5 ? getActiveIndex('screen') + 1 : 0
    params.set('screen', nextIndex.toString())
    history.replace({ pathname, search: params.toString() })
  }

  const handlePreviousButtonClick = () => {
    const prevIndex = getActiveIndex('screen') === 0 ? 5 : getActiveIndex('screen') - 1
    params.set('screen', prevIndex.toString())
    history.replace({ pathname, search: params.toString() })
  }

  const isCustomizeExperienceError =
    isDonationFieldsError || isUpsellError || isEmailOptinError || isCustomAmountValuesError || isReminderError

  const renderActionButtons = (isWithinViewport: boolean) => {
    if (isWithinViewport) {
      return (
        <div className={styles.buttonContainer}>
          <Button className={styles.button} isOutlined type='submit' isLoading={isLoading}>
            Save
          </Button>
          <Button aria-label='Close visual editor' className={styles.button} onClick={onClose}>
            <FontAwesomeIcon icon={faTimes} />
          </Button>
        </div>
      )
    }
    return null
  }

  const renderCarouselButtons = () => {
    if (large.lessThan) return null
    return (
      <>
        <CarouselNextButton onClick={handleNextButtonClick} isClean isFullyRounded className={styles.nextButton}>
          <FontAwesomeIcon icon={faArrowRight} />
        </CarouselNextButton>
        <CarouselPreviousButton
          onClick={handlePreviousButtonClick}
          isClean
          isFullyRounded
          className={styles.previousButton}
        >
          <FontAwesomeIcon icon={faArrowLeft} />
        </CarouselPreviousButton>
      </>
    )
  }

  const renderErrorIcon = (isError?: boolean) =>
    isError ? (
      <span className={styles.errorIcon}>
        <FontAwesomeIcon className='p-1' icon={faExclamationCircle} />
      </span>
    ) : null

  const renderTemplateCard = () => {
    if (medium.lessThan) {
      return <TemplateBrandingCard />
    }
    return (
      <Carousel name='template and branding'>
        <CarouselItems>
          <CarouselItem>
            <TemplateBrandingCard />
          </CarouselItem>
          <CarouselItem>
            <TemplatesCard />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
    )
  }

  return (
    <>
      {renderActionButtons(large.lessThan)}
      <Tabs initialIndex={getActiveIndex('tab')} invertTheme>
        <div className={styles.root}>
          <TabsNav placement='center'>
            <TabsNavItem value='tab' name={TAB.TEMPLATE} onClick={handleClick}>
              Template & Branding
              {renderErrorIcon(isNameError || isColourError)}
            </TabsNavItem>
            <TabsNavItem value='tab' name={TAB.LAYOUT} onClick={handleClick}>
              Layout
              {renderErrorIcon(isLayoutError)}
            </TabsNavItem>
            <TabsNavItem value='tab' name={TAB.EXPERIENCE} onClick={handleClick}>
              Customize Experience
              {renderErrorIcon(isCustomizeExperienceError)}
            </TabsNavItem>
            <TabsNavItem value='tab' name={TAB.SHARING} onClick={handleClick}>
              Sharing
            </TabsNavItem>
            <TabsNavItem value='tab' name={TAB.EMAIL} onClick={handleClick}>
              Email
              {renderErrorIcon(isEmailThankYouError)}
            </TabsNavItem>
          </TabsNav>
          {renderActionButtons(!large.lessThan)}
        </div>
        <TabsPanels animationType='scale'>
          <TabsPanel key={1}>{renderTemplateCard()}</TabsPanel>
          <TabsPanel key={2}>
            <LayoutCard />
          </TabsPanel>
          <TabsPanel key={3}>
            <Carousel initialIndex={getActiveIndex('screen')} isLooping name='customize form'>
              <div className={styles.carouselContainer}>
                <CarouselButton value='screen' name='0' className='mr-4' index={0} onClick={handleClick}>
                  Donation
                  {renderErrorIcon(isDonationFieldsError)}
                </CarouselButton>
                <CarouselButton value='screen' name={SCREEN.REMINDER} className='mr-4' index={1} onClick={handleClick}>
                  Reminders
                  {renderErrorIcon(isReminderError)}
                </CarouselButton>
                <CarouselButton value='screen' name={SCREEN.UPSELL} className='mr-4' index={2} onClick={handleClick}>
                  Upsell
                  {renderErrorIcon(isUpsellError)}
                </CarouselButton>
                <CarouselButton value='screen' name={SCREEN.EMPLOYER} className='mr-4' index={3} onClick={handleClick}>
                  Employer Matching
                </CarouselButton>
                <CarouselButton
                  value='screen'
                  name={SCREEN.EMAIL_OPT_IN}
                  className='mr-4'
                  index={4}
                  onClick={handleClick}
                >
                  Email Opt-In
                  {renderErrorIcon(isEmailOptinError)}
                </CarouselButton>
                <CarouselButton value='screen' name={SCREEN.THANK_YOU} className='mr-4' index={5} onClick={handleClick}>
                  Thank You
                </CarouselButton>
              </div>
              <CarouselItems>
                <CarouselItem>
                  <DonationCard />
                </CarouselItem>
                <CarouselItem>
                  <RemindersCard />
                </CarouselItem>
                <CarouselItem>
                  <UpsellCard />
                </CarouselItem>
                <CarouselItem>
                  <EmployerMatchingCard />
                </CarouselItem>
                <CarouselItem>
                  <EmailOptinCard />
                </CarouselItem>
                <CarouselItem>
                  <ThankYouCard />
                </CarouselItem>
              </CarouselItems>
              {renderCarouselButtons()}
            </Carousel>
          </TabsPanel>
          <TabsPanel key={4}>
            <SharingCard />
          </TabsPanel>
          <TabsPanel key={5}>
            <EmailCard />
          </TabsPanel>
        </TabsPanels>
      </Tabs>
    </>
  )
}

export { DesktopVisualEditor }
