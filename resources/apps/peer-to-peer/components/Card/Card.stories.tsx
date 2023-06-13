import type { Meta, Story } from '@storybook/react'
import { useEffect } from 'react'
import { setRootThemeColour } from '@/shared/utilities/setRootThemeColour'
import { faUsers } from '@fortawesome/pro-light-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { Button, Column, Columns, Container } from '@/aerosol'
import { Text } from '../Text'
import { Card } from './Card'

export default {
  title: 'Peer to Peer/Card',
  component: Card,
  argTypes: {
    colour: { control: 'color' },
  },
} as Meta<typeof Card>

interface CustomColour {
  colour: string
}

export const Default: Story<typeof Card & CustomColour> = ({ colour }) => {
  useEffect(() => setRootThemeColour({ colour }), [colour])

  return (
    <Container containerWidth='extraSmall'>
      <Columns>
        <Card>
          <Columns isResponsive={false} isStackingOnMobile={false} isMarginless>
            <Column>
              <Text isMarginless type='h4' theme='custom'>
                Team Fundraiser
              </Text>
              <Text theme='custom'>
                Team-up with friends, family and co-workers to raise money towards a collective goal.
              </Text>
            </Column>
            <Column columnWidth='small'>
              <FontAwesomeIcon icon={faUsers} />
            </Column>
          </Columns>
          <Column columnWidth='six'>
            <Button isFullWidth theme='custom'>
              Team Fundraiser
            </Button>
          </Column>
        </Card>
      </Columns>
    </Container>
  )
}
