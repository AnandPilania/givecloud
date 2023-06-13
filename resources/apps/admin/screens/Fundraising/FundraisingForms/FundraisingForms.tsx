import type { FC } from 'react'
import type { FundraisingForms as FundraisingFormsType } from './useFundraisingFormsQuery'
import { Suspense } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faAdd } from '@fortawesome/pro-regular-svg-icons'
import { useLocation } from 'react-router-dom'
import { Columns, Column, Button, Container, KebabDropdown, KebabDropdownItem, Text } from '@/aerosol'
import { FundraisingForm } from '@/screens/Fundraising/FundraisingForms/FundraisingForm'
import { CreateFundraisingForm } from './CreateFundraisingForm'
import { SkeletonFundraisingForms } from './SkeletonFundraisingForms'
import { VisualEditorLoader } from '@/screens/Fundraising/VisualEditor'
import usePageTitle from '@/hooks/usePageTitle'
import { useFundraisingFormsQuery } from './useFundraisingFormsQuery'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './FundraisingForms.styles.scss'

const sortByDefault = (forms?: FundraisingFormsType) =>
  forms?.sort((a, b) => Number(b.isDefaultForm) - Number(a.isDefaultForm))

const getCreateFormParams = () =>
  new URLSearchParams({
    form: 'createFundraisingForm',
    tab: '0',
    screen: '0',
  }).toString()

const FundraisingForms: FC = () => {
  const { large } = useTailwindBreakpoints()

  usePageTitle('Fundraising')
  const { pathname, search } = useLocation()
  const isEditorOpen = search.includes('createFundraisingForm')
  const { data, isLoading } = useFundraisingFormsQuery()

  if (isLoading && !isEditorOpen) {
    return <SkeletonFundraisingForms />
  }

  const renderNewFormKebabItem = () =>
    large.lessThan ? (
      <KebabDropdownItem to={{ pathname, search: getCreateFormParams() }}>New Experience</KebabDropdownItem>
    ) : null

  const renderNewFormButton = () =>
    !large.lessThan ? (
      <Button to={{ pathname, search: getCreateFormParams() }} size='medium' className='mr-4'>
        New Experience
        <FontAwesomeIcon icon={faAdd} className='ml-2' />
      </Button>
    ) : null

  const renderForms = () => sortByDefault(data)?.map((form) => <FundraisingForm key={form?.id} form={form} />)

  const renderCreateForm = () =>
    isEditorOpen ? (
      <Suspense fallback={<VisualEditorLoader />}>
        <CreateFundraisingForm isOpen={isEditorOpen} />
      </Suspense>
    ) : null

  const staticContent = (
    <Columns isMarginPreserved isResponsive={false} isStackingOnMobile={false}>
      <Column className={styles.header}>
        <Text isMarginless isBold type='h1'>
          Fundraising
        </Text>
      </Column>
      <Column className={styles.buttonContainer}>
        {renderNewFormButton()}
        <KebabDropdown>
          {renderNewFormKebabItem()}
          <KebabDropdownItem to='/fundraising/forms/deleted-forms'>View Recently Deleted</KebabDropdownItem>
        </KebabDropdown>
      </Column>
    </Columns>
  )

  return (
    <Container staticContent={staticContent} isScrollable>
      {renderForms()}
      {renderCreateForm()}
    </Container>
  )
}

export { FundraisingForms }
