import type { ComponentPropsWithRef, FC, ForwardedRef, SyntheticEvent } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import { forwardRef } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import { Toggle, Accordion, AccordionContent, AccordionHeader, Text, TextArea } from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './TransparencyStatementAccordion.styles.scss'

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const TransparencyStatementAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { transparencyPromiseValue, setTransparencyPromiseState, isTransparencyError } = useFundraisingFormState()
  const { touchedInputs, errors } = transparencyPromiseValue

  const handleTextChange = (e: SyntheticEvent) => {
    const { name, value } = e.target as HTMLInputElement
    if (!value) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        [name]: '',
        errors: {
          ...errors,
          [name]: ['Field is required'],
        },
      })
    } else {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        [name]: value,
        errors: {
          ...errors,
          [name]: [],
        },
      })
    }
  }

  const toggleEnabledState = () => {
    const isPromiseValid = !!transparencyPromiseValue?.transparencyPromiseStatement?.length

    if (transparencyPromiseValue.transparencyPromiseEnabled) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        touchedInputs: {},
        errors: {
          transparencyPromise1Description: [],
          transparencyPromise2Description: [],
          transparencyPromiseStatement: [],
        },
        transparencyPromiseEnabled: false,
      })
    } else if (isPromiseValid) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        transparencyPromiseEnabled: true,
      })
    } else {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        transparencyPromiseEnabled: true,
        touchedInputs: {
          transparencyPromiseStatement: isPromiseValid ? '' : 'transparencyPromiseStatement',
        },
        errors: {
          transparencyPromise1Description: transparencyPromiseValue.errors?.transparencyPromise1Description ?? [],
          transparencyPromise2Description: transparencyPromiseValue.errors?.transparencyPromise2Description ?? [],
          transparencyPromiseStatement: isPromiseValid ? [] : ['Field is required'],
        },
      })
    }
  }

  const handleBlur = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement
    setTransparencyPromiseState({
      ...transparencyPromiseValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement
    setTransparencyPromiseState({
      ...transparencyPromiseValue,
      touchedInputs: {
        [name]: '',
      },
    })
  }

  const getErrors = (name: string) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  const renderErrorIcon = () =>
    isTransparencyError ? <FontAwesomeIcon className='ml-2' icon={faExclamationCircle} /> : null

  return (
    <Accordion hasBorderTop isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root}>
      <AccordionHeader>
        <div className={styles.header}>
          <Text isError={isTransparencyError} isMarginless type='h5' isBold>
            Impact Promise
            {renderErrorIcon()}
          </Text>
          <Toggle
            isEnabled={transparencyPromiseValue.transparencyPromiseEnabled}
            setIsEnabled={toggleEnabledState}
            name='transparency promise'
          />
        </div>
      </AccordionHeader>
      <AccordionContent>
        <Text isSecondaryColour className='mb-6'>
          Communicate a clear and transparent commitment to your donors that builds trust
        </Text>
        <TextArea
          charCountMax={120}
          isAutoGrowing
          isLabelHidden
          label='Impact Promise'
          ref={ref as ForwardedRef<HTMLTextAreaElement>}
          isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
          isMarginless
          value={transparencyPromiseValue.transparencyPromiseStatement}
          onChange={handleTextChange}
          name='transparencyPromiseStatement'
          onBlur={handleBlur}
          onFocus={handleFocus}
          errors={getErrors('transparencyPromiseStatement')}
        />
      </AccordionContent>
    </Accordion>
  )
})

TransparencyStatementAccordion.displayName = 'TransparencyStatementAccordion'

export { TransparencyStatementAccordion }
