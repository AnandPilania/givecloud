import type { Meta, Story } from '@storybook/react'
import type { ChangeEvent } from 'react'
import { useState, useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { CodeInput } from './CodeInput'

export default {
  title: 'Peer to Peer/CodeInput',
  component: CodeInput,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof CodeInput>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof CodeInput & CustomColour> = ({ colour }) => {
  const [inputs, setInputs] = useState({
    valueOne: '',
    valueTwo: '',
    valueThree: '',
    valueFour: '',
  })

  useEffect(() => setRootThemeColour({ colour }), [colour])

  const onChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    const { value, name } = target

    setInputs((prevState) => ({
      ...prevState,
      [name]: value,
    }))
  }

  return (
    <div className='flex gap-2' style={{ width: '225px' }}>
      <CodeInput name='valueOne' value={inputs.valueOne} onChange={onChange} />
      <CodeInput name='valueTwo' value={inputs.valueTwo} onChange={onChange} />
      <CodeInput name='valueThree' value={inputs.valueThree} onChange={onChange} />
      <CodeInput name='valueFour' value={inputs.valueFour} onChange={onChange} />
    </div>
  )
}
