import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { Carousel, CarouselItem, CarouselItems, SlideTransition } from '@/aerosol'
import { useParams } from '@/shared/hooks'
import { TeamAvatarScreen } from './TeamAvatarScreen'
import { TeamGoalScreen } from './TeamGoalScreen'
import { TeamNameScreen } from './TeamNameScreen'
import { TeamSummaryScreen } from './TeamSummaryScreen'
import styles from './Team.styles.scss'

enum SCREEN_MAP {
  name,
  goal,
  avatar,
  summary,
}

const Team: FC = () => {
  const { params } = useParams()
  const activeIndex = SCREEN_MAP[params.get(SCREENS.SCREEN)!]

  return (
    <SlideTransition isOpenOnMounted className='h-full'>
      <Carousel initialIndex={activeIndex} name={SCREENS.TEAM}>
        <CarouselItems>
          <CarouselItem isPaddingless className={styles.item}>
            <TeamNameScreen index={SCREEN_MAP.name} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <TeamGoalScreen index={SCREEN_MAP.goal} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <TeamAvatarScreen index={SCREEN_MAP.avatar} />
          </CarouselItem>
          <CarouselItem isPaddingless className={styles.item}>
            <TeamSummaryScreen index={SCREEN_MAP.summary} />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
    </SlideTransition>
  )
}

export { Team }
