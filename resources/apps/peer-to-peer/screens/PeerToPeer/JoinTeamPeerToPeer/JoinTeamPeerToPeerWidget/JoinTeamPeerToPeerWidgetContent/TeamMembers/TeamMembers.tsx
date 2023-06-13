import type { FC } from 'react'
import type { PeerToPeerCampaign } from '@/types'
import { Carousel, CarouselItems, CarouselItem, CarouselNextButton, CarouselPreviousButton } from '@/aerosol'
import { chunkArray } from '@/shared/utilities/chunkArray'
import { faChevronRight } from '@fortawesome/pro-light-svg-icons'
import { faChevronLeft } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { TeamMemberTile } from './TeamMemberTile'
import styles from './TeamMembers.styles.scss'

interface Props {
  team: PeerToPeerCampaign[]
}

const renderTeamMember = (teamMember: PeerToPeerCampaign) => (
  <TeamMemberTile key={teamMember.id} teamMember={teamMember} />
)

const renderCarouselItem = (chunk: PeerToPeerCampaign[], index: number) => (
  <CarouselItem isPaddingless className={styles.item} key={index}>
    {chunk.map(renderTeamMember)}
  </CarouselItem>
)

const TeamMembers: FC<Props> = ({ team }) => {
  const { large, medium } = useTailwindBreakpoints()

  const breakpoints = [
    { breakPoint: medium.greaterThan, limit: 2 },
    { breakPoint: medium.lessThan, limit: 3 },
    { breakPoint: large.lessThan, limit: 2 },
  ]

  const numberOfChunks = breakpoints.find(({ breakPoint }) => breakPoint)?.limit

  const chunkedList = chunkArray<PeerToPeerCampaign>(team, numberOfChunks)

  const renderItems = () => chunkedList.map(renderCarouselItem)

  const renderButtons = () => {
    const hideButtons = breakpoints.some(({ breakPoint, limit }) => breakPoint && team.length <= limit)

    return hideButtons ? null : (
      <div className={styles.buttonContainer}>
        <CarouselPreviousButton isFullyRounded theme='custom' className='mx-2'>
          <FontAwesomeIcon icon={faChevronLeft} />
        </CarouselPreviousButton>
        <CarouselNextButton isFullyRounded theme='custom' className='mx-2'>
          <FontAwesomeIcon icon={faChevronRight} />
        </CarouselNextButton>
      </div>
    )
  }

  return (
    <div className={styles.container}>
      <Carousel name='team-members'>
        <CarouselItems className={styles.wrapper}>{renderItems()}</CarouselItems>
        {renderButtons()}
      </Carousel>
    </div>
  )
}

export { TeamMembers }
