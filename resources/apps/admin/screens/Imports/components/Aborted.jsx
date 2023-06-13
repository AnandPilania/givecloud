import Container from '@/screens/Imports/components/Container'
import Stamp from '@/screens/Imports/components/Stamp'
import { ExclamationIcon, RefreshIcon } from '@heroicons/react/outline'

export default function Aborted({ reset, title, label }) {
  return (
    <>
      <Container>
        <Stamp bgColor='bg-yellow-500'>
          <ExclamationIcon className='text-white h-10 w-10' />
        </Stamp>
        <p className='pt-4 text-2xl font-bold'>{title}</p>
        <p className='pt-1 text-lg'>You can restart the job</p>
        <button
          type='button'
          onClick={reset}
          className='mt-12 border border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white
            ml-auto mb-8 inline-flex items-center px-8 py-2 shadow-sm text-xl font-medium rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-purple'
        >
          <RefreshIcon className='mr-3 -ml-1 h-5 w-5' />
          {label}
        </button>
      </Container>
    </>
  )
}
