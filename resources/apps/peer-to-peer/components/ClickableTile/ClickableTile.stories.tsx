import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { BrowserRouter } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { formatMoney } from '@/shared/utilities'
import { Container, Columns, Column, Thermometer } from '@/aerosol'
import { ClickableTile } from './ClickableTile'
import { HeroAvatar } from '../HeroAvatar'
import { Text } from '../Text'

export default {
  title: 'Peer to Peer/ClickableTile',
  component: ClickableTile,
} as ComponentMeta<typeof ClickableTile>

const list = [
  {
    name: 'Peter',
    img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    amount: 50,
  },
  {
    name: 'Kate',
    img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    amount: 10,
  },
  {
    name: 'Carlos',
    img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    amount: 70,
  },
  {
    name: 'Obama',
    img: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    amount: 60,
  },
]

export const Default: ComponentStory<typeof ClickableTile> = () => {
  return (
    <BrowserRouter>
      <Container containerWidth='extraSmall'>
        <Columns isMarginless>
          <Column>
            {list.map(({ img, name, amount }, index) => (
              <ClickableTile to='/' key={index}>
                <Columns isResponsive={false} isStackingOnMobile={false}>
                  <Column columnWidth='small'>
                    <HeroAvatar size='small' isMarginless src={img} />
                  </Column>
                  <Column>
                    <Text isBold>
                      {name}’s Challenge <FontAwesomeIcon icon={faArrowRight} className='ml-1 transform' />
                    </Text>
                    <div className='flex'>
                      <Thermometer
                        initialPercentage={amount}
                        additionalPercentage={0}
                        className='mr-2'
                        aria-hidden={true}
                      />
                      <Text isBold isMarginless>
                        <span className='sr-only'>amount raised so far:</span>
                        {formatMoney({ amount, notation: 'compact' })}
                      </Text>
                    </div>
                  </Column>
                </Columns>
              </ClickableTile>
            ))}
          </Column>
        </Columns>
      </Container>
    </BrowserRouter>
  )
}
