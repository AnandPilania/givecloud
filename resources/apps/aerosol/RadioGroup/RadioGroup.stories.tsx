import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { AvatarType } from './AvatarTile/avatars'
import { useState } from 'react'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { RadioButton } from './RadioButton'
import { RadioGroup } from './RadioGroup'
import { RadioTile } from './RadioTile'
import { ColourTile } from './ColourTile'
import { Text } from '@/aerosol/Text'
import { Container } from '@/aerosol/Container'
import { COLOURS } from '@/shared/constants/theme'

import { avatars } from './AvatarTile/avatars'
import { AvatarTile } from './AvatarTile'
import { chunkArray } from '@/shared/utilities/chunkArray'
import LoveSVG from './AvatarTile/svgs/love.svg?react'
import GiftSVG from './AvatarTile/svgs/gift.svg?react'
import ParachuteSVG from './AvatarTile/svgs/parachute.svg?react'
import CatSVG from './AvatarTile/svgs/cat.svg?react'
import FoodSVG from './AvatarTile/svgs/food.svg?react'
import BiodegradableSVG from './AvatarTile/svgs/biodegradable.svg?react'
import ConfettiSVG from './AvatarTile/svgs/confetti.svg?react'
import DogSVG from './AvatarTile/svgs/dog.svg?react'
import KnowledgeSVG from './AvatarTile/svgs/knowledge.svg?react'
import RingsSVG from './AvatarTile/svgs/rings.svg?react'
import WaterSVG from './AvatarTile/svgs/water.svg?react'

export default {
  title: 'Aerosol/Radio Group',
  component: RadioGroup,
  args: {
    label: 'Default RadioGroup',
    name: 'default',
    isLabelVisible: true,
    row: false,
    isDisabled: false,
  },
  argsTypes: {
    isLabelVisible: {
      control: 'boolean',
    },
    row: {
      control: 'boolean',
    },
    onChange: {
      action: 'click',
    },
    isDisabled: {
      control: 'boolean',
    },
  },
} as ComponentMeta<typeof RadioGroup>

export const Default: ComponentStory<typeof RadioGroup> = (args) => {
  const [value, setValue] = useState('option1')

  return (
    <RadioGroup {...args} name='default group' label='default group' checkedValue={value} onChange={setValue}>
      <RadioButton id='one' label='Label 1' description='Example: description 1' value='option1' />
      <RadioButton id='two' label='Label 2' description='Example: description 2' value='option2' />
    </RadioGroup>
  )
}

export const WithRadioTiles = () => {
  const [value, setValue] = useState('option1')

  return (
    <RadioGroup
      showInput={false}
      label='RadioTiles'
      name='tiles'
      isLabelVisible={true}
      checkedValue={value}
      onChange={setValue}
    >
      <Columns className='w-full' isResponsive={false}>
        <Column>
          <RadioButton id='one' value='option1'>
            <RadioTile>
              <Text type='h5' isBold>
                One
              </Text>
              <Text isSecondaryColour>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Eos reiciendis at architecto incidunt suscipit,
                expedita natus dicta excepturi accusamus ad, aperiam sapiente tempore modi ea, placeat vel atque nisi
                quasi!
              </Text>
            </RadioTile>
          </RadioButton>
        </Column>
        <Column>
          <RadioButton id='two' value='option2'>
            <RadioTile>
              <Text type='h5' isBold>
                Two
              </Text>
              <Text isSecondaryColour>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Eos reiciendis at architecto incidunt suscipit,
                expedita natus dicta excepturi accusamus ad, aperiam sapiente tempore modi ea, placeat vel atque nisi
                quasi!
              </Text>
            </RadioTile>
          </RadioButton>
        </Column>
      </Columns>
    </RadioGroup>
  )
}

export const WithColourTiles = () => {
  const [colour, setColour] = useState('blue')

  return (
    <RadioGroup label='Colour theme' name='colours' onChange={setColour} checkedValue={colour} showInput={false}>
      <Columns isResponsive={false} isStackingOnMobile={false} isWrapping className='ml-1 mt-0.5'>
        {COLOURS.map(({ value, code }) => (
          <Column key={value} isPaddingless columnWidth='small'>
            <RadioButton id={value} value={code}>
              <ColourTile colour={{ value, code }} />
            </RadioButton>
          </Column>
        ))}
      </Columns>
    </RadioGroup>
  )
}

const mappedAvatars = {
  social:
    'https://images.unsplash.com/photo-1503023345310-bd7c1de61c7d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=930&q=80',
  love: LoveSVG,
  gift: GiftSVG,
  parachute: ParachuteSVG,
  cat: CatSVG,
  dog: DogSVG,
  food: FoodSVG,
  biodegradable: BiodegradableSVG,
  confetti: ConfettiSVG,
  knowledge: KnowledgeSVG,
  rings: RingsSVG,
  water: WaterSVG,
}
export const WithAvatarTile = () => {
  const [selected, setSelected] = useState('social')

  const renderAvatar = (avatarType: AvatarType) => {
    return (
      <Column key={avatarType.name} isPaddingless>
        <RadioButton id={avatarType.name} value={avatarType.name}>
          <AvatarTile className='mr-1'>
            <img src={mappedAvatars[avatarType.name]} alt={avatarType.altText} />
          </AvatarTile>
        </RadioButton>
      </Column>
    )
  }

  const renderRow = (row: AvatarType[], index: number) => {
    return (
      <Columns isResponsive={false} isStackingOnMobile={false} key={index}>
        {row.map((avatarType: AvatarType) => renderAvatar(avatarType))}
      </Columns>
    )
  }

  return (
    <Container containerWidth='extraSmall'>
      <div className='w-48'>
        <RadioGroup
          showInput={false}
          isLabelVisible={false}
          label='avatars'
          name='avatars'
          checkedValue={selected}
          onChange={setSelected}
        >
          {chunkArray<AvatarType>(avatars, 4).map((avatarRow, index) => renderRow(avatarRow, index))}
        </RadioGroup>
      </div>
    </Container>
  )
}
