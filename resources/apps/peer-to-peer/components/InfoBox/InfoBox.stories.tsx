import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { faCheck, faShieldCheck } from '@fortawesome/pro-regular-svg-icons'
import { Text } from '../Text'
import { InfoBox } from './InfoBox'

export default {
  title: 'Peer to Peer / InfoBox',
  component: InfoBox,
} as ComponentMeta<typeof InfoBox>

export const Default: ComponentStory<typeof InfoBox> = () => {
  return (
    <>
      <InfoBox icon={faCheck}>
        <Text type='footnote' isMarginless>
          100% Tax Deductible in Canada through our charity number 999083023 R00001.
        </Text>
      </InfoBox>
      <InfoBox icon={faShieldCheck}>
        <Text type='footnote' isMarginless>
          Transparency Promise ensures 100% of your donation is used as promised.
        </Text>
      </InfoBox>
    </>
  )
}
