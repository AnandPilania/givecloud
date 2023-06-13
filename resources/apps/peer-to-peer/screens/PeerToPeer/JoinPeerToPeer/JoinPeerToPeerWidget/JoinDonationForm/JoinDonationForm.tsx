import type { FC } from 'react'
import { useMemo } from 'react'
import { ImpactPromise } from '@/components/ImpactPromise'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHeart } from '@fortawesome/free-solid-svg-icons'
import { SCREENS } from '@/constants/screens'
import { Button, Thermometer } from '@/aerosol'
import {
  AmountSelector,
  Badge,
  Confetti,
  HeroAvatar,
  Text,
  WidgetContent,
  WidgetFooter,
  WidgetHeader,
} from '@/components'
import { formatMoney } from '@/shared/utilities'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { avatarMap } from '@/screens/PeerToPeer/svgs'
import styles from './JoinDonationForm.styles.scss'

interface Props {
  index: number
}

const percentageMap = {
  50: 40,
  150: 15,
  250: 10,
  500: 5,
  1000: 2,
  2500: 1,
  5000: 0.1,
}

const getPercentage = (amount: number) => {
  return Object.keys(percentageMap).reduce((previousValue, val) => {
    return amount >= Number(val) ? percentageMap[val] : previousValue
  }, 0)
}

const JoinDonationForm: FC<Props> = ({ index }) => {
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      logo_url,
      local_currency: { code: currency },
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const percentage = useMemo(
    () => getPercentage(peerToPeerValue.team.teamMemberGoalAmount),
    [peerToPeerValue.team.teamMemberGoalAmount]
  )

  const handleOnChange = (teamMemberGoalAmount: number) =>
    setPeerToPeerState({
      ...peerToPeerValue,
      team: {
        ...team,
        teamMemberGoalAmount,
      },
    })

  const renderBadge = () => (percentage ? <Badge percentage={percentage} /> : <div className={styles.spacingDiv} />)

  const renderAvatar = () =>
    peerToPeerValue.avatarName === 'custom' ? (
      <HeroAvatar
        isMarginless
        src={peerToPeerValue.socialAvatar}
        initAnimationOn={index === activeIndex}
        size='small'
      />
    ) : (
      <HeroAvatar isMarginless size='small' initAnimationOn={index === activeIndex}>
        {avatarMap[peerToPeerValue.avatarName]}
      </HeroAvatar>
    )

  const renderConfetti = () => (index === activeIndex ? <Confetti /> : null)

  return (
    <>
      {renderConfetti()}
      <WidgetHeader indexToNavigate={SCREENS.SUMMARY} onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <div className={styles.challengeName}>
          {renderAvatar()}
          <Text isMarginless isBold>
            Sarah's Challenge
          </Text>
        </div>
        <div className={styles.thermometerContainer}>
          <Text isBold isMarginless>
            {formatMoney({ amount: 0, showZero: true, digits: 0, currency })}
          </Text>
          <Thermometer
            theme='custom'
            initialPercentage={0}
            additionalPercentage={0}
            className={styles.thermometer}
            aria-label='challenge thermometer'
          />
          <Text isBold isMarginless>
            {formatMoney({ amount: team.teamMemberGoalAmount, notation: 'compact', currency })}
          </Text>
        </div>
        <div className={styles.amountContainer}>
          <div className={styles.badgeContainer}>{renderBadge()}</div>
          <AmountSelector value={5} onChange={handleOnChange} currency={'USD'} className={styles.amountSelector} />
        </div>
      </WidgetContent>
      <WidgetFooter>
        <ImpactPromise>
          <Text type='footnote' isSecondaryColour isBold>
            A+ Impact Promise <FontAwesomeIcon icon={faHeart} className='ml-0.5' />
          </Text>
          <Text type='footnote' isSecondaryColour isBold>
            100% of your donation ensures families in our community have a nutricious full pantry.
          </Text>
        </ImpactPromise>
        <Button theme='custom' isFullWidth onClick={() => console.log('redirect to live p2p donation exp')}>
          Donate
        </Button>
      </WidgetFooter>
    </>
  )
}

export { JoinDonationForm }
