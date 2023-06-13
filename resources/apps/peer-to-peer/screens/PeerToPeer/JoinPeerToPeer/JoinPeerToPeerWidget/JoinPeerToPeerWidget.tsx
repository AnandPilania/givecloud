import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { Carousel, CarouselItems, CarouselItem } from '@/aerosol'
import { Widget } from '@/components'
import { FAQDrawer } from '@/screens/PeerToPeer/FAQDrawer'
import { PrivacyDrawer } from '@/screens/PeerToPeer/PrivacyDrawer'
import { JoinAvatarScreen } from './JoinAvatarScreen'
import { JoinGoalScreen } from './JoinGoalScreen'
import { JoinSummaryScreen } from './JoinSummaryScreen'
import { useParams } from '@/shared/hooks'
import styles from './JoinPeerToPeerWidget.styles.scss'

enum SCREEN_MAP {
  goal,
  avatar,
  summary,
  join,
}

const JoinPeerToPeerWidget: FC = () => {
  const { params } = useParams()
  const activeIndex = SCREEN_MAP[params.get(SCREENS.SCREEN)!]

  return (
    <Widget>
      <Carousel initialIndex={activeIndex} name={SCREENS.JOIN}>
        <CarouselItems>
          <CarouselItem className={styles.item} isPaddingless>
            <JoinGoalScreen />
          </CarouselItem>
          <CarouselItem className={styles.item} isPaddingless>
            <JoinAvatarScreen index={SCREEN_MAP.avatar} />
          </CarouselItem>
          <CarouselItem className={styles.item} isPaddingless>
            <JoinSummaryScreen index={SCREEN_MAP.summary} />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
      <FAQDrawer />
      <PrivacyDrawer />
    </Widget>
  )
}

export { JoinPeerToPeerWidget }
