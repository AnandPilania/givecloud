import { useState, useCallback } from 'react'
import PropTypes from 'prop-types'
import axios from 'axios'
import { useQueryClient } from 'react-query'
import ScaleLoader from 'react-spinners/ScaleLoader'
import { Button } from '@/aerosol'
import useApiUrl from '@/hooks/api/useApiUrl'

const ActiveChecklistItem = ({
  taskKey,
  title,
  description,
  action,
  actionText,
  knowledgeBase,
  isSkippable,
  isSkipped,
}) => {
  const apiUrl = useApiUrl()
  const queryClient = useQueryClient()
  const [isSetSkipLoading, setIsSetSkipLoading] = useState(false)

  const markTaskAsSkipped = useCallback(
    async (markAs) => {
      let response
      setIsSetSkipLoading(true)
      if (markAs == 'skip') {
        response = await axios.post(`${apiUrl}/quickstart/${taskKey}/skip`)
      } else {
        response = await axios.delete(`${apiUrl}/quickstart/${taskKey}/skip`)
      }
      if (response.status === 200) {
        queryClient.invalidateQueries('dashboard-checklist')
      }
      setIsSetSkipLoading(false)
    },
    [taskKey, queryClient, apiUrl]
  )

  return (
    <div className={`mb-4 pt-6 lg:pt-0 pb-4 px-4 bg-gray-100 rounded-lg lg:bg-transparent`}>
      <div className='flex items-start'>
        <div className='flex flex-col items-center justify-center'>
          <h4 className='text-xl text-center font-extrabold'>{title}</h4>
          <p className='pt-2 pb-6 text-center tracking-wide'>{description}</p>
          <div className='flex flex-col lg:flex-row items-center justify-center'>
            {!!action && (
              <Button
                className='px-6'
                onClick={() => {
                  window.location = action
                }}
              >
                {actionText}
              </Button>
            )}
            {!!knowledgeBase && (
              <Button
                className='ml-3'
                isClean
                onClick={() => {
                  window.open(knowledgeBase)
                }}
              >
                Learn More
              </Button>
            )}
          </div>
          {!!isSkippable && (
            <div className='mt-4 h-6'>
              {isSetSkipLoading && (
                <div className='mt-1'>
                  <ScaleLoader height={20} color='#CCC' loading />
                </div>
              )}
              {!isSetSkipLoading && (
                <Button isClean onClick={() => markTaskAsSkipped(isSkipped ? 'unskip' : 'skip')}>
                  <span className='text-sm text-gray-500'>{`${isSkipped ? 'Unskip' : 'Skip'} this step`}</span>
                </Button>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

ActiveChecklistItem.propTypes = {
  taskKey: PropTypes.string.isRequired,
  title: PropTypes.string.isRequired,
  description: PropTypes.string.isRequired,
  action: PropTypes.string,
  actionText: PropTypes.string,
  knowledgeBase: PropTypes.string,
  isSkippable: PropTypes.bool,
  isSkipped: PropTypes.bool,
}

export default ActiveChecklistItem
