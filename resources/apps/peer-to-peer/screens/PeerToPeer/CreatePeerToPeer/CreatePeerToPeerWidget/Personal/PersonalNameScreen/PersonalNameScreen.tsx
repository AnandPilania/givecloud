import type { ChangeEvent, FC } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faTag } from '@fortawesome/pro-regular-svg-icons'
import { SCREENS } from '@/constants/screens'
import { CREATE_PATH } from '@/constants/paths'
import { CarouselButton, Input } from '@/aerosol'
import { HeroAvatar, WidgetContent, WidgetHeader, WidgetFooter, Text } from '@/components'
import { useCarouselContext } from '@/aerosol/Carousel/CarouselContext'
import { usePeerToPeerState } from '@/screens/PeerToPeer/usePeerToPeerState'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useParams } from '@/shared/hooks'
import styles from './PersonalNameScreen.styles.scss'

interface Props {
  index: number
}

const PersonalNameScreen: FC<Props> = ({ index }) => {
  const { peerToPeerValue, personal, setPeerToPeerState } = usePeerToPeerState()
  const {
    fundraisingExperience: {
      logo_url,
      global_settings: { org_website },
    },
  } = useFundraisingExperienceState()
  const { activeIndex } = useCarouselContext()
  const { setAndReplaceParams } = useParams()

  const handleOnChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = target as HTMLInputElement
    setPeerToPeerState({
      ...peerToPeerValue,
      personal: {
        ...personal,
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
          Give Your Challenge a Name
        </Text>
        <div className={styles.content}>
          <Input label='Challenge Name' name='firstName' value={personal.firstName} onChange={handleOnChange} />
        </div>
      </WidgetContent>
      <WidgetFooter>
        <CarouselButton className='w-full' isDisabled={!personal.firstName} onClick={handleClick} theme='custom'>
          Continue
          <FontAwesomeIcon className='ml-2' icon={faArrowRight} />
        </CarouselButton>
      </WidgetFooter>
    </>
  )
}

export { PersonalNameScreen }
