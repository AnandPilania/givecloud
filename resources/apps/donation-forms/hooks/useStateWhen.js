import { useState } from 'react'

const useStateWhen = (setState = null, initialValue = '') => {
  const [input, setInput] = useState(initialValue)

  const setInputWhen = (value, pattern = null, callback = null) => {
    const matches = pattern ? value.match(pattern) : [value]

    if (matches) {
      const newValue = typeof callback === 'function' ? callback(matches) : callback || value

      setInput(newValue)

      if (typeof setState === 'function') {
        setState(newValue)
      }
    }
  }

  return [input, setInputWhen]
}

export default useStateWhen
