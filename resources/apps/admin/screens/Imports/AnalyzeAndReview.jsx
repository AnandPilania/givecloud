import { useState, useEffect, useCallback, useMemo } from 'react'
import { useParams } from 'react-router-dom'
import AdvanceButton from '@/screens/Imports/AdvanceButton'
import Loading from './components/Loading'
import Done from './Analysis/Done'
import Error from './Analysis/Error'
import useApiQuery from '@/hooks/useApiQuery'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'

export default function AnalyseAndReview({ fileInfo }) {
  const apiQuery = useApiQuery()
  const { id } = useParams()
  const [stage, setStage] = useState()

  const mappedFieldsCount = useMemo(() => {
    return fileInfo.data.filter((row) => row.mappedTo).length
  }, [fileInfo.data])

  const startAnalysis = useCallback(async () => {
    await apiQuery.post(`imports/${id}/analyse`)
    setStage('analysis_queue')
  }, [id])

  const resetAnalysis = useCallback(async () => {
    await apiQuery.destroy(`imports/${id}/analyse`)
    setStage('draft')
  }, [id, stage])

  useEffect(() => {
    setStage(fileInfo.import.stage)
  }, [fileInfo.import.stage])

  if (['aborted', 'error'].includes(stage))
    return (
      <>
        <Error fileInfo={fileInfo} resetAnalysis={resetAnalysis} />
      </>
    )
  if (['draft', ''].includes(stage))
    return (
      <>
        <Done>
          <p className='pt-2 text-xl font-extrabold'>We are good to go!</p>
          <p className='pt-8'>
            <strong>{fileInfo.import.total_records} rows</strong> will be analyzed against{' '}
            <strong>{mappedFieldsCount}</strong> mapped fields.
          </p>
        </Done>
        <button
          type='button'
          onClick={startAnalysis}
          className='mt-12 border border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white
            ml-auto mb-8 inline-flex items-center px-8 py-2 shadow-sm text-xl font-medium rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple'
        >
          Start Analysis
          <FontAwesomeIcon icon={faArrowRight} className='ml-3 -mr-1 h-5 w-5' aria-hidden='true' />
        </button>
      </>
    )

  if (['import_ready', 'done'].includes(stage))
    return (
      <>
        <Done>
          <p className='pt-2 text-xl font-extrabold'>File looks great! </p>
          <p className='pt-8'>
            <strong>{fileInfo.import.total_records} rows</strong> will be imported
          </p>
        </Done>
        <AdvanceButton to='import' title={`Import ${fileInfo.import.total_records} rows`} isEnabled={true} />
        {stage !== 'done' && (
          <p>
            <button className='text-right text-blue-400 text-sm hover:text-brand-blue' onClick={resetAnalysis}>
              Reset analysis
            </button>
          </p>
        )}
      </>
    )

  return (
    <>
      <Loading
        fileInfo={fileInfo}
        reset={resetAnalysis}
        isComplete={!!fileInfo.import.analysis_ended_at}
        title='Analyzing'
      />
      <AdvanceButton
        to='import'
        title={`Import ${fileInfo.import.total_records} rows`}
        isEnabled={!!fileInfo.import.analysis_ended_at}
      />
    </>
  )
}
