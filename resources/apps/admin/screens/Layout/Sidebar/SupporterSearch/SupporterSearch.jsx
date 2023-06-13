import { useState } from 'react'
import { useRecoilState } from 'recoil'
import { Combobox } from '@headlessui/react'
import queryState from './atoms/queryState'
import useSupporterSearchQuery from './hooks/useSupporterSearchQuery'
import FloatingContainer from '@/screens/Layout/Sidebar/SupporterSearch/components/FloatingContainer'

const SupporterSearch = () => {
  const [query, setQuery] = useRecoilState(queryState)
  const [openEmptyState, setOpenEmptyState] = useState(false)
  const [redirecting, setRedirecting] = useState(false)
  const [queryResults] = useSupporterSearchQuery()
  const { results } = queryResults || {}

  return (
    <Combobox
      onChange={(result) => {
        setRedirecting(true)
        window.location = result.url
      }}
    >
      {({ open }) => (
        <div className='relative mt-8'>
          <Combobox.Input
            className='peer h-9 w-full bg-white bg-opacity-15 rounded-sm border-0 pl-10 pr-4 text-white
            font-medium
            active:bg-white active:text-gray-900
            focus:bg-white focus:text-gray-900 focus:outline-none focus:shadow-outline-blue focus:ring-0 sm:text-md
            placeholder:text-sm placeholder:text-white placeholder:opacity-80 placeholder:font-medium'
            placeholder='Find Supporter'
            autoComplete='off'
            onKeyUp={(event) => {
              if (redirecting || event.key !== 'Enter' || !results) {
                return
              }

              if (results?.length === 1) {
                return (window.location = results[0].url)
              }
              return (window.location = `/jpanel/supporters?fB=${query}`)
            }}
            onFocus={(event) => {
              !event.target.value && setOpenEmptyState(true)
            }}
            onBlur={() => {
              setOpenEmptyState(false)
            }}
            onChange={(event) => setQuery(event.target.value)}
          />
          <div className='absolute inset-y-0 left-0 pl-3 flex items-center text-white pointer-events-none peer-active:text-gray-900 peer-focus:text-gray-900'>
            <svg
              xmlns='http://www.w3.org/2000/svg'
              className='h-4 w-4 '
              fill='none'
              viewBox='0 0 24 24'
              stroke='currentColor'
              strokeWidth={2}
            >
              <path strokeLinecap='round' strokeLinejoin='round' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
            </svg>
          </div>
          {(open || openEmptyState) && <FloatingContainer />}
        </div>
      )}
    </Combobox>
  )
}

export default SupporterSearch
