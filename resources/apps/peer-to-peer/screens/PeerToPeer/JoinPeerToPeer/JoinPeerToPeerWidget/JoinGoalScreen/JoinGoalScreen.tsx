import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { useMemo } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faSparkles } from '@fortawesome/pro-regular-svg-icons'
import { faBolt, faFire, faHeart, faStar } from '@fortawesome/free-solid-svg-icons'
import { CarouselButton, Thermometer } from '@/aerosol'
import { AmountSelector, Badge, HeroAvatar, WidgetContent, WidgetHeader, WidgetFooter, Text } from '@/components'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { formatMoney } from '@/shared/utilities'
import { useParams } from '@/shared/hooks'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import styles from './JoinGoalScreen.styles.scss'

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

const JoinGoalScreen: FC = () => {
  const { setAndReplaceParams } = useParams()
  const { peerToPeerValue, team, personal, setPeerToPeerState } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()

  const percentage = useMemo(
    () => getPercentage(peerToPeerValue.team.teamMemberGoalAmount),
    [peerToPeerValue.team.teamMemberGoalAmount]
  )

  const additionalPercentage = useMemo(
    () => (team.teamMemberGoalAmount / team.goalAmount) * 100,
    [team.teamMemberGoalAmount]
  )

  const handleOnChange = (teamMemberGoalAmount: number) =>
    setPeerToPeerState({
      ...peerToPeerValue,
      team: {
        ...team,
        teamMemberGoalAmount,
      },
    })

  const handleClick = () => setAndReplaceParams(SCREENS.SCREEN, SCREENS.AVATAR)

  const renderBadge = () =>
    percentage ? <Badge icons={badgeIcons} percentage={percentage} /> : <div className={styles.spacingDiv} />

  return (
    <>
      <WidgetHeader onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar icon={faSparkles} />
        <Text className={styles.text} isBold type='h2'>
          Choose Your Goal
        </Text>
        <div className={styles.badgeContainer}>{renderBadge()}</div>
        <AmountSelector
          value={team.teamMemberGoalAmount}
          onChange={handleOnChange}
          currency={peerToPeerValue.currencyCode}
          className={styles.amountSelector}
          presetAmounts={[500, 1000, 1500, 2500, 5000, 10000]}
          maxValue={team.goalAmount}
        />
        <div className={styles.thermometerContainer}>
          <Text isBold isMarginless className={styles.text}>
            {formatMoney({
              amount: team.amountRaised ?? 0,
              showZero: true,
              notation: 'compact',
              currency: peerToPeerValue.currencyCode,
            })}
          </Text>
          <Thermometer
            initialPercentage={team.amountRaised ?? 0}
            additionalPercentage={additionalPercentage}
            className={styles.thermometer}
            aria-label={`fundraising goal thermometer`}
            theme='custom'
          />
          <Text isMarginless className={styles.text}>
            {formatMoney({ amount: team.goalAmount, notation: 'compact', currency: peerToPeerValue.currencyCode })}
          </Text>
        </div>
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

export { JoinGoalScreen }
