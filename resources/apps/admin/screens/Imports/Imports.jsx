import { useState, useCallback } from 'react'
import { useEffectOnce } from 'react-use'
import { Route, useRouteMatch, Switch, useParams, useHistory, useLocation } from 'react-router-dom'
import { until } from '@open-draft/until'
import Pusher from 'pusher-js'
import configState from '@/atoms/config'
import { useRecoilValue } from 'recoil'
import usePageTitle from '@/hooks/usePageTitle'
import useApiQuery from '@/hooks/useApiQuery'
import FieldMapping from './Mapping/FieldMapping'
import AnalyzeAndReview from './AnalyzeAndReview'
import Steps from '@/screens/Imports/Steps'
import File from '@/screens/Imports/File'
import Import from '@/screens/Imports/Import'
import Summary from '@/screens/Imports/Summary'
import LoadingState from '@/screens/Imports/components/LoadingState'

const steps = [
  { name: 'File Upload', num: 1, link: 'file' },
  { name: 'Map fields', num: 2, link: 'mapping' },
  { name: 'Analyze & Review', num: 3, link: 'analyze' },
  { name: 'Import', num: 4, link: 'import' },
  { name: 'Summary', num: 5, link: 'summary' },
]

const mappedFieldsCount = (data = []) => {
  return data.filter((row) => row.mappedTo).length
}

const Imports = () => {
  usePageTitle('Imports')

  const { id } = useParams()
  const apiQuery = useApiQuery()
  const history = useHistory()
  const { pathname } = useLocation()

  const { accountName, pusherConfig } = useRecoilValue(configState)

  const [fileInfo, setFileInfo] = useState(null)
  const [loadingFileInfo, setLoadingFileInfo] = useState(true)

  const { path, url } = useRouteMatch()

  useEffectOnce(() => {
    const pusher = new Pusher(pusherConfig.key, pusherConfig)
    const channel = pusher.subscribe(`${accountName}.imports.${id}`)
    channel.bind('import.updated', (data) => {
      setFileInfo(data)
    })
    getFileInfo()
  })

  const getFileInfo = useCallback(async () => {
    const { data } = await until(() => apiQuery.get(`imports/${id}`))
    setFileInfo(data.data)
    setLoadingFileInfo(false)
    if (pathname === `/imports/wizard/${id}`) {
      if (['import_ready', 'analysis_queue', 'analyzing'].includes(data.data.import.stage)) {
        history.push(`${url}/analyze`)
      } else if (['import_queue', 'importing'].includes(data.data.import.stage)) {
        history.push(`${url}/import`)
      } else if (data.data.import.stage === 'done') {
        history.push(`${url}/summary`)
      } else if (mappedFieldsCount(data.data.data) > 0) {
        history.push(`${url}/analyze`)
      } else if (data.data.sheet.name) {
        history.push(`${url}/mapping`)
      } else {
        history.push(`${url}/file`)
      }
    }
  }, [apiQuery, history, id, pathname, url])

  const saveState = useCallback(
    (payload) => {
      apiQuery.post(`imports/${id}`, payload)
    },
    [apiQuery, id]
  )

  return (
    <div id='mainContent' className='py-0 px-5 md:px-7'>
      <div className='max-w-3xl m-auto text-center'>
        <Steps steps={steps} disableAll={fileInfo?.import?.stage === 'done'} />
        <h1 className='uppercase text-brand-blue text-base font-bold mb-1'>Import</h1>
        <LoadingState isLoading={loadingFileInfo}>
          <Switch>
            <Route path={`${path}/file`} exact={true}>
              <div className='text-4xl font-black'>File upload</div>
              <File fileInfo={fileInfo} saveState={saveState} />
            </Route>

            <Route path={`${path}/mapping`} exact={true}>
              <FieldMapping fileInfo={fileInfo} />
            </Route>

            <Route path={`${path}/analyze`} exact={true}>
              <AnalyzeAndReview fileInfo={fileInfo} />
            </Route>
            <Route path={`${path}/import`} exact={true}>
              <Import fileInfo={fileInfo} />
            </Route>
            <Route path={`${path}/summary`} exact={true}>
              <Summary fileInfo={fileInfo} />
            </Route>
          </Switch>
        </LoadingState>
      </div>
    </div>
  )
}

export default Imports
