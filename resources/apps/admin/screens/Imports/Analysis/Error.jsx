import { useMemo, useState } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTimes, faCloudUpload } from '@fortawesome/pro-regular-svg-icons'
import Container from '@/screens/Imports/components/Container'
import Stamp from '@/screens/Imports/components/Stamp'
import { Link } from 'react-router-dom'
import Divider from '@/screens/Imports/components/Divider'

const filteredErrors = (logs) => {
  return logs.split('\n').filter((line) => {
    return line.includes('ERROR')
  })
}

export default function Error({ fileInfo, resetAnalysis }) {
  const [showErrors, setShowErrors] = useState(false)

  const errors = useMemo(() => {
    return filteredErrors(fileInfo.import.analysis_messages)
  }, [fileInfo.import.analysis_messages])

  return (
    <>
      <Container>
        <Stamp bgColor='bg-red-500'>
          <FontAwesomeIcon icon={faTimes} className='text-white' size='2x' />
        </Stamp>
        <p className='pt-4 text-2xl font-bold'>File does not look so great... </p>
        <p className='pt-1 text-lg'>
          <button
            className='text-brand-blue underline font-bold'
            onClick={() => {
              setShowErrors(!showErrors)
            }}
          >
            Most of your file
          </button>{' '}
          has errors
        </p>

        <div>
          <button className='mt-8 text-right text-blue-400 text-sm hover:text-brand-blue' onClick={resetAnalysis}>
            Reset analysis
          </button>
        </div>

        {showErrors && (
          <ul className='mt-2 list-none text-left'>
            {errors.map((line, index) => {
              return <li key={index}>{line}</li>
            })}
          </ul>
        )}

        <Link
          to={`file`}
          className='mt-12 border border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white
            ml-auto mb-8 inline-flex items-center px-8 py-2 shadow-sm text-xl font-medium rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple'
        >
          <FontAwesomeIcon icon={faCloudUpload} className='mr-3 -ml-1 h-5 w-5' />
          Upload a corrected file
        </Link>

        <Divider />

        <button
          className='mt-8 border border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white
            ml-auto mb-8 inline-flex items-center px-8 py-2 shadow-sm text-xl font-medium rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple'
          onClick={() => {
            alert('TODO, introduce new state.')
          }}
        >
          <FontAwesomeIcon icon={faTimes} className='mr-3 -ml-1 h-5 w-5' />
          Cancel Import
        </button>
      </Container>
    </>
  )
}
