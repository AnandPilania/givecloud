import type { FC } from 'react'
import { Column, Columns, Button, Dialog, DialogHeader, Text } from '@/aerosol'
import { CSV_KEYS, useCSVQueries } from './useCSVQueries'

interface Props {
  isOpen: boolean
  onClose: () => void
  id?: string
}

const triggerDownload = (name: string, blob: Blob) => {
  const a = document.createElement('a')
  a.download = name
  a.href = window.URL.createObjectURL(blob)

  a.dispatchEvent(
    new MouseEvent('click', {
      view: window,
      bubbles: true,
      cancelable: true,
    })
  )
  a.remove()
}

const ExportCSVDialog: FC<Props> = ({ isOpen, onClose, id }) => {
  const onSuccess = (name: CSV_KEYS, data: BlobPart) => triggerDownload(name, new Blob([data], { type: 'text/csv' }))

  const [
    { isLoading: isContributionsLoading, refetch: refetchContributions },
    { isLoading: isSupportersLoading, refetch: refetchSupporters },
    { isLoading: isPerformanceLoading, refetch: refetchPerformance },
  ] = useCSVQueries({ id, onSuccess })

  const onClick = (refetch: () => void) => refetch()

  return (
    <Dialog size='small' isOpen={isOpen} onClose={onClose}>
      <DialogHeader onClose={onClose}>
        <Text isMarginless type='h3'>
          Export to CSV
        </Text>
      </DialogHeader>
      <Columns isResponsive={false} isStackingOnMobile={false} className='flex-col'>
        <Column columnWidth='six'>
          <Button isLoading={isContributionsLoading} onClick={() => onClick(refetchContributions)}>
            Download Contributions
          </Button>
        </Column>
        <Column columnWidth='six'>
          <Button isLoading={isSupportersLoading} onClick={() => onClick(refetchSupporters)}>
            Download Supporters
          </Button>
        </Column>
        <Column columnWidth='six'>
          <Button isLoading={isPerformanceLoading} onClick={() => onClick(refetchPerformance)}>
            Download 90-Day Performance
          </Button>
        </Column>
      </Columns>
    </Dialog>
  )
}

export { ExportCSVDialog }
