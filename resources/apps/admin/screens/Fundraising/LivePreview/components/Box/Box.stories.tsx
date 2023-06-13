import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { Box } from './Box'
import { Icon } from '../Icon'
import { faArrowUp } from '@fortawesome/free-solid-svg-icons'
import { Skeleton } from '../Skeleton'
import { Button } from '../Button'

export default {
  title: 'Live Preview / Box',
  component: Box,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof Box>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof Box & CustomColour> = ({ colour }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <div className='bg-grey-300 flex justify-center items-center p-4'>
      <Box>
        <Icon icon={faArrowUp} className='mt-10' />
        <span className='font-bold text-[22px] text-center mt-3'>Upgrade Your Impact</span>
        <Skeleton className='mt-4 mb-6' />
        <Button>$99/mon</Button>
        <div className='flex mt-3 w-full'>
          <Button className='mr-4'>$99/mon</Button>
          <Button>$99/mon</Button>
        </div>
      </Box>
    </div>
  )
}

export const Small: Story<typeof Box & CustomColour> = ({ colour }) => {
  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <div className='bg-grey-300 flex justify-center items-center p-4'>
      <Box size='small'>
        <div className='flex flex-col items-center mt-5'>
          <span className='font-bold text-[22px] text-center mt-3'>Are You Sure?</span>
          <Skeleton className='mt-4 mb-6' />
          <Button>$99/mon</Button>
        </div>
      </Box>
    </div>
  )
}
