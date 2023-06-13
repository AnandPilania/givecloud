import { useState } from 'react'
import { Box, Column, Columns, Text, Badge, TextArea, Toggle } from '@/aerosol'
import { UpsellPreview } from '@/screens/Fundraising/LivePreview/UpsellPreview'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import styles from './UpsellCard.scss'

const UpsellCard = () => {
  const { medium } = useTailwindBreakpoints()
  const { upsellValue, setUpsellState } = useFundraisingFormState()
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [messageRef, setMessageRef] = useFocus()
  const { touchedInputs, errors } = upsellValue

  const handleChange = (e) => {
    const { name, value } = e.target
    if (!value) {
      setUpsellState({
        ...upsellValue,
        [name]: '',
        errors: {
          ...errors,
          [name]: ['Field is required'],
        },
        touchedInputs: {
          [name]: '',
        },
      })
    } else {
      setUpsellState({
        ...upsellValue,
        [name]: value,
        errors: {
          ...errors,
          [name]: [],
        },
      })
    }
  }

  const handleBlur = ({ target }) => {
    const { name } = target
    setUpsellState({
      ...upsellValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }) => {
    const { name } = target
    setUpsellState({
      ...upsellValue,
      touchedInputs: {
        [name]: '',
      },
    })
  }

  const handleToggleState = () => {
    const isUpsellDescriptionValid = !!upsellValue.upsellDescription.length

    if (upsellValue.upsellEnabled) {
      setUpsellState({
        ...upsellValue,
        upsellEnabled: false,
        touchedInputs: { upsellDescription: isUpsellDescriptionValid ? '' : 'upsellDescription' },
        errors: {
          ...errors,
          upsellDescription: [],
        },
      })
    } else {
      setUpsellState({
        ...upsellValue,
        upsellEnabled: true,
        touchedInputs: { upsellDescription: isUpsellDescriptionValid ? '' : 'upsellDescription' },
        errors: {
          ...errors,
          upsellDescription: isUpsellDescriptionValid ? [] : ['Field is required'],
        },
      })
    }
  }

  const getErrors = (name) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  const renderPreviewImage = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <UpsellPreview
          isHovered={isPreviewHovered}
          isMessageInputFocused={!touchedInputs?.['upsellDescription']}
          messageOnClick={setMessageRef}
          isDisabled={!upsellValue.upsellEnabled}
        />
        <Badge theme='secondary' className={styles.badge}>
          Sample
        </Badge>
      </Column>
    )
  }

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns isMarginless className='h-full'>
        {renderPreviewImage()}
        <Column>
          <div className={styles.header}>
            <Text isMarginless isBold type='h5'>
              Upsell
            </Text>
            <Toggle name='upsell' isEnabled={upsellValue.upsellEnabled} setIsEnabled={handleToggleState} />
          </div>
          <Text isMarginless isSecondaryColour>
            Convert one-time donors to monthly donors by graciously asking them to upgrade their donation after they
            donate.
          </Text>
          <TextArea
            isAutoGrowing
            ref={messageRef}
            charCountMax={150}
            isDisabled={!upsellValue.upsellEnabled}
            value={upsellValue.upsellDescription}
            onChange={handleChange}
            name='upsellDescription'
            label='Upsell Message'
            onFocus={handleFocus}
            onBlur={handleBlur}
            errors={getErrors('upsellDescription')}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { UpsellCard }
