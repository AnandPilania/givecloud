import type { FC, SyntheticEvent } from 'react'
import { SCREENS } from '@/constants/screens'
import { CREATE_PATH } from '@/constants/paths'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faTag } from '@fortawesome/pro-regular-svg-icons'
import { CarouselButton, Input } from '@/aerosol'
import { HeroAvatar, WidgetContent, WidgetFooter, WidgetHeader, Text } from '@/components'
import { PeerToPeerFooter } from '@/screens/PeerToPeer/PeerToPeerFooter'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useParams } from '@/shared/hooks'
import styles from './TeamNameScreen.styles.scss'

interface Props {
  index: number
}

const TeamNameScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, team, setPeerToPeerState } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const { setAndReplaceParams } = useParams()

  const handleOnChange = ({ target }: SyntheticEvent) => {
    const { name, value } = target as HTMLInputElement
    setPeerToPeerState({
      ...peerToPeerValue,
      team: {
        ...team,
        [name]: value,
      },
    })
  }

  const handleClick = () => setAndReplaceParams(SCREENS.SCREEN, SCREENS.GOAL)

  return (
    <>
      <WidgetHeader to={CREATE_PATH} onCloseHref={org_website}>
        <img src={logo_url} className={styles.logo} alt='' />
      </WidgetHeader>
      <WidgetContent className={styles.root}>
        <HeroAvatar icon={faTag} initAnimationOn={index === activeIndex} />
        <Text className={styles.text} isBold type='h2'>
          Give Your Team a Name
        </Text>
        <div className={styles.content}>
          <Input label='Team Name' name='name' value={team.name} onChange={handleOnChange} />
        </div>
      </WidgetContent>
      <WidgetFooter>
        <CarouselButton className='w-full' isDisabled={!team.name} onClick={handleClick} theme='custom'>
          Continue
          <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
        </CarouselButton>
        <PeerToPeerFooter isOnWidget />
      </WidgetFooter>
    </>
  )
}

export { TeamNameScreen }
