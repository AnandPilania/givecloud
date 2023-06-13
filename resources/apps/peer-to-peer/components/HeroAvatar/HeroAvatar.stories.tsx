import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { faRocketLaunch } from '@fortawesome/pro-regular-svg-icons'
import { HeroAvatar } from './HeroAvatar'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Peer to Peer/HeroAvatar',
  component: HeroAvatar,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof HeroAvatar>

interface CustomColour {
  colour: string
}

const image =
  'https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1587&q=80'

export const Default: Story<typeof HeroAvatar & CustomColour> = ({ colour }) => {
  useEffect(() => setRootThemeColour({ colour }), [colour])

  return <HeroAvatar icon={faRocketLaunch} />
}

export const Image: Story<typeof HeroAvatar> = () => <HeroAvatar src={image} theme='primary' />

export const SmallImage: Story<typeof HeroAvatar> = () => <HeroAvatar size='small' src={image} theme='primary' />

export const InitialsDefault: Story<typeof HeroAvatar & CustomColour> = ({ colour }) => {
  useEffect(() => setRootThemeColour({ colour }), [colour])

  return <HeroAvatar initials='ZZ' />
}

export const InitialsSmall: Story<typeof HeroAvatar & CustomColour> = ({ colour }) => {
  useEffect(() => setRootThemeColour({ colour }), [colour])

  return <HeroAvatar initials='ZZ' size='small' />
}
