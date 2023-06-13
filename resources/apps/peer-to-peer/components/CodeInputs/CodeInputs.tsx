import type { ChangeEvent, FC, KeyboardEvent, MutableRefObject } from 'react'
import type { CodeInputProps } from '../CodeInput'
import { useEffect, useMemo } from 'react'
import { useFocus } from '@/shared/hooks'
import { CodeInput } from '../CodeInput'
import styles from './CodeInputs.styles.scss'

interface CodeInput {
  name: string
  value: string
}

interface EventWithIndex extends Omit<ChangeEvent<HTMLInputElement>, 'target'> {
  target: HTMLInputElement & { index: number }
}

interface Props extends Omit<CodeInputProps, 'onChange' | 'onFocus'> {
  inputValues: CodeInput[]
  onChange?: (e: EventWithIndex) => void
  nextFocusedElement?: () => void | null
}

const CodeInputs: FC<Props> = ({ inputValues, onChange, nextFocusedElement, ...rest }) => {
  const refs = inputValues.map(() => {
    const [...rest] = useFocus<HTMLInputElement>()
    return [...rest]
  }) as [[MutableRefObject<HTMLInputElement>, () => void]]

  useEffect(() => {
    const [_, focusOnFirstInput] = refs[0]
    const isCodePrefilled = inputValues.every(({ value }) => !!value.length)
    if (isCodePrefilled) nextFocusedElement?.()
    else focusOnFirstInput()
  }, [])

  const handleChange = (e: ChangeEvent<HTMLInputElement>, index: number) => {
    const isBackspace = e.target.value === ''

    return (setNextFocus: (index: number, isBackspace: boolean) => void) => {
      onChange?.({
        ...e,
        target: {
          ...e.target,
          index,
        },
      })
      return setNextFocus?.(index, isBackspace)
    }
  }

  return (
    <div className={styles.root}>
      {inputValues.map(({ name, value }, index) => {
        const lastIndex = refs.length - 1
        const nextIndex = index === lastIndex ? lastIndex : index + 1
        const previousIndex = index === 0 ? 0 : index - 1
        const [ignoredNextInput, focusNextInput] = refs[nextIndex]
        const [ignoredPreviousInput, focusPreviousInput] = refs[previousIndex]

        const handleNextFocus = (index: number, isBackspace: boolean) => {
          if (index === lastIndex && !isBackspace) {
            return nextFocusedElement?.()
          }
          if (isBackspace) {
            focusPreviousInput()
          } else {
            focusNextInput()
          }
        }

        const keyMap = useMemo(
          () => ({
            ArrowLeft: focusPreviousInput,
            ArrowRight: focusNextInput,
          }),
          []
        )

        const handleKeyDown = ({ key }: KeyboardEvent<HTMLInputElement>) => keyMap?.[key]?.()

        return (
          <CodeInput
            {...rest}
            ref={refs[index][0]}
            key={name}
            name={name}
            value={value}
            onChange={(e: ChangeEvent<HTMLInputElement>) => handleChange(e, index)(handleNextFocus)}
            onKeyDown={handleKeyDown}
            aria-label={`Enter letter ${index + 1} of your join code`}
          />
        )
      })}
    </div>
  )
}

export { CodeInputs, EventWithIndex }
