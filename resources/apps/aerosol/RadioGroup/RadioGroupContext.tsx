import { createContext, useContext } from 'react'

interface RadioGroupContextData {
  name: string
  onChange: (value: string) => void
  checkedValue: string | undefined
  isDisabled?: boolean
  showInput?: boolean
}

const RadioGroupContext = createContext<RadioGroupContextData | null>(null)
const useRadioGroupContext = () => {
  const context = useContext(RadioGroupContext)
  if (context === null) throw new Error('useRadioGroupContext is not being used within a provider')
  return context
}

export { RadioGroupContext, useRadioGroupContext }
export type { RadioGroupContextData as RadioGroupProps }
