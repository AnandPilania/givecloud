import { useRecoilState } from 'recoil'
import { errorBag as errorBagState, shouldValidateBag as shouldValidateBagState } from '@/atoms/errorBag'
import { except, objectHas } from '@/utilities/object'

const substitutions = [
  [/billing[_\s]first[_\s]name/, 'first name'],
  [/billing[_\s]last[_\s]name/, 'last name'],
  [/billing[_\s]email/, 'email'],
  [/billing[_\s]address\s?1/, 'address'],
  [/billing[_\s]city/, 'city'],
  [/billing[_\s]zip/, 'zip'],
  [/billing[_\s]country[_\s]code/, 'country'],
  [/The ([aeiou].+) field is required/i, 'Please enter an $1'],
  [/The ([^aeiou].+) field is required/i, 'Please enter a $1'],
]

export const applySubstitutions = (message) => {
  message = String(message || '')

  substitutions.forEach((substitution) => {
    const [searchValue, replaceValue] = substitution
    message = message.replace(searchValue, replaceValue)
  })

  return message
}

const useErrorBag = () => {
  const [errorBag, setErrorBag] = useRecoilState(errorBagState)
  const [shouldValidateBag, setShouldValidateBag] = useRecoilState(shouldValidateBagState)

  const updateErrorBag = (errors) => {
    setErrorBag({ ...errors, length: Object.keys(except(errors, 'length')).length })
  }

  const setError = (key, message = null) => {
    const keys = Array.isArray(key) ? key : [key]

    if (message) {
      const friendlyMessage = applySubstitutions(message)

      // prettier-ignore
      updateErrorBag(keys.reduce(
        (obj, key) => ({ ...obj, [key]: friendlyMessage }),
        { ...errorBag }
      ))

      return friendlyMessage
    }

    if (objectHas(errorBag, keys)) {
      updateErrorBag(except(errorBag, keys))
    }

    return ''
  }

  const setShouldValidate = (key, shouldValidate = true) => {
    const keys = Array.isArray(key) ? key : [key]

    // prettier-ignore
    setShouldValidateBag(keys.reduce(
      (obj, key) => ({ ...obj, [key]: Boolean(shouldValidate) }),
      { ...shouldValidateBag }
    ))
  }

  return { errorBag, setError, shouldValidateBag, setShouldValidate }
}

export default useErrorBag
