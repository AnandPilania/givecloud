import { Link } from 'react-router-dom'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'

export default function AdvanceButton({ to, isEnabled, title }) {
  return (
    <Link
      to={to}
      disabled={!isEnabled}
      className={classNames(
        {
          'bg-brand-blue hover:bg-brand-purple ': isEnabled,
          'bg-blue-300': !isEnabled,
        },
        'ml-auto mb-8 inline-flex items-center px-8 py-2 border border-transparent shadow-sm text-xl font-medium rounded-full text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple'
      )}
    >
      {title}
      <FontAwesomeIcon icon={faArrowRight} className='ml-3 -mr-1 h-5 w-5' aria-hidden='true' />
    </Link>
  )
}
