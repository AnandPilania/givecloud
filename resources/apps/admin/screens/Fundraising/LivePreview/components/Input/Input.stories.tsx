import type { Meta, Story } from '@storybook/react'
import { Input } from './Input'
import { faPlus } from '@fortawesome/pro-light-svg-icons'

export default {
  title: 'Live Preview / Input',
  component: Input,
} as Meta<typeof Input>

export const Default: Story<typeof Input> = () => {
  return (
    <div className='w-56'>
      <Input icon={faPlus} />
    </div>
  )
}
