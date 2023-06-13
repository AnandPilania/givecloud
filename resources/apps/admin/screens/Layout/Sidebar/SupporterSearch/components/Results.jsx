import { Combobox } from '@headlessui/react'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import Item from '@/screens/Layout/Sidebar/SupporterSearch/components/Item'
import Warning from '@/screens/Layout/Sidebar/SupporterSearch/components/Warning'
import queryState from '@/screens/Layout/Sidebar/SupporterSearch/atoms/queryState'
import useSupporterSearchQuery from '@/screens/Layout/Sidebar/SupporterSearch/hooks/useSupporterSearchQuery'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import styles from '@/screens/Layout/Sidebar/SupporterSearch/SupporterSearch.scss'
import { faArrowUp } from '@fortawesome/pro-regular-svg-icons'

export default function Results() {
  const query = useRecoilValue(queryState)
  const [queryResults] = useSupporterSearchQuery()
  const { results, supporters } = queryResults || {}

  return (
    <>
      {query && results?.length > 0 && (
        <Combobox.Options static className={classnames('list-none p-0', styles.root)}>
          {!supporters && (
            <>
              <Warning>
                <span className='font-bold'>No Supporters found.</span> Here are some contributions
              </Warning>
            </>
          )}

          {results?.map((result) => (
            <Combobox.Option key={result.id} value={result}>
              {({ active }) => <Item result={result} active={active} />}
            </Combobox.Option>
          ))}

          {supporters && (
            <div className='text-center'>
              <a href={`/jpanel/supporters?fB=${query}`} className='font-semibold text-brand-blue underline text-xs'>
                View All Results
              </a>
            </div>
          )}
        </Combobox.Options>
      )}

      {query && results?.length === 0 && <Warning>No Supporters or Contributions found</Warning>}

      {!query && (
        <div className='text-center p-2 '>
          <FontAwesomeIcon icon={faArrowUp} />
          <p className='font-medium text-base'> Try searching by name, email, phone or address.</p>
        </div>
      )}
    </>
  )
}
