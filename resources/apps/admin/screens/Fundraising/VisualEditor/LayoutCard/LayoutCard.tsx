import type { FC, SyntheticEvent } from 'react'
import type { ImageData } from '@/aerosol/ImagePicker/ImagePicker'
import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import {
  Column,
  Columns,
  Box,
  ImagePicker,
  Input,
  Text,
  TextArea,
  RadioGroup,
  RadioButton,
  RadioTile,
  Tabs,
  TabsNav,
  TabsNavItem,
  TabsPanel,
  TabsPanels,
} from '@/aerosol'
import { SimplifiedSVG, StandardDesktopSVG, StandardMobileSVG } from './svgs'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useFocus } from '@/shared/hooks'
import config from '@/atoms/config'
import styles from './LayoutCard.styles.scss'

interface Config {
  isFundraisingFormsStandardLayoutEnabled: boolean
}

const LayoutCard: FC = () => {
  const { isFundraisingFormsStandardLayoutEnabled: isStandardLayoutEnabled } = useRecoilValue<Config>(config)
  const [isPreviewHovered, setIsPreviewHovered] = useState(false)
  const [focusFields, setFocusFields] = useState({})
  const [headlineRef, setHeadlineRef] = useFocus<HTMLInputElement>()
  const [descriptionRef, setDescriptionRef] = useFocus<HTMLTextAreaElement>()
  const { layoutValue, setLayoutState } = useFundraisingFormState()
  const { medium } = useTailwindBreakpoints()
  const isSimplifiedLayout = layoutValue.layout === 'simplified'

  const { errors, touchedInputs } = layoutValue

  const handleImageUpload = ({ id, url }: ImageData) => {
    setLayoutState({
      ...layoutValue,
      backgroundImage: {
        id,
        full: url,
      },
    })
  }

  const handleRemoveImage = () => {
    setLayoutState({
      ...layoutValue,
      backgroundImage: {
        id: '',
        full: '',
      },
    })
  }

  const handleLayoutChange = (layout: string) => setLayoutState({ ...layoutValue, layout })

  const handleChange = ({ target }: SyntheticEvent) => {
    const { name, value } = target as HTMLInputElement

    if (!value) {
      setLayoutState({
        ...layoutValue,
        [name]: '',
        errors: { ...errors, [name]: ['Field is required'] },
      })
    } else {
      setLayoutState({
        ...layoutValue,
        [name]: value,
        errors: { ...errors, [name]: [] },
      })
    }
  }

  const handleBlur = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement

    if (!!focusFields?.[name]) setFocusFields({})

    setLayoutState({
      ...layoutValue,
      touchedInputs: { ...touchedInputs, [name]: name },
    })
  }

  const handleFocus = ({ target }: SyntheticEvent) => {
    const { name } = target as HTMLInputElement
    setFocusFields((prev) => ({ ...prev, [name]: name }))
  }

  const getErrors = (name: string) => (!!touchedInputs?.[name] ? errors?.[name] : null)

  const renderPreview = () => {
    if (medium.lessThan) return null

    const renderSVG = () =>
      isSimplifiedLayout ? (
        <SimplifiedSVG />
      ) : (
        <Tabs>
          <div className='rounded-full p-2 bg-white'>
            <TabsNav>
              <TabsNavItem>Desktop</TabsNavItem>
              <TabsNavItem>Mobile</TabsNavItem>
            </TabsNav>
          </div>
          <TabsPanels>
            <TabsPanel key={1}>
              <StandardDesktopSVG
                isPreviewHovered={isPreviewHovered}
                isHeadlineFocused={!!focusFields['landingPageHeadline']}
                isDescriptionFocused={!!focusFields['landingPageDescription']}
                headlineOnClick={setHeadlineRef}
                descriptionOnClick={setDescriptionRef}
              />
            </TabsPanel>
            <TabsPanel key={2}>
              <StandardMobileSVG
                isPreviewHovered={isPreviewHovered}
                isHeadlineFocused={!!focusFields['landingPageHeadline']}
                isDescriptionFocused={!!focusFields['landingPageDescription']}
                headlineOnClick={setHeadlineRef}
                descriptionOnClick={setDescriptionRef}
              />
            </TabsPanel>
          </TabsPanels>
        </Tabs>
      )

    return (
      <Column
        columnWidth='four'
        className={styles.background}
        onMouseEnter={() => setIsPreviewHovered(true)}
        onMouseLeave={() => setIsPreviewHovered(false)}
        aria-hidden='true'
      >
        {renderSVG()}
      </Column>
    )
  }

  const renderStandardBtnText = () =>
    isStandardLayoutEnabled ? 'Our high-performing donation layout.' : 'A new layout is coming very soon.'

  const renderInputs = () =>
    isStandardLayoutEnabled ? (
      <>
        <Input
          ref={headlineRef}
          charCountMax={50}
          label='Headline'
          value={layoutValue.landingPageHeadline}
          onChange={handleChange}
          name='landingPageHeadline'
          onFocus={handleFocus}
          onBlur={handleBlur}
          errors={getErrors('landingPageHeadline')}
          isDisabled={isSimplifiedLayout}
        />
        <TextArea
          ref={descriptionRef}
          charCountMax={250}
          label='Description'
          value={layoutValue.landingPageDescription}
          onChange={handleChange}
          name='landingPageDescription'
          onBlur={handleBlur}
          onFocus={handleFocus}
          errors={getErrors('landingPageDescription')}
          isDisabled={isSimplifiedLayout}
          rows={2}
        />
      </>
    ) : null

  return (
    <Box isReducedPadding={medium.lessThan} className={styles.root} isMarginless>
      <Columns className='h-full'>
        {renderPreview()}
        <Column>
          <Text isBold type='h5'>
            Layout
          </Text>
          <Text isSecondaryColour>Customize how your fundraising experience looks when it's viewed.</Text>
          <RadioGroup
            showInput={false}
            isLabelVisible={false}
            checkedValue={layoutValue.layout}
            label='layouts'
            name='layout'
            onChange={handleLayoutChange}
            className='my-4'
          >
            <Columns isWrapping isMarginless>
              <Column isPaddingless>
                <RadioButton value='standard' disabled={!isStandardLayoutEnabled} id='standard' className='lg:pr-4'>
                  <RadioTile>
                    <Text isBold isSecondaryColour={!isStandardLayoutEnabled}>
                      Standard
                    </Text>
                    <Text type='footnote' isMarginless isSecondaryColour>
                      {renderStandardBtnText()}
                    </Text>
                  </RadioTile>
                </RadioButton>
              </Column>
              <Column isPaddingless>
                <RadioButton value='simplified' id='simplified'>
                  <RadioTile>
                    <Text isBold>Simplified</Text>
                    <Text type='footnote' isMarginless isSecondaryColour>
                      Reduced UI elements for the simplest layout.
                    </Text>
                  </RadioTile>
                </RadioButton>
              </Column>
            </Columns>
          </RadioGroup>
          {renderInputs()}
          <ImagePicker
            isMarginless={false}
            label='Feature Image'
            id='background-image'
            image={layoutValue.backgroundImage.full}
            removeImage={handleRemoveImage}
            onChange={handleImageUpload}
          />
        </Column>
      </Columns>
    </Box>
  )
}

export { LayoutCard }
