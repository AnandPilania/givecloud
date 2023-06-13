import type { Meta, Story } from '@storybook/react'
import type { EventWithIndex } from './CodeInputs'
import { useEffect, useState } from 'react'
import { Button } from '@/aerosol'
import { CodeInputs } from './CodeInputs'
import { useFocus } from '@/shared/hooks'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Peer to Peer/CodeInputs',
  component: CodeInputs,
  argTypes: {
    colour: { control: 'color ' },
  },
} as Meta<typeof CodeInputs>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof CodeInputs & CustomColour> = ({ colour }) => {
  const [values, setValues] = useState([
    {
      name: 'one',
      value: '',
    },
    {
      name: 'two',
      value: '',
    },
    {
      name: 'three',
      value: '',
    },
    {
      name: 'four',
      value: '',
    },
  ])
  const [buttonRef, setButtonFocus] = useFocus<HTMLButtonElement>()

  useEffect(() => setRootThemeColour({ colour }), [colour])

  const handleStateValues = (value: string, index: number) => {
    setValues((prevState) => {
      const copy = [...prevState]
      copy[index] = { ...copy[index], value }
      return copy
    })
  }

  const handleChange = ({ target }: EventWithIndex) => {
    const { value, index } = target
    handleStateValues(value, index)
  }

  return (
    <div style={{ width: '220px' }}>
      <CodeInputs nextFocusedElement={setButtonFocus} inputValues={values} onChange={handleChange} />
      <Button ref={buttonRef} className='mt-8'>
        Verify Code
      </Button>
    </div>
  )
}
