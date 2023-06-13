import type { ComponentPropsWithRef, FC, FocusEvent } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import { forwardRef } from 'react'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle, faPercentage, faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import {
  Column,
  Columns,
  Toggle,
  Accordion,
  AccordionContent,
  AccordionHeader,
  Input,
  Text,
  Tooltip,
  Label,
} from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './TransparencyPromiseAccordion.styles.scss'

const isWholeNumber = (value: string) => new RegExp('^[1-9]\\d*$').test(value)

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const TransparencyPromiseAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { transparencyPromiseValue, setTransparencyPromiseState, isTransparencyError } = useFundraisingFormState()
  const { touchedInputs, errors } = transparencyPromiseValue

  const handleTextChange = (e: FocusEvent<HTMLInputElement>) => {
    const { name, value } = e.target
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

  const handlePercentChange = (e: FocusEvent<HTMLInputElement>) => {
    const { value } = e.target as HTMLInputElement
    if (!value) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        //@ts-ignore
        transparencyPromise1Percentage: '',
        transparencyPromise2Percentage: 100,
      })
    } else if (Number(value) <= 100 && isWholeNumber(value)) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        //@ts-ignore
        transparencyPromise1Percentage: Number(value).toFixed(),
        transparencyPromise2Percentage: 100 - Number(value),
      })
    }
  }

  const toggleEnabledState = () => {
    const isPromiseOneValid = !!transparencyPromiseValue.transparencyPromise1Description?.length
    const isPromiseTwoValid = !!transparencyPromiseValue.transparencyPromise2Description?.length
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
    } else if (isPromiseOneValid && isPromiseTwoValid) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        transparencyPromiseEnabled: true,
      })
    } else {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        transparencyPromiseEnabled: true,
        touchedInputs: {
          transparencyPromise1Description: isPromiseOneValid ? '' : 'transparencyPromise1Description',
          transparencyPromise2Description: isPromiseTwoValid ? '' : 'transparencyPromise2Description',
        },
        errors: {
          transparencyPromise1Description: isPromiseOneValid ? [] : ['Field is required'],
          transparencyPromise2Description: isPromiseTwoValid ? [] : ['Field is required'],
          transparencyPromiseStatement: transparencyPromiseValue.errors?.transparencyPromiseStatement ?? [],
        },
      })
    }
  }

  const handleBlur = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target
    setTransparencyPromiseState({
      ...transparencyPromiseValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handlePercentageOnBlur = () => {
    if (!transparencyPromiseValue.transparencyPromise1Percentage) {
      setTransparencyPromiseState({
        ...transparencyPromiseValue,
        transparencyPromise1Percentage: 0,
        transparencyPromise2Percentage: 100,
      })
    }
  }

  const getErrors = (name: string) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  const tooltipContent = (
    <Text isMarginless isBold>
      We'll calculate the remainder for you.
    </Text>
  )

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
        <Text isSecondaryColour className='mb-4'>
          Communicate a clear and transparent commitment to your donors that builds trust.
        </Text>
        <Columns isResponsive={false}>
          <Column>
            <Input
              ref={ref}
              min={1}
              max={100}
              isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
              isMarginless
              type='number'
              icon={faPercentage}
              value={transparencyPromiseValue.transparencyPromise1Percentage}
              onChange={handlePercentChange}
              name='transparencyPromise1Percentage'
              label='Percentage'
              onBlur={handlePercentageOnBlur}
            />
          </Column>
          <Column columnWidth='six'>
            <Input
              charCountMax={50}
              isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
              isMarginless
              value={transparencyPromiseValue.transparencyPromise1Description}
              onChange={handleTextChange}
              name='transparencyPromise1Description'
              label='Promise Message One'
              onBlur={handleBlur}
              errors={getErrors('transparencyPromise1Description')}
            />
          </Column>
        </Columns>
        <Columns isResponsive={false}>
          <Column>
            <Label
              isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
              htmlFor='transparencyPromise2Percentage'
            >
              <span className='mr-2'>Remainder</span>
              <Tooltip isHidden={!transparencyPromiseValue.transparencyPromiseEnabled} tooltipContent={tooltipContent}>
                <FontAwesomeIcon
                  icon={faInfoCircle}
                  className={classNames(
                    styles.icon,
                    transparencyPromiseValue.transparencyPromiseEnabled ? styles.enabled : styles.disabled
                  )}
                />
              </Tooltip>
            </Label>
            <Input
              isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
              min={0}
              isReadOnly
              isMarginless
              type='number'
              icon={faPercentage}
              value={transparencyPromiseValue.transparencyPromise2Percentage}
              name='transparencyPromise2Percentage'
            />
          </Column>
          <Column columnWidth='six'>
            <Input
              charCountMax={50}
              isDisabled={!transparencyPromiseValue.transparencyPromiseEnabled}
              isMarginless
              value={transparencyPromiseValue.transparencyPromise2Description}
              onChange={handleTextChange}
              name='transparencyPromise2Description'
              label='Promise Message Two'
              onBlur={handleBlur}
              errors={getErrors('transparencyPromise2Description')}
            />
          </Column>
        </Columns>
      </AccordionContent>
    </Accordion>
  )
})

TransparencyPromiseAccordion.displayName = 'TransparencyPromiseAccordion'

export { TransparencyPromiseAccordion }
