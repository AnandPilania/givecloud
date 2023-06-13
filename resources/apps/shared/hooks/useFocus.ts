import { useRef } from 'react'

interface FocusType {
  focus: () => void
}

export const useFocus = <T extends FocusType>() => {
  const htmlElRef = useRef<T | null>(null)
  const setFocus = () => htmlElRef.current && htmlElRef.current.focus()

  return [htmlElRef, setFocus] as const
}
