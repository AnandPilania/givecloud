import type { FC } from 'react'
import type { InputProps } from '@/aerosol/Input'
import { Input } from '@/aerosol/Input'
import { FocusEvent, useState } from 'react'
import { isBasicEmailRegexValid, isBasicEmailValid, isEmailAtValid, isEmailDotValid } from './validations'

const EmailInput: FC<InputProps> = ({ onBlur, onFocus, value, ...rest }) => {
  const [isTouched, setIsTouched] = useState(false)

  const handleBlur = (e: FocusEvent<HTMLInputElement>) => {
    onBlur?.(e)
    setIsTouched(true)
  }

  const handleFocus = (e: FocusEvent<HTMLInputElement>) => {
    onFocus?.(e)
    setIsTouched(false)
  }

  const getErrors = () => {
    if (!isTouched || !String(value)?.length) return []
    if (isBasicEmailValid(String(value))) return []
    if (!isEmailAtValid(String(value))) return ['@ is required']
    if (!isEmailDotValid(String(value))) return ['Dot is required']
    if (!isBasicEmailRegexValid(String(value))) return ['Not a valid email']
  }
  return <Input {...rest} value={value} type='email' onBlur={handleBlur} onFocus={handleFocus} errors={getErrors()} />
}

export { EmailInput }
