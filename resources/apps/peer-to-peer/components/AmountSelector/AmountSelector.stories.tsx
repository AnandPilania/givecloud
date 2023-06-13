import type { Meta, Story } from '@storybook/react'
import { useState, useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { AmountSelector } from './AmountSelector'

export default {
  title: 'Peer to Peer/Amount Selector',
  component: AmountSelector,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof AmountSelector>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof AmountSelector & CustomColour> = ({ colour }) => {
  const [value, setValue] = useState(24.5)

  useEffect(() => setRootThemeColour({ colour }), [colour])

  return <AmountSelector value={value} currency='USD' onChange={setValue} />
}

export const WithMaxAmount: Story<typeof AmountSelector & CustomColour> = ({ colour }) => {
  const [value, setValue] = useState(75)

  useEffect(() => setRootThemeColour({ colour }))

  return <AmountSelector value={value} currency='USD' onChange={setValue} maxValue={100} />
}
