import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import Emoji from 'react-emoji-render'

import configState from '@/atoms/config'
import useFetcherQuery from '@/hooks/useFetcherQuery'
import LoadingStatus from '../LoadingStatus'
import Confetti from './Confetti'
import TaskList from './TaskList'

const StartChecklist = () => {
  const { data, isLoading, isError } = useFetcherQuery('dashboard-checklist', 'dashboard/checklist')
  const { shouldShowExpandedChecklist } = useRecoilValue(configState)
  const forceExpandedQuickStart = new URLSearchParams(window.location.search).get('quickstart') === '1'
  const [showExpandedChecklist, setShowExpandedChecklist] = useState(
    forceExpandedQuickStart || shouldShowExpandedChecklist
  )

  return (
    <section className='max-w-7xl mx-auto flex'>
      <div
        className={`transition-all ${
          !showExpandedChecklist ? 'h-16' : ''
        } relative order-1 flex-grow overflow-hidden bg-white mb-8 shadow rounded-lg flex flex-col`}
      >
        <div
          className='absolute top-0 left-0 right-0 -mt-24 h-56 -skew-y-6'
          style={{ background: 'linear-gradient(156.26deg, #2f80ed 36.19%, #56ccf2 100.98%' }}
        >
          <Confetti />
        </div>
        {!showExpandedChecklist && (
          <div
            onClick={() => setShowExpandedChecklist(true)}
            className='absolute z-10 top-0 right-0 bg-white py-2 px-3 rounded-lg mt-4 mr-3 text-sm hover:bg-gray-200 cursor-pointer'
          >
            View
          </div>
        )}
        <div className='px-6 pt-6 pb-3 relative'>
          <div className='text-white uppercase text-sm font-extrabold'>Quickstart Guide</div>
          {showExpandedChecklist && (
            <>
              <h2 className='pt-4 text-white text-4xl font-extrabold'>
                <span>Let&apos;s Go!</span>
                <Emoji className='ml-2 -mt-1' text='🚀' />
              </h2>
              <h4 className='text-base text-white font-semibold mb-4'>Get ready to be the office hero.</h4>
            </>
          )}
        </div>
        {showExpandedChecklist && (
          <div className='relative'>
            <LoadingStatus isLoading={isLoading} isError={isError} align='center' height='24'>
              {!!data?.data?.tasks && <TaskList tasks={data?.data?.tasks} />}
            </LoadingStatus>
          </div>
        )}
      </div>
    </section>
  )
}

export default StartChecklist
