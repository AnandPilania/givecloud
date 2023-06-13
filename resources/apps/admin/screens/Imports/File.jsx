import useApiUrl from '@/hooks/api/useApiUrl'
import { useEffect, useState } from 'react'
import axios from 'axios'
import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { Switch } from '@headlessui/react'
import FileSelector from '@/screens/Imports/FileUpload/FileSelector'
import CurrentFile from '@/screens/Imports/FileUpload/CurrentFile'
import AdvanceButton from '@/screens/Imports/AdvanceButton'

export default function File({ fileInfo, isLoading, saveState }) {
  const apiUrl = useApiUrl()
  const { id } = useParams()

  const [needsFile, setNeedsFile] = useState(false)
  const [isUploading, setIsUploading] = useState(false)
  const [isError, setIsError] = useState(false)
  const [message, setMessage] = useState()
  const [newFile, setNewFile] = useState()
  const [fileHasHeaders, setFileHasHeaders] = useState(fileInfo.import.file_has_headers)

  useEffect(() => {
    if (!fileInfo.sheet?.rows?.length) setNeedsFile(true)
    if (fileInfo.sheet?.rows?.length) setFileHasHeaders(fileInfo.import.file_has_headers)
  }, [fileInfo.sheet, fileInfo.import.file_has_headers])

  const handleFile = async (e) => {
    setIsUploading(true)
    setIsError(false)
    setNeedsFile(false)
    setMessage()

    const formData = new FormData()
    formData.append('file', e.target.files[0], e.target.files[0].name)
    setNewFile(e.target.files[0])
    await axios
      .post(apiUrl + `/imports/${id}/file`, formData)
      .then(() => {})
      .catch((error) => {
        setIsError(true)
        setNeedsFile(true)
        setMessage(error.response?.data?.message || error.response?.data?.error || error.message)
      })
      .finally(() => {
        setIsUploading(false)
      })
  }

  return (
    <>
      <p className='mt-2 text-gray-600'>{`Hint: You can always refresh your file here and won't loose any mapping information.`}</p>

      {needsFile && !isUploading && (
        <FileSelector handleFile={handleFile} fileInfo={fileInfo} setWantsFile={setNeedsFile} />
      )}

      {!needsFile && <CurrentFile file={newFile} fileInfo={fileInfo} isLoading={isUploading} change={setNeedsFile} />}

      {isError && <p className='mt-2 text-sm text-red-500'>{message}</p>}

      <div className='mt-6'>
        <Switch.Group as='div' className='flex items-center mb-12'>
          <Switch
            checked={!!fileHasHeaders}
            onChange={(value) => {
              setFileHasHeaders(value)
              saveState({ file_has_headers: value })
            }}
            className={classNames(
              fileHasHeaders ? 'bg-brand-blue' : 'bg-gray-200',
              'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
            )}
          >
            <span
              aria-hidden='true'
              className={classNames(
                fileHasHeaders ? 'translate-x-5' : 'translate-x-0',
                'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
              )}
            />
          </Switch>
          <Switch.Label as='span' className='ml-3'>
            <span className='text-xl font-bold text-gray-900'>The first row contains column headings.</span>
          </Switch.Label>
        </Switch.Group>

        <AdvanceButton to='mapping' title='Map fields' isEnabled={fileInfo?.import && !needsFile} />
      </div>
    </>
  )
}
