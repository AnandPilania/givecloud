import type { ComponentMeta, ComponentStory } from '@storybook/react'
import { useState } from 'react'
import { Button } from '@/aerosol/Button'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { Dialog } from './Dialog'
import { DialogHeader } from './DialogHeader'
import { DialogContent } from './DialogContent'
import { DialogFooter } from './DialogFooter'
import { Text } from '@/aerosol/Text'

export default {
  title: 'Aerosol/Dialog',
  component: Dialog,
  args: {
    size: 'medium',
  },
  argTypes: {
    options: ['small', 'medium', 'large'],
    control: { type: 'radio' },
  },
  parameters: {
    docs: {
      description: {
        component: `A Dialog is a centred container that houses a title, description (optional), and actions.`,
      },
    },
  },
} as ComponentMeta<typeof Dialog>

export const Default: ComponentStory<typeof Dialog> = ({ size }) => {
  const [isDialogOpen, setIsDialogOpen] = useState(false)

  const handleCloseDialog = () => {
    setIsDialogOpen(false)
  }

  return (
    <>
      <Button onClick={() => setIsDialogOpen(true)}>Trigger Dialog</Button>
      <Dialog size={size} isOpen={isDialogOpen} onClose={handleCloseDialog}>
        <DialogHeader onClose={handleCloseDialog}>
          <Text isMarginless type='h2' className='text-left'>
            Hasta la vista, baby
          </Text>
        </DialogHeader>
        <DialogContent>
          <Columns isResponsive={false}>
            <Column>
              <Text>lala</Text>
            </Column>
          </Columns>
        </DialogContent>
        <DialogFooter>
          <Columns>
            <Column>
              <Button isOutlined onClick={handleCloseDialog}>
                No thanks :(
              </Button>
            </Column>
            <Column>
              <Button onClick={handleCloseDialog}>Sounds good :)</Button>
            </Column>
          </Columns>
        </DialogFooter>
      </Dialog>
    </>
  )
}
