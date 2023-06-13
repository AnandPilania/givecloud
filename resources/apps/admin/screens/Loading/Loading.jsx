import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'

const Loading = () => (
  <div data-testid='loading' className='flex items-center justify-center h-full' aria-live='assertive' aria-busy='true'>
    <FontAwesomeIcon icon={faSpinner} className='text-4xl' spin />
  </div>
)

export { Loading }
