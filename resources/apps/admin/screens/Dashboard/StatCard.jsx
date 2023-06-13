import LoadingStatus from './LoadingStatus'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowUp, faArrowDown, faCircleInfo } from '@fortawesome/pro-regular-svg-icons'
import { Tooltip } from '@/aerosol'

const StatCard = (props) => {
  const { label, tooltip, value, diff, isIncreasing, isLoading, isError } = props

  const increasing = () => {
    if (!diff) return <p className='text-sm text-gray-500'>No data</p>
    if (diff === '0') return <p className='text-sm text-gray-500'>No change</p>

    return (
      <p className='flex items-baseline text-sm font-semibold text-green-600'>
        <FontAwesomeIcon icon={faArrowUp} className='mr-1 h-4 w-4 flex-shrink-0 self-center text-green-600' />
        <span className='sr-only'> Increased by </span>
        {Math.abs(diff)}%
      </p>
    )
  }

  const decreasing = () => {
    if (!diff) return <p className='text-sm text-gray-500'>No data</p>
    if (diff === '0') return <p className='text-sm text-gray-500'>No change</p>

    return (
      <p className='flex items-baseline text-sm font-semibold text-red-500'>
        <FontAwesomeIcon icon={faArrowDown} className='mr-1 h-4 w-4 flex-shrink-0 self-center text-red-500' />
        <span className='sr-only'> Decreased by </span>
        {Math.abs(diff)}%
      </p>
    )
  }

  return (
    <div className='bg-white shadow rounded-lg px-6 py-4'>
      <div className='flex gap-2 justify-between'>
        <div className='font-medium text-gray-500 grow'>{label}</div>
        <Tooltip tooltipContent={tooltip}>
          <FontAwesomeIcon icon={faCircleInfo} className='h-4 w-4 flex-shrink-0 self-center text-brand-blue' />
        </Tooltip>
      </div>
      <LoadingStatus isLoading={isLoading} isError={isError} align='start'>
        <div className='flex items-baseline justify-between'>
          <h4 className='font-extrabold text-3xl mt-4 mb-0'>{value}</h4>
          {isIncreasing && increasing()}
          {!isIncreasing && decreasing()}
        </div>
      </LoadingStatus>
    </div>
  )
}

export default StatCard
