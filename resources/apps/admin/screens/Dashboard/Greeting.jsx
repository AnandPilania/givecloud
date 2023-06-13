import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import Emoji from 'react-emoji-render'

import configState from '@/atoms/config'
import usePageTitle from '@/hooks/usePageTitle'
import useTimeOfDay from '@/hooks/useTimeOfDay'

const chooseEmoji = () => {
  const emojis = [':tada:', ':raised_hands:', ':purple_heart:', ':sunny:', ':star-struck:', ':heart_eyes:', ':wink:']
  return emojis[Math.floor(Math.random() * emojis.length)]
}

const Greeting = () => {
  usePageTitle('Home')
  const [emoji] = useState(chooseEmoji())
  const timeOfDay = useTimeOfDay()
  const { userFirstName = '' } = useRecoilValue(configState)

  return (
    <section className='max-w-7xl mx-auto'>
      <h2 className='flex items-center mb-8 text-4xl font-bold '>
        <span>
          {timeOfDay} {userFirstName}
        </span>
        <Emoji
          className='block ml-2 cursor-not-allowed relative rotate-12 hover:scale-110 hover:rotate-6'
          text={emoji}
        />
      </h2>
    </section>
  )
}

export default Greeting
