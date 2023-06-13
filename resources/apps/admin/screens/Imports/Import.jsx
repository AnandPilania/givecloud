import { useCallback } from 'react'
import { useParams } from 'react-router-dom'
import Aborted from './components/Aborted'
import Error from './Analysis/Error'
import useApiQuery from '@/hooks/useApiQuery'

import Loading from '@/screens/Imports/components/Loading'
import Done from '@/screens/Imports/Analysis/Done'
import AdvanceButton from '@/screens/Imports/AdvanceButton'

export default function Import({ fileInfo }) {
  const apiQuery = useApiQuery()
  const { id } = useParams()

  const startImport = useCallback(async () => {
    await apiQuery.post(`imports/${id}/import`)
  }, [id])

  const resetImport = useCallback(async () => {
    await apiQuery.destroy(`imports/${id}/import`)
    await startImport()
  }, [id])

  if (fileInfo.import.stage === 'aborted')
    return (
      <>
        <Aborted
          fileInfo={fileInfo}
          reset={resetImport}
          title='Import was previously aborted...'
          label='Restart import'
        />
      </>
    )

  if (fileInfo.import.stage === 'error')
    return (
      <>
        <Error fileInfo={fileInfo} setCurrentStep={setCurrentStep} />
      </>
    )

  return (
    <>
      {fileInfo.import.is_complete && (
        <Done fileInfo={fileInfo}>
          <p className='pt-2 text-xl font-extrabold'>We're all done! </p>
        </Done>
      )}

      {!fileInfo.import.is_complete && (
        <Loading fileInfo={fileInfo} reset={resetImport} title='Importing' isComplete={fileInfo.import.is_complete} />
      )}
      <AdvanceButton to='summary' title={`View summary`} isEnabled={fileInfo.import.is_complete} />
    </>
  )
}
