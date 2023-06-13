import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { COLOURS, ColoursType } from '@/shared/constants/theme'
import { ColourPicker } from './ColourPicker'

export default {
  title: 'Aerosol/ColorPicker',
  component: ColourPicker,
} as ComponentMeta<typeof ColourPicker>

export const Default: ComponentStory<typeof ColourPicker> = () => {
  const [selectedColour, setSelectedColour] = useState(COLOURS[0])

  const onChange = (colour: ColoursType) => {
    setSelectedColour(colour)
  }

  return (
    <div className='w-full flex justify-center'>
      <ColourPicker
        colour={selectedColour}
        onChange={onChange}
        aria-label='Select template theme color'
        placement='bottom'
      />
    </div>
  )
}
