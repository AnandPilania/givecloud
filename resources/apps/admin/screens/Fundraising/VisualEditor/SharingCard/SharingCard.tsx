import type { SyntheticEvent } from 'react'
import type { ImageData } from '@/aerosol/ImagePicker/ImagePicker'
import { useState } from 'react'
import { Column, Columns, Box, Badge, ImagePicker, Input, Text, TextArea } from '@/aerosol'
import { SharingPreview } from '@/screens/Fundraising/LivePreview/SharingPreview'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints, useFocus } from '@/shared/hooks'
import styles from './SharingCard.styles.scss'

const SharingCard = () => {
  const { sharingValue, setSharingState } = useFundraisingFormState()
  const { medium } = useTailwindBreakpoints()
  const [focusFields, setFocusFields] = useState({})
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [descriptionRef, setDescriptionRef] = useFocus<HTMLTextAreaElement>()
  const [titleRef, setTitleRef] = useFocus<HTMLInputElement>()
  const { errors, touchedInputs } = sharingValue

  const handleChange = (e: SyntheticEvent) => {
    const { name, value } = e.target as HTMLInputElement

    if (!value) {
      setSharingState({
        ...sharingValue,
        [name]: '',
        errors: {
          ...errors,
          [name]: ['Field is required'],
        },
      })
    } else {
      setSharingState({
        ...sharingValue,
        [name]: value,
        errors: {
          ...errors,
          [name]: [],
        },
      })
    }
  }

  const handleImageUpload = ({ id, url }: ImageData) =>
    setSharingState({
      ...sharingValue,
      socialPreviewImage: {
        id,
        full: url,
      },
    })

  const handleRemoveImage = () => {
    setSharingState({
      ...sharingValue,
      socialPreviewImage: {
        id: '',
        full: '',
      },
    })
  }

  const handleBlur = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement

    setFocusFields((prevState) => ({ ...prevState, [name]: '' }))

    setSharingState({
      ...sharingValue,
      touchedInputs: {
        ...touchedInputs,
        [name]: name,
      },
    })
  }

  const handleFocus = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement

    setFocusFields((prevState) => ({ ...prevState, [name]: name }))
  }

  const renderPreview = () => {
    if (medium.lessThan) return null
    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
      >
        <SharingPreview
          isHovered={isPreviewHovered}
          isTitleFocused={!!focusFields['socialLinkTitle']}
          titleOnClick={setTitleRef}
          isDescriptionFocused={!!focusFields['socialLinkDescription']}
          descriptionOnClick={setDescriptionRef}
        />
        <Badge theme='secondary' className={styles.badge}>
          Sample
        </Badge>
      </Column>
    )
  }

  const getErrors = (name: string) => (!!touchedInputs?.[name] ? errors?.[name] : [])

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns className='h-full'>
        {renderPreview()}
        <Column>
          <Text isBold type='h5'>
            Sharing
          </Text>
          <Text isSecondaryColour className='mb-6'>
            Customize how your fundraising experience looks when it's shared.
          </Text>
          <Input
            ref={titleRef}
            charCountMax={50}
            name='socialLinkTitle'
            label='Link Title'
            value={sharingValue.socialLinkTitle}
            onChange={handleChange}
            onBlur={handleBlur}
            onFocus={handleFocus}
            errors={getErrors('socialLinkTitle')}
          />
          <TextArea
            ref={descriptionRef}
            isAutoGrowing
            charCountMax={150}
            name='socialLinkDescription'
            label='Link Description'
            value={sharingValue.socialLinkDescription}
            onChange={handleChange}
            onBlur={handleBlur}
            onFocus={handleFocus}
            errors={getErrors('socialLinkDescription')}
            className='mb-10'
          />
          <ImagePicker
            name='socialPreviewImage'
            label='Link Preview Image'
            id='preview-image'
            image={sharingValue.socialPreviewImage.full}
            removeImage={handleRemoveImage}
            onChange={handleImageUpload}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { SharingCard }
