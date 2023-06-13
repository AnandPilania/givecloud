import type { FC } from 'react'
import { Suspense } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheckCircle } from '@fortawesome/free-solid-svg-icons'
import { faCalendar } from '@fortawesome/pro-light-svg-icons'
import { useParams, useLocation, useHistory } from 'react-router-dom'
import { Column, Columns, Container, Text, triggerToast } from '@/aerosol'
import usePageTitle from '@/hooks/usePageTitle'
import { useFundraisingFormWithStatsQuery } from './useFundraisingFormWithStatsQuery'
import { EmbedDialog } from './EmbedDialog'
import { EmbedPanel } from './EmbedPanel'
import { FormPreviewPanel } from './FormPreviewPanel'
import { FundraisingFormDashboardHeader } from './FundraisingFormDashboardHeader'
import { IntegrationsDialog } from './IntegrationsDialog'
import { IntegrationsPanel } from './IntegrationsPanel'
import { QRCodePanel } from './QRCodePanel'
import { SetupAlert } from './SetupAlert'
import { ShareShortUrlPanel } from './ShareShorturlPanel'
import { UpdateFundraisingForm } from './UpdateFundraisingForm'
import { TotalRevenuePanel } from './TotalRevenuePanel'
import { TotalViewsPanel } from './TotalViewsPanel'
import { TotalDonorsPanel } from './TotalDonorsPanel'
import { TotalConversionsPanel } from './TotalConversionsPanel'
import { VisualEditorLoader } from '@/screens/Fundraising/VisualEditor'
import { SkeletonFundraisingFormDashboard } from './SkeletonFundraisingFormDashboard'
import formatDate from '@/utilities/formatDate'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './FundraisingFormDashboard.styles.scss'

const renderDate = (createdAt?: string) => formatDate(createdAt, { year: 'numeric', month: 'short', day: 'numeric' })

enum DIALOGS {
  'embedFundraisingForm',
  'integrations',
}

type DialogParams = keyof typeof DIALOGS

interface IDParam {
  id: string
}

const getUpdateFormParams = () =>
  new URLSearchParams({
    form: 'updateFundraisingForm',
    tab: '0',
    screen: '0',
  }).toString()

const FundraisingFormDashboard: FC = () => {
  const history = useHistory()
  const { id } = useParams<IDParam>()
  const { search, pathname } = useLocation()
  const isDialogOpen = (dialog: DialogParams) => !!search.includes(dialog)
  const isEditorOpen = search.includes('updateFundraisingForm')
  const { data } = useFundraisingFormWithStatsQuery(id)
  const { medium } = useTailwindBreakpoints()

  const { revenueAmount, donorCount, conversion, views, trends } = data?.stats ?? {}

  usePageTitle(`${data?.name} Dashboard`)

  const onClose = (param: DialogParams) => {
    const params = new URLSearchParams(search)

    if (params.has(param)) {
      params.delete(param)
      history.replace({
        search: params.toString(),
      })
    }
  }

  const handleShareShortUrl = () => {
    if (data?.shortlinkUrl) navigator.clipboard.writeText(data.shortlinkUrl)
    triggerToast({ type: 'success', header: 'Short URL copied to clipboard!' })
  }

  const renderIsDefaultForm = () => {
    if (data?.isDefaultForm) {
      return (
        <Column className={styles.defaultColumn}>
          <FontAwesomeIcon icon={faCheckCircle} className={styles.defaultCheckIcon} />
          <Text isSecondaryColour isMarginless>
            Default Experience
          </Text>
        </Column>
      )
    }
    return null
  }

  const renderExtraColumn = () => (medium.greaterThan ? <Column columnWidth='four' /> : null)

  if (isEditorOpen) {
    return (
      <Suspense fallback={<VisualEditorLoader />}>
        <UpdateFundraisingForm isOpen={isEditorOpen} />
      </Suspense>
    )
  }

  return (
    <Suspense fallback={<SkeletonFundraisingFormDashboard />}>
      <Container containerWidth='extraSmall' className={styles.root}>
        <FundraisingFormDashboardHeader
          id={id}
          isDefaultForm={data?.isDefaultForm}
          name={data?.name}
          publicUrl={data?.publicUrl}
          testmodeUrl={data?.testmodeUrl}
        />
        <Columns isMarginless>
          {renderIsDefaultForm()}
          <Column className={styles.createdColumn}>
            <Text isSecondaryColour isMarginless>
              <FontAwesomeIcon icon={faCalendar} className='mr-2' />
              Created on {renderDate(data?.createdAt)} by {data?.createdBy}
            </Text>
          </Column>
        </Columns>
        <SetupAlert />
        <Columns isMarginless>
          <Column columnWidth='four'>
            <FormPreviewPanel
              previewImageUrl={data?.previewImageUrl}
              to={{ pathname, search: getUpdateFormParams() }}
            />
          </Column>
          <Column columnWidth='four' className='justify-between'>
            <ShareShortUrlPanel onClick={handleShareShortUrl} />
            <EmbedPanel to={{ pathname, search: 'embedFundraisingForm' }} />
            <QRCodePanel href={data?.qrCode} target='_blank' />
            <IntegrationsPanel to={{ pathname, search: 'integrations' }} />
          </Column>
        </Columns>
        <Columns isMarginless isWrappingReverse>
          <Column columnWidth='four'>
            <TotalRevenuePanel revenue={revenueAmount} trends={trends?.revenues} />
          </Column>
          <Column columnWidth='four'>
            <TotalViewsPanel views={views} trends={trends?.views} />
          </Column>
        </Columns>
        <Columns isMarginless isWrappingReverse>
          <Column isPaddingless columnWidth='four' className='md:flex-row'>
            <Column>
              <TotalDonorsPanel donors={donorCount} trend={trends?.donors?.trend} />
            </Column>
            <Column>
              <TotalConversionsPanel conversion={conversion} trend={trends?.conversions?.trend} />
            </Column>
          </Column>
          {renderExtraColumn()}
        </Columns>
      </Container>
      <EmbedDialog
        isOpen={isDialogOpen('embedFundraisingForm')}
        onClose={() => onClose('embedFundraisingForm')}
        formId={id}
      />
      <IntegrationsDialog
        id={id}
        isOpen={isDialogOpen('integrations')}
        onClose={() => onClose('integrations')}
        dpEnabled={data?.dpEnabled}
        dpGlCode={data?.dpGlCode}
        dpSolicitCode={data?.dpSolicitCode}
        dpSubSolicitCode={data?.dpSubSolicitCode}
        dpCampaign={data?.dpCampaign}
        gtmContainerId={data?.gtmContainerId}
        metaPixelId={data?.metaPixelId}
        dpMeta9={data?.dpMeta9}
        dpMeta10={data?.dpMeta10}
        dpMeta11={data?.dpMeta11}
        dpMeta12={data?.dpMeta12}
        dpMeta13={data?.dpMeta13}
        dpMeta14={data?.dpMeta14}
        dpMeta15={data?.dpMeta15}
        dpMeta16={data?.dpMeta16}
        dpMeta17={data?.dpMeta17}
        dpMeta18={data?.dpMeta18}
        dpMeta19={data?.dpMeta19}
        dpMeta20={data?.dpMeta20}
        dpMeta21={data?.dpMeta21}
        dpMeta22={data?.dpMeta21}
      />
    </Suspense>
  )
}

export { FundraisingFormDashboard }
