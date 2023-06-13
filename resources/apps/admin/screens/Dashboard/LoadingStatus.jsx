import ScaleLoader from 'react-spinners/ScaleLoader'

const LoadingStatus = ({ isLoading, isError, children, className = '', height = 'full', align = 'center' }) => {
  return (
    <>
      {(isLoading || isError) && (
        <div
          className={`${className} relative flex flex-col mt-4 items-${
            align == 'center' ? 'center' : 'start'
          } justify-${align == 'center' ? 'center' : 'start'} w-full h-${height}`}
        >
          {isLoading && <ScaleLoader height={20} color='#CCC' loading />}
          {isError && <div className='text-gray-400'>Error loading data</div>}
        </div>
      )}
      {!isLoading && !isError && <>{children}</>}
    </>
  )
}

export default LoadingStatus
