import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { Carousel, CarouselItem, CarouselItems, SlideTransition } from '@/aerosol'
import { PersonalNameScreen } from './PersonalNameScreen'
import { PersonalGoalScreen } from './PersonalGoalScreen'
import { PersonalAvatarScreen } from './PersonalAvatarScreen'
import { PersonalSummaryScreen } from './PersonalSummaryScreen'
import { useParams } from '@/shared/hooks'
import styles from './Personal.styles.scss'

enum SCREEN_MAP {
  name,
  goal,
  avatar,
  summary,
}

const Personal: FC = () => {
  const { params } = useParams()
  const activeIndex = SCREEN_MAP[params.get(SCREENS.SCREEN)!]

  return (
    <SlideTransition isOpenOnMounted className='h-full'>
      <Carousel initialIndex={activeIndex} name={SCREENS.PERSONAL}>
        <CarouselItems>
          <CarouselItem isPaddingless className={styles.item}>
            <PersonalNameScreen index={SCREEN_MAP.name} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <PersonalGoalScreen index={SCREEN_MAP.goal} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <PersonalAvatarScreen index={SCREEN_MAP.avatar} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <PersonalSummaryScreen index={SCREEN_MAP.summary} />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
    </SlideTransition>
  )
}

export { Personal }
