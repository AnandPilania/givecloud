import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSquareList } from '@fortawesome/pro-light-svg-icons'
import { Button, Column, Columns, Container, Text } from '@/aerosol'
import { DeletedFundraisingForm } from './DeletedFundraisingForm'
import { SkeletonDeletedFundraisingForms } from './SkeletonDeletedFundraisingForms'
import { useDeletedFundraisingFormsQuery } from './useDeletedFundraisingFormsQuery'
import usePageTitle from '@/hooks/usePageTitle'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './DeletedFundraisingForms.scss'

const DeletedFundraisingForms = () => {
  usePageTitle('Recently Deleted')
  const { large } = useTailwindBreakpoints()
  const { data, isLoading } = useDeletedFundraisingFormsQuery()

  if (isLoading) return <SkeletonDeletedFundraisingForms />

  const renderEmptyState = () => (
    <div className={styles.emptyState}>
      <FontAwesomeIcon aria-hidden='true' icon={faSquareList} className='text-gray-400 mb-4' size='4x' />
      <Text type='h4' isMarginless isBold className='text-center'>
        You haven't deleted anything yet.
      </Text>
      <Text isSecondaryColour type='h5'>
        Delete fundraising experiences and come back to undo the action.
      </Text>
      <Button to='/fundraising/forms' className='mt-4' isFullWidth={large.lessThan}>
        Go to fundraising experiences
      </Button>
    </div>
  )

  const renderDeletedForms = () => data?.map((form) => <DeletedFundraisingForm key={form?.id} form={form} />)

  const renderContent = () => (data?.length ? renderDeletedForms() : renderEmptyState())

  return (
    <Container>
      <Columns isMarginPreserved>
        <Column>
          <Text isMarginless isBold type='h1'>
            Deleted Fundraising Experiences
          </Text>
        </Column>
      </Columns>
      {renderContent()}
    </Container>
  )
}

export { DeletedFundraisingForms }
