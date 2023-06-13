import type { Meta, Story } from '@storybook/react'
import { useState } from 'react'
import { CurrencySelector } from './CurrencySelector'

export default {
  title: 'Peer to Peer/Currency Selector',
  component: CurrencySelector,
} as Meta<typeof CurrencySelector>

export const Default: Story<typeof CurrencySelector> = () => {
  const [currencyCode, setCurrencyCode] = useState('CAD')

  const onChange = (currencyCode: string) => {
    setCurrencyCode(currencyCode)
  }

  return (
    <div className='flex justify-center'>
      <CurrencySelector currencyCode={currencyCode} onChange={onChange} />
    </div>
  )
}
