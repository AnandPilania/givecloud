import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { MouseEvent } from 'react'
import { useEffect, useState } from 'react'
import { BrowserRouter } from 'react-router-dom'
import { Dropdown } from './Dropdown'
import { DropdownButton } from './DropdownButton'
import { DropdownItems } from './DropdownItems'
import { DropdownItem } from './DropdownItem'
import { DropdownHeader } from './DropdownHeader'
import { DropdownLabel } from './DropdownLabel'
import { DropdownContent } from './DropdownContent'
import { Text } from '@/aerosol/Text'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'

export default {
  title: 'Aerosol/Dropdown',
  component: Dropdown,
  argTypes: {
    colour: { control: 'color' },
  },
} as ComponentMeta<typeof Dropdown>

export const Label: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='primary' value={value} aria-label='sr-only label'>
          <DropdownLabel>I am a label</DropdownLabel>
          <DropdownContent>
            <DropdownButton>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const Header: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='primary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton>{value}</DropdownButton>
            <DropdownItems>
              <DropdownHeader>
                <Text type='h4' isMarginless>
                  Header
                </Text>
              </DropdownHeader>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const Primary: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='primary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const PrimaryOutlined: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='primary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isOutlined>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const PrimaryClean: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='primary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isClean>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const Secondary: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='secondary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const SecondaryOutlined: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='secondary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isOutlined>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const SecondaryClean: ComponentStory<typeof Dropdown> = () => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='secondary' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isClean>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const Custom = ({ colour }) => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='custom' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const CustomOutlined = ({ colour }) => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='custom' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isOutlined>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}

export const CustomClean = ({ colour }) => {
  const [value, setValue] = useState('one')

  const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
    const { target } = e
    setValue((target as HTMLButtonElement).value)
  }

  useEffect(() => {
    setRootThemeColour({ colour })
  }, [colour])

  return (
    <BrowserRouter>
      <div className='w-full flex justify-center items-center'>
        <Dropdown theme='custom' value={value} aria-label='sr-only label'>
          <DropdownContent>
            <DropdownButton isClean>{value}</DropdownButton>
            <DropdownItems>
              <DropdownItem onClick={handleClick} value='one' />
              <DropdownItem onClick={handleClick} value='two'>
                two
              </DropdownItem>
            </DropdownItems>
          </DropdownContent>
        </Dropdown>
      </div>
    </BrowserRouter>
  )
}
