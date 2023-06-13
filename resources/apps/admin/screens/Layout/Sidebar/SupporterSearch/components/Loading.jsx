import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/pro-regular-svg-icons'

export default function Loading() {
  return (
    <div className='flex items-center justify-center py-6'>
      <FontAwesomeIcon icon={faSpinner} className='text-2xl' spin={true} />
    </div>
  )
}
