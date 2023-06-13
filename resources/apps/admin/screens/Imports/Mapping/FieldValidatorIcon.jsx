import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck, faExclamationCircle, faChevronDown, faSpinner } from '@fortawesome/pro-regular-svg-icons'

const FieldValidatorIcon = ({ loading, isValid, hasState }) => {
  if (loading) {
    return <FontAwesomeIcon spin={true} icon={faSpinner} className='h-5 w-5 text-gray-400' />
  }

  if (!hasState) {
    return <FontAwesomeIcon icon={faChevronDown} className='h-5 w-5 text-gray-400' />
  }

  if (isValid) {
    return <FontAwesomeIcon icon={faCheck} className='h-5 w-5 text-green-500' />
  }

  return <FontAwesomeIcon icon={faExclamationCircle} className='h-5 w-5 text-red-500' />
}

export default FieldValidatorIcon
