const iconForFile = (filename) => {
  if (!filename) {
    return 'spreadsheet.png'
  }

  const extension = filename.split('.').pop()

  if (!extension) {
    return 'spreadsheet.png'
  }

  if (extension === 'csv') return 'csv.png'
  if (extension === 'txt') return 'txt.png'
  if (['xls', 'xlsx'].indexOf(extension) !== false) return 'spreadsheet.png'
  if (extension === 'csv') return 'csv.png'
}

export default function CurrentFile({ file, fileInfo, change }) {
  return (
    <div className='mt-2 bg-white shadow-lg rounded-lg'>
      <div className='flex flex-col justify-center space-y-3 p-8'>
        <div className='m-auto'>
          <img
            className='m-auto'
            src={`/jpanel/assets/images/imports/${iconForFile(file?.name ?? fileInfo?.import?.file_name)}`}
            width='72px'
          />
        </div>
        <p className='font-extrabold text-lg text-black'>{file?.name ?? fileInfo?.import?.file_name}</p>

        <p className='text-lg text-gray-500'>
          {fileInfo?.sheet?.columns} Columns, {fileInfo?.sheet?.rows - fileInfo?.import?.file_has_headers} rows
        </p>

        <button
          className='text-right text-blue-400 text-sm hover:text-brand-blue'
          onClick={() => {
            change(true)
          }}
        >
          Change file
        </button>
      </div>
    </div>
  )
}
