import { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { CommandInputOption } from './CommandInputOption'
import { CommandInput, SelectedType } from './CommandInput'
import { faSeal } from '@fortawesome/pro-regular-svg-icons'

export default {
  title: 'Aerosol/Command Input',
  component: CommandInput,
  args: {
    label: 'Peepz',
    isOptional: false,
    isDisabled: false,
    isLabelHidden: false,
    icon: faSeal,
    placeholder: '',
  },
  argTypes: {
    label: {
      control: 'text',
    },
    isOptional: {
      control: 'boolean',
    },
    isLabelHidden: {
      control: 'boolean',
    },
    isDisabled: {
      control: 'boolean',
    },
    errors: {
      name: 'errors (comma separated array)',
      defaultValue: [],
      control: {
        type: 'object',
      },
    },
    icon: {
      options: ['search', 'heart', 'percentage', 'user'],
      control: { type: 'radio' },
    },
    name: {
      control: false,
    },
    placeholder: {
      control: 'text',
    },
  },
} as ComponentMeta<typeof CommandInput>

interface Peeps {
  id: string
  name: string
}

const people = [
  { id: '1', name: 'Wade Cooper' },
  { id: '2', name: 'Arlene Mccoy' },
  { id: '3', name: 'Devon Webb' },
  { id: '4', name: 'Tom Cook' },
  { id: '5', name: 'Tanya Fox' },
  { id: '6', name: 'Hellen Schmidt' },
]

interface Person {
  id: string
  name: string
}

export const Default: ComponentStory<typeof CommandInput> = ({ ...rest }) => {
  const [selected, setSelected] = useState<SelectedType<Person>>('')

  const [query, setQuery] = useState('')
  const filteredPeople =
    query === ''
      ? people
      : people.filter((person) =>
          person.name.toLowerCase().replace(/\s+/g, '').includes(query.toLowerCase().replace(/\s+/g, ''))
        )
  const isQueryEmpty = !filteredPeople.length && query !== ''

  return (
    <CommandInput<Person>
      {...rest}
      customOption={{ id: '7', name: query }}
      isQueryEmpty={isQueryEmpty}
      query={query}
      displayValue={(value) => (!value ? '' : value['name'])}
      selected={selected}
      setSelected={setSelected}
      setQuery={setQuery}
      name='people'
    >
      {filteredPeople.map((people) => (
        <CommandInputOption key={people.id} value={people}>
          {people.name}
        </CommandInputOption>
      ))}
    </CommandInput>
  )
}
