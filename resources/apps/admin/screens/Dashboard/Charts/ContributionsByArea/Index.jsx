import { useState, useEffect, useMemo } from 'react'

import LoadingStatus from '@/screens/Dashboard/LoadingStatus'
import DrilldownChart from '@/screens/Dashboard/Charts/ContributionsByArea/DrilldownChart'
import BarChart from '@/screens/Dashboard/Charts/ContributionsByArea/BarChart'
import useFetcherQuery from '@/hooks/useFetcherQuery'

const ContributionsByAreaChart = () => {
  const [chosenCountry, setChosenCountry] = useState(null)
  const { data, isLoading, isError } = useFetcherQuery(
    'dashboard-contributions-by-country',
    `dashboard/contributions-by-country`
  )
  const chartData = useMemo(() => data?.data?.countryData, [data])

  useEffect(() => {
    if (chartData?.length === 1) {
      setChosenCountry(chartData[0].code)
    }
  }, [chartData, setChosenCountry])

  const handleViewChange = (e) => {
    setChosenCountry(e.target.value)
  }

  return (
    <LoadingStatus isLoading={isLoading} hasError={isError}>
      {chartData?.length > 1 && (
        <select className='max-w-sm my-3 p-2 bg-gray-100 rounded-md' onChange={handleViewChange}>
          <option value=''>All Countries</option>
          {chartData.map((country) => (
            <option key={country.code} value={country.code}>
              {country.label}
            </option>
          ))}
        </select>
      )}
      {/* Show Country stats if there's more than one country */}
      {!chosenCountry && <BarChart data={chartData} />}
      {/*
        Otherwise show the region data from one country if there's
        only one, or if they've specifically chosen one
      */}
      {chosenCountry && <DrilldownChart countryCode={chosenCountry} />}
    </LoadingStatus>
  )
}

export default ContributionsByAreaChart
