export default function FileSelector({ handleFile, fileInfo, setWantsFile }) {
  return (
    <div className='mt-2 px-6 py-10 border-2 border-gray-300 border-dashed rounded-md'>
      <div className='text-center'>
        <svg
          xmlns='http://www.w3.org/2000/svg'
          className='mx-auto h-14 w-14 text-gray-300'
          fill='none'
          viewBox='0 0 24 24'
          stroke='currentColor'
        >
          <path
            strokeLinecap='round'
            strokeLinejoin='round'
            strokeWidth={2}
            d='M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12'
          />
        </svg>
        <div className='flex justify-center text-sm mt-4 text-gray-600'>
          <label
            htmlFor='file-upload'
            className='relative cursor-pointer text-lg underline rounded-md font-bold text-brand-blue hover:text-brand-teal'
          >
            <span>Choose a file</span>
            <input id='file-upload' name='file-upload' onChange={handleFile} type='file' className='sr-only' />
          </label>
        </div>
        <p className='text-xs text-gray-500'>CSV, XLSX, up to 20MB</p>
      </div>
      {fileInfo.import.file_name && (
        <button
          className='mt-4 text-right text-blue-400 text-sm hover:text-brand-blue'
          onClick={() => {
            setWantsFile(false)
          }}
        >
          or continue with {fileInfo?.import.file_name}
        </button>
      )}
    </div>
  )
}
