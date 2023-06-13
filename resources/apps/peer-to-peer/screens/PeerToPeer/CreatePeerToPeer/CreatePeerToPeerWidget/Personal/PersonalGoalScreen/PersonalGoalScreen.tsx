import type { FC } from 'react'
import { useMemo } from 'react'
import { SCREENS } from '@/constants/screens'
import { CREATE_PATH } from '@/constants/paths'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faSparkles } from '@fortawesome/pro-regular-svg-icons'
import { faBolt, faFire, faHeart, faStar } from '@fortawesome/free-solid-svg-icons'
import { CarouselButton } from '@/aerosol'
import {
  AmountSelector,
  Badge,
  CurrencySelector,
  HeroAvatar,
  WidgetContent,
  WidgetHeader,
  WidgetFooter,
  Text,
} from '@/components'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import { useParams } from '@/shared/hooks'
import styles from './PersonalGoalScreen.styles.scss'

const badgeIcons = {
  50: faHeart,
  40: faHeart,
  25: faStar,
  10: faStar,
  5: faBolt,
  1: faFire,
}

const percentageMap = {
  500: 50,
  1000: 40,
  1500: 25,
  2500: 10,
  5000: 5,
  10000: 1,
}

const getPercentage = (amount: number) => {
  return Object.keys(percentageMap).reduce((previousValue, val) => {
    return amount >= Number(val) ? percentageMap[val] : previousValue
  }, 0)
}

interface Props {
  index: number
}

const PersonalGoalScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, personal, setPeerToPeerState } = usePeerToPeerState()
  const { supporter } = useSupporterState()
  const { fundraisingExperience } = useFundraisingExperienceState()
  const {
    logo_url,
    global_settings: { org_website },
  } = fundraisingExperience
  const { activeIndex } = useCarouselContext()
  const { setAndReplaceParams } = useParams()

  const percentage = useMemo(() => getPercentage(personal.goalAmount), [personal.goalAmount])

  const handleClick = () => setAndReplaceParams(SCREENS.SCREEN, SCREENS.AVATAR)

  const handleCurrency = (currencyCode: string) => {
    setPeerToPeerState({
      ...peerToPeerValue,
      currencyCode,
    })
  }

  const renderBadge = () =>
    percentage ? <Badge icons={badgeIcons} percentage={percentage} /> : <div className={styles.spacingDiv} />

  const handleOnChange = (goalAmount: number) =>
    setPeerToPeerState({
      ...peerToPeerValue,
      personal: {
        ...personal,
        goalAmount,
      },
    })

  const hasSupporterFirstName = !!supporter.first_name.length
  const indexToNavigate = hasSupporterFirstName ? undefined : SCREENS.NAME
  const navigateBackTo = hasSupporterFirstName ? CREATE_PATH : undefined

  return (
    <>
      <WidgetHeader indexToNavigate={indexToNavigate} to={navigateBackTo} onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar icon={faSparkles} initAnimationOn={index === activeIndex} />
        <Text className={styles.text} isBold type='h2'>
          Choose Your Personal Goal
        </Text>
        <div className={styles.badgeContainer}>{renderBadge()}</div>
        <AmountSelector
          value={personal.goalAmount}
          onChange={handleOnChange}
          currency={peerToPeerValue.currencyCode}
          className={styles.amountSelector}
          presetAmounts={[500, 1000, 1500, 2500, 5000, 10000]}
        />
        <CurrencySelector currencyCode={peerToPeerValue.currencyCode} onChange={handleCurrency} />
      </WidgetContent>
      <WidgetFooter>
        <CarouselButton className='w-full' onClick={handleClick} theme='custom'>
          Continue
          <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
        </CarouselButton>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    </>
  )
}

export { PersonalGoalScreen }
