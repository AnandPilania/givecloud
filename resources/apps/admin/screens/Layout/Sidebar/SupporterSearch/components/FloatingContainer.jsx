import Loading from '@/screens/Layout/Sidebar/SupporterSearch/components/Loading'
import Results from '@/screens/Layout/Sidebar/SupporterSearch/components/Results'
import useSupporterSearchQuery from '@/screens/Layout/Sidebar/SupporterSearch/hooks/useSupporterSearchQuery'

export default function FloatingContainer() {
  const [, isLoading] = useSupporterSearchQuery()

  return (
    <div className='absolute w-full mt-2 overflow-hidden rounded-md shadow-lg z-30'>
      <div className='bg-white p-2 overflow-x-hidden max-h-112'>{isLoading ? <Loading /> : <Results />}</div>
    </div>
  )
}
