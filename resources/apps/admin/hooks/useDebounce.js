import debounce from 'lodash.debounce'
import { useRef } from 'react'

const useDebounce = (fn, delay) => {
  const ref = useRef(debounce(fn, delay))
  return ref.current
}

export { useDebounce }
