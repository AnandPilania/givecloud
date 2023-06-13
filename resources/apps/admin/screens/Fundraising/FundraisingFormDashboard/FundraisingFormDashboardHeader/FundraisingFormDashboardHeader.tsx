import type { FundraisingForm } from '@/types'
import type { FC } from 'react'
import { memo } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExternalLink } from '@fortawesome/pro-regular-svg-icons'
import {
  Button,
  Column,
  Columns,
  DropdownDivider,
  KebabDropdown,
  KebabDropdownItem,
  Text,
  Tooltip,
  triggerToast,
} from '@/aerosol'
import { DeleteFormDialog } from './DeleteFormDialog'
import { useHistory, useLocation } from 'react-router-dom'
import usePageTitle from '@/hooks/usePageTitle'
import { useRecoilValue } from 'recoil'
import { useCloneFundraisingFormMutation } from './useCloneFundraisingFormMutation'
import { useDefaultFundraisingFormMutation } from './useDefaultFundraisingFormMutation'
import { useTailwindBreakpoints } from '@/shared/hooks'
import configState from '@/atoms/config'
import styles from './FundraisingFormDashboardHeader.styles.scss'
import { ExportCSVDialog } from './ExportCVSDialog'

enum DIALOGS {
  'deleteFundraisingForm',
  'export',
}

type DialogParams = keyof typeof DIALOGS

type Props = Pick<FundraisingForm, 'id' | 'name' | 'publicUrl' | 'testmodeUrl' | 'isDefaultForm'>

interface ConfigState {
  clientUrl: string
  isDonorPerfectEnabled: boolean
  isGivecloudExpress: boolean
  isTestMode: boolean
}

const FundraisingFormDashboardHeader: FC<Partial<Props>> = memo<Partial<Props>>(
  ({ id, name, publicUrl, testmodeUrl, isDefaultForm }) => {
    const history = useHistory()
    const { pathname, search } = useLocation()
    const { large } = useTailwindBreakpoints()
    const { clientUrl, isGivecloudExpress, isTestMode } = useRecoilValue<ConfigState>(configState)
    const { mutate: cloneForm } = useCloneFundraisingFormMutation(id)
    const { mutate: setFormToDefault } = useDefaultFundraisingFormMutation()
    const isLiveGatewayConnected = !isTestMode
    const isDialogOpen = (dialog: DialogParams) => !!search.includes(dialog)
    const params = new URLSearchParams(search)

    const onClose = (param: DialogParams) => {
      if (params.has(param)) {
        params.delete(param)
        history.replace({
          search: params.toString(),
        })
      }
    }

    const handleOpenDialog = (search: DialogParams) => ({ pathname, search })

    usePageTitle(`${name} Dashboard`)

    const handleCloneForm = () => {
      if (id) {
        cloneForm(
          { id },
          {
            onSuccess: ({ id: responseID, name: repsonseName }) => {
              history.push(`/fundraising/forms/${responseID}`)
              triggerToast({ type: 'success', header: `${repsonseName} Cloned!` })
            },
            onError: () => {
              triggerToast({
                type: 'error',
                header: `Sorry, there was a problem cloning ${name}.`,
                options: { autoClose: false },
              })
            },
          }
        )
      }
    }

    const handleSetIsDefaultForm = () => {
      if (id) {
        setFormToDefault(
          { id },
          {
            onSuccess: ({ data }) => {
              triggerToast({ type: 'success', header: `${data?.data?.name} is now the default experience.` })
            },
            onError: () => {
              triggerToast({
                type: 'error',
                header: `There was a problem setting ${name} as the default experience.`,
                options: { autoClose: false },
              })
            },
          }
        )
      }
    }

    const viewTooltipContent = <Text isMarginless>You must be able to accept payments before using Live View.</Text>
    const testModeContent = (
      <Text isMarginless>
        Preview the experience you've crafted for your donors without charging a real credit card.
      </Text>
    )

    const renderViewButton = () => {
      if (large.lessThan) return null
      return (
        <Tooltip
          isHidden={isLiveGatewayConnected}
          tooltipContent={viewTooltipContent}
          placement='bottom'
          hasTabIndex={false}
        >
          <Button isDisabled={!isLiveGatewayConnected} href={publicUrl} target='_blank' className='mr-4 inline-block'>
            Live View
            <FontAwesomeIcon size='lg' icon={faExternalLink} className='ml-2' />
          </Button>
        </Tooltip>
      )
    }

    const renderViewTestButton = () => {
      if (large.lessThan) return null
      return (
        <Tooltip hasTabIndex={false} placement='bottom' tooltipContent={testModeContent}>
          <Button isOutlined href={testmodeUrl} target='_blank' className='mr-4 inline-block'>
            Test Mode
            <FontAwesomeIcon size='lg' icon={faExternalLink} className='ml-2' />
          </Button>
        </Tooltip>
      )
    }

    const renderCustomizeViewKebabItems = () => {
      const itemText = isLiveGatewayConnected
        ? 'View Experience'
        : `View Experience\n(${isGivecloudExpress ? 'Stripe' : 'Gateway'} not enabled)`

      if (large.lessThan) {
        return (
          <KebabDropdownItem href={publicUrl} target='_blank' isDisabled={!isLiveGatewayConnected}>
            {itemText}
          </KebabDropdownItem>
        )
      }
      return null
    }

    const renderTestMode = () => {
      if (large.lessThan) {
        return (
          <KebabDropdownItem href={testmodeUrl} target='_blank'>
            Test Mode
          </KebabDropdownItem>
        )
      }
      return null
    }

    const renderSetDefaultFormOption = () => {
      if (isDefaultForm) return null
      return <KebabDropdownItem onClick={handleSetIsDefaultForm}>Set as Default</KebabDropdownItem>
    }
    return (
      <Columns isMarginless isResponsive={false} isStackingOnMobile={false} className={styles.root}>
        <Column>
          <Text isMarginless isTruncated isBold type='h1' className={styles.text}>
            {name}
          </Text>
        </Column>
        <Column className={styles.buttonContainer}>
          {renderViewButton()}
          {renderViewTestButton()}
          <KebabDropdown>
            {renderCustomizeViewKebabItems()}
            {renderTestMode()}
            <KebabDropdownItem href={`${clientUrl}/jpanel/contributions?df=${id}`}>
              View Contributions
            </KebabDropdownItem>
            <KebabDropdownItem href={`${clientUrl}/jpanel/supporters?donationForms=${id}`}>
              View Supporters
            </KebabDropdownItem>
            <DropdownDivider />
            <KebabDropdownItem to={() => handleOpenDialog('export')}>Export to CSV</KebabDropdownItem>
            {renderSetDefaultFormOption()}
            <KebabDropdownItem onClick={handleCloneForm}>Clone</KebabDropdownItem>
            <KebabDropdownItem to={() => handleOpenDialog('deleteFundraisingForm')} className='text-red-700'>
              Delete
            </KebabDropdownItem>
          </KebabDropdown>
        </Column>
        <DeleteFormDialog
          isOpen={isDialogOpen('deleteFundraisingForm')}
          onClose={() => onClose('deleteFundraisingForm')}
          formId={id}
          formName={name}
          isDefaultForm={isDefaultForm}
        />
        <ExportCSVDialog id={id} isOpen={isDialogOpen('export')} onClose={() => onClose('export')} />
      </Columns>
    )
  }
)
export { FundraisingFormDashboardHeader }
