export default function Warning(props) {
  return (
    <div className='rounded-md p-2 bg-warning-light mb-2 only:mb-0'>
      <div className='flex items-center'>
        <div className='flex-shrink-0'>
          <svg
            xmlns='http://www.w3.org/2000/svg'
            className='h-8 w-8 text-yellow-800'
            fill='none'
            viewBox='0 0 24 24'
            stroke='currentColor'
            strokeWidth={2}
          >
            <path
              strokeLinecap='round'
              strokeLinejoin='round'
              d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
            />
          </svg>
        </div>
        <div className='ml-3'>
          <p className='font-semibold text-sm text-yellow-800'>{props.children}</p>
        </div>
      </div>
    </div>
  )
}
