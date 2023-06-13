import BarChart from './BarChart'
import LoadingStatus from '../../LoadingStatus'
import useFetcherQuery from '@/hooks/useFetcherQuery'

const DrilldownChart = ({ countryCode }) => {
  const { data, isLoading, isError } = useFetcherQuery(
    ['dashboard-contributions-by-region', countryCode],
    `dashboard/${countryCode}/contributions-by-region`
  )

  const chartData = data?.data?.regionData

  return (
    <LoadingStatus isLoading={isLoading} hasError={isError}>
      <BarChart data={chartData} />
    </LoadingStatus>
  )
}

export default DrilldownChart
