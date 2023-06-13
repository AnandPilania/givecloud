import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'
import classNames from 'classnames'
import Container from '@/screens/Imports/components/Container'
import Stamp from '@/screens/Imports/components/Stamp'

export default function Loading({ fileInfo, title, reset, isComplete }) {
  const progress = isComplete ? 100 : fileInfo.import.progress

  return (
    <>
      <Container>
        <Stamp>
          <FontAwesomeIcon icon={faSpinner} spin={true} size='2x' />
        </Stamp>
      </Container>
      <div className='max-w-xs m-auto'>
        <div className='flex content-center w-full bg-white rounded-full p-2 shadow-lg'>
          <div
            className={classNames('h-4 bg-brand-blue rounded-full', {
              'animate-pulse': !isComplete,
            })}
            style={{ width: progress + '%' }}
          ></div>
        </div>
      </div>
      <div className='mt-4 mb-8 m-auto text-center'>
        <p>
          {title}{' '}
          {!isComplete && (
            <>
              <strong>{fileInfo.import.current_record}</strong> of {fileInfo.import.total_records} rows
            </>
          )}
          {isComplete && <strong> done!</strong>}
        </p>

        <p className='pt-8'>
          <button className='text-right text-blue-400 text-sm hover:text-brand-blue' onClick={reset}>
            Reset
          </button>
        </p>
      </div>
    </>
  )
}
