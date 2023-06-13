import { ComponentMeta } from '@storybook/react'
import { Alert } from './Alert'
import { Button } from '@/aerosol/Button'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { Text } from '@/aerosol/Text'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { ERROR, INFO, WARNING, SUCCESS } from '@/shared/constants/theme'
const themes = [ERROR, INFO, WARNING, SUCCESS] as const

export default {
  title: 'Aerosol/Alert',
  component: Alert,
} as ComponentMeta<typeof Alert>

export const Default = () => {
  return themes.map((theme) => (
    <Alert iconPosition='center' type={theme} key={theme}>
      <Columns isMarginless>
        <Column columnWidth='six'>
          <Text isMarginless>
            Our top performing nonprofits populate all these fields to help build trust and increase conversions.
          </Text>
        </Column>
      </Columns>
    </Alert>
  ))
}

export const withCTA = () => {
  const { medium } = useTailwindBreakpoints()

  return themes.map((theme) => (
    <Alert iconPosition='center' type={theme} key={theme} className='mb-4'>
      <Columns isMarginless isResponsive={false} isStackingOnMobile={medium.lessThan} className='w-full items-center'>
        <Column>
          <Text isMarginless>
            Our top performing nonprofits populate all these fields to help build trust and increase conversions.
          </Text>
        </Column>
        <Column columnWidth='small'>
          <Button theme={theme} size='small' onClick={() => console.log('Ouch!')}>
            Clickity click
          </Button>
        </Column>
      </Columns>
    </Alert>
  ))
}

export const Outlined = () => {
  return themes.map((theme) => (
    <Alert isOutlined iconPosition='center' type={theme} key={theme}>
      <Columns isMarginless>
        <Column columnWidth='six'>
          <Text isMarginless>
            Our top performing nonprofits populate all these fields to help build trust and increase conversions.
          </Text>
        </Column>
      </Columns>
    </Alert>
  ))
}

export const WithoutIcon = () => {
  return themes.map((theme) => (
    <Alert isIconVisible={false} type={theme} key={theme}>
      <Columns isMarginless>
        <Column columnWidth='six'>
          <Text isMarginless>
            Our top performing nonprofits populate all these fields to help build trust and increase conversions.
          </Text>
        </Column>
      </Columns>
    </Alert>
  ))
}
