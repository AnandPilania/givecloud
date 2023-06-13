import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { Button } from '@/aerosol/Button'
import { Text } from '@/aerosol/Text'
import { Thermometer } from './Thermometer'

export default {
  title: 'Aerosol/Thermometer',
  component: Thermometer,
  args: {
    initialPercentage: 50,
  },
} as ComponentMeta<typeof Thermometer>

const TARGET_AMOUNT = 100

export const Default: ComponentStory<typeof Thermometer> = ({ initialPercentage }) => {
  const [amount, setAmount] = useState(30)

  const incrementAmount = () => {
    setAmount((prev) => prev + 1)
  }

  const decrementAmount = () => {
    if (amount === 0) return
    setAmount((prev) => prev - 1)
  }

  return (
    <>
      <Thermometer
        additionalPercentage={(amount / TARGET_AMOUNT) * 100}
        initialPercentage={initialPercentage}
        aria-label='storybook thermometer'
      />
      <div className='flex items-center justify-center gap-6 mt-6'>
        <Button onClick={decrementAmount}>-</Button>
        <Text type='h1' isMarginless>
          ${amount}
        </Text>
        <Button onClick={incrementAmount}>+</Button>
      </div>
    </>
  )
}

export const WithNoPercentValues: ComponentStory<typeof Thermometer> = () => (
  <Thermometer additionalPercentage={0} initialPercentage={0} aria-label='storybook thermometer' />
)
