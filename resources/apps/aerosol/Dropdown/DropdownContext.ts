import type { Dispatch } from 'react'
import type { DropdownProps } from './Dropdown'
import type { FloatProps } from '@headlessui-float/react'
import { createContext, useContext } from 'react'

interface DropdownContext extends Partial<DropdownProps> {
  placement: FloatProps['placement']
  isOpen: boolean
  setIsOpen: Dispatch<React.SetStateAction<boolean>>
  setSelected: (value: string) => void
  selected: string
  toggleIsOpen: () => void
}

const DropdownContext = createContext<DropdownContext | null>(null)
const useDropdownContext = () => {
  const context = useContext(DropdownContext)
  if (context === null) throw new Error('useDropdownContext is not being used within a provider')
  return context
}

export { DropdownContext, useDropdownContext }
