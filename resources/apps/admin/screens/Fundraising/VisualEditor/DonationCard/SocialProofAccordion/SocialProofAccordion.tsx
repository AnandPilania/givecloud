import type { ComponentPropsWithRef, FC } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import { forwardRef } from 'react'
import {
  Column,
  Columns,
  Toggle,
  Accordion,
  AccordionContent,
  AccordionHeader,
  RadioButton,
  RadioGroup,
  Text,
} from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './SocialProofAccordion.styles.scss'

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const SocialProofAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { socialProofValue, setSocialProofState } = useFundraisingFormState()

  const toggleEnabledState = () =>
    setSocialProofState({ ...socialProofValue, socialProofEnabled: !socialProofValue.socialProofEnabled })

  const handleChange = (socialProofPrivacy: string) => setSocialProofState({ ...socialProofValue, socialProofPrivacy })

  const getRef = (reference: string) => (reference === socialProofValue.socialProofPrivacy ? ref : null)

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root}>
      <AccordionHeader>
        <div className={styles.header}>
          <Text isMarginless type='h5' isBold>
            Social Proof
          </Text>
          <Toggle
            isEnabled={socialProofValue.socialProofEnabled}
            setIsEnabled={toggleEnabledState}
            name='social proof'
          />
        </div>
      </AccordionHeader>
      <AccordionContent>
        <Text isSecondaryColour className='mb-6'>
          Motivate your donors to complete their transaction by surfacing how others are giving.
        </Text>
        <RadioGroup
          name='socialProof'
          label='Social proof options'
          isLabelVisible={false}
          checkedValue={socialProofValue.socialProofPrivacy}
          onChange={handleChange}
          isDisabled={!socialProofValue.socialProofEnabled}
        >
          <Columns isMarginless isWrapping>
            <Column isPaddingless columnWidth='six'>
              <RadioButton
                ref={getRef('initials-and-geography')}
                id='social-proof-option-1'
                label='Initials & Geography'
                description='Example: JB (Ottawa,ON) gave $150'
                value='initials-and-geography'
              />
            </Column>
            <Column isPaddingless columnWidth='six'>
              <RadioButton
                ref={getRef('geography-only')}
                id='social-proof-option-2'
                label='Geography Only'
                description='Example: Someone (Ottawa,ON) gave $250'
                value='geography-only'
              />
            </Column>
          </Columns>
        </RadioGroup>
      </AccordionContent>
    </Accordion>
  )
})

SocialProofAccordion.displayName = 'SocialProofAccordion'

export { SocialProofAccordion }
