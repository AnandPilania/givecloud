import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { faHeart } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Text } from '@/aerosol'
import { ImpactPromise } from './ImpactPromise'

export default {
  title: 'Peer to Peer/ImpactPromise',
  component: ImpactPromise,
} as ComponentMeta<typeof ImpactPromise>

export const Default: ComponentStory<typeof ImpactPromise> = () => (
  <ImpactPromise>
    <Text type='footnote' isSecondaryColour>
      A+ Impact Promise <FontAwesomeIcon icon={faHeart} className='ml-0.5' />
    </Text>
    <Text type='footnote' isSecondaryColour className='font-normal'>
      100% of your donation ensures families in our community have a nutricious full pantry.
    </Text>
  </ImpactPromise>
)
