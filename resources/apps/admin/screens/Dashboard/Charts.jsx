import { RiFundsLine, RiKey2Line, RiTrophyLine, RiUserHeartLine, RiGlobeLine } from 'react-icons/ri'

import StatCard from '@/screens/Dashboard/StatCard'
import GraphCard from '@/screens/Dashboard/GraphCard'
import RevenueChart from '@/screens/Dashboard/Charts/RevenueChart'
import EngagementChart from '@/screens/Dashboard/Charts/EngagementChart'
import BestSellersChart from '@/screens/Dashboard/Charts/BestPerformingChart'
import ContributionsByAreaChart from '@/screens/Dashboard/Charts/ContributionsByArea/Index'
import AccountGrowthChart from '@/screens/Dashboard/Charts/AccountGrowthChart'
import LoadingStatus from '@/screens/Dashboard/LoadingStatus'
import useFetcherQuery from '@/hooks/useFetcherQuery'
import { MonthsDropdown } from '@/screens/Dashboard/MonthsDropdown'
import { useState, useEffect } from 'react'

const getData = (data, key) => {
  return data?.data ? data.data[key] : ''
}

const Charts = () => {
  const [selectedMonth, setSelectedMonth] = useState()
  const [statsData, setStatsData] = useState()
  const [statDataIsLoading, setStatDataIsLoading] = useState(true)
  const [statDataHasError, setStatDataHasError] = useState(false)
  const { data, isLoading, isError } = useFetcherQuery(
    ['dashboard-stats', selectedMonth],
    'dashboard/stats',
    {},
    { month: selectedMonth }
  )

  useEffect(() => {
    setStatsData(data)
    setStatDataIsLoading(isLoading)
    setStatDataHasError(isError)
  }, [selectedMonth, data, isLoading, isError])

  const {
    data: chartsData,
    isLoading: chartDataIsLoading,
    isError: chartDataHasError,
  } = useFetcherQuery('dashboard-charts', 'dashboard/charts')

  const renderMonthsDropdown = () => {
    return (
      <div className='flex items-center text-gray-600 font-sans font-semibold mb-3'>
        <div className='mr-1'>{`Here's how`}</div>
        <MonthsDropdown onChange={setSelectedMonth} />
        <div>
          is going<span className='text-sm text-gray-400 '>: compared to the same time in the previous month</span>
        </div>
      </div>
    )
  }

  return (
    <>
      <section className='max-w-7xl mx-auto'>
        {renderMonthsDropdown()}
        <div className='grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6'>
          <StatCard
            label={`Total Amount`}
            tooltip='This is the total amount of all contributions and all recurring transactions.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.totals?.period}
            diff={statsData?.data?.totals?.diff}
            isIncreasing={statsData?.data?.totals?.increasing}
          />
          <StatCard
            label='One-Time'
            tooltip='The total amount of all contributions.  This includes any initial contributions made when signing up for monthly giving.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.one_time?.period?.formatted}
            diff={statsData?.data?.one_time?.diff}
            isIncreasing={statsData?.data?.one_time?.increasing}
          />
          <StatCard
            label='Recurring'
            tooltip='The total amount of automated recurring payments that have been processed.  This helps give you a sense of the health of your monthly giving.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.recurring?.period?.formatted}
            diff={statsData?.data?.recurring?.diff}
            isIncreasing={statsData?.data?.recurring?.increasing}
          />
          <StatCard
            label='New Supporters'
            tooltip='The total number of supporters who made their first contribution.  This is helpful in gauging how many new supporters you are attracting each month.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.supporters?.period?.formatted}
            diff={statsData?.data?.supporters?.diff}
            isIncreasing={statsData?.data?.supporters?.increasing}
          />
          <StatCard
            label='Contributions'
            tooltip='The total number of contributions.  This includes any initial contributions made when signing up for monthly giving.  This is useful when comparing to your previous period - are you collecting more donations?'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.contributions?.period?.formatted}
            diff={statsData?.data?.contributions?.diff}
            isIncreasing={statsData?.data?.contributions?.increasing}
          />
          <StatCard
            label='Average Size'
            tooltip='The average size of all contributions, not including any recurring transactions.  This is helpful in understanding your donor behaviour.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.order_amount?.period?.formatted}
            diff={statsData?.data?.order_amount?.diff}
            isIncreasing={statsData?.data?.order_amount?.increasing}
          />
          <StatCard
            label='Average Daily Amount'
            tooltip='The average total amount you receive daily.  This is helpful when gauging how fast you are fundraising - your daily fundraising velocity.'
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.daily_revenue?.period?.formatted}
            diff={statsData?.data?.daily_revenue?.diff}
            isIncreasing={statsData?.data?.daily_revenue?.increasing}
          />
          <StatCard
            label='DCC Coverage'
            tooltip={`The total DCC you’ve collected (${statsData?.data?.dcc?.period?.formatted}) as compared to the total amount you’ve collected (${statsData?.data?.totals?.period}).  This is helpful in gauging the effectiveness of DCC across all your contributions, even when donors declined DCC.`}
            isLoading={statDataIsLoading}
            isError={statDataHasError}
            value={statsData?.data?.coverage?.period?.formatted + `%`}
            diff={statsData?.data?.coverage?.diff}
            isIncreasing={statsData?.data?.coverage?.increasing}
          />
        </div>
      </section>
      <section className='max-w-7xl mx-auto pt-8'>
        <GraphCard icon={RiFundsLine} accentColor={'#0066FF'} label='Contributions: Last 60 Days' contentHeight='230px'>
          <LoadingStatus isLoading={chartDataIsLoading} isError={chartDataHasError}>
            <RevenueChart data={getData(chartsData, 'revenue_chart')} />
          </LoadingStatus>
        </GraphCard>
      </section>
      <section className='max-w-7xl mx-auto pt-8'>
        <div className='grid grid-cols-1 lg:grid-cols-3 gap-6'>
          <GraphCard icon={RiKey2Line} accentColor={'#6C87F0'} label="Today's Engagement" contentHeight='230px'>
            <LoadingStatus isLoading={chartDataIsLoading} isError={chartDataHasError}>
              <EngagementChart data={getData(chartsData, 'engagement_chart_data')} />
            </LoadingStatus>
          </GraphCard>
          <GraphCard
            icon={RiTrophyLine}
            accentColor={'#42CFFC'}
            label='Best Performing: 12 months'
            contentHeight='230px'
          >
            <LoadingStatus isLoading={chartDataIsLoading} isError={chartDataHasError}>
              <BestSellersChart data={getData(chartsData, 'best_seller_chart_data')} />
            </LoadingStatus>
          </GraphCard>
          <GraphCard
            icon={RiUserHeartLine}
            accentColor={'#FC58AF'}
            label='Supporter Growth 30 Day'
            contentHeight='230px'
          >
            <LoadingStatus isLoading={chartDataIsLoading} isError={chartDataHasError}>
              <AccountGrowthChart data={getData(chartsData, 'account_growth_chart_data_30day')} />
            </LoadingStatus>
          </GraphCard>
        </div>
      </section>
      <section className='max-w-7xl mx-auto pt-8 mb-36'>
        <GraphCard
          icon={RiGlobeLine}
          accentColor={'#42CFFC'}
          label='Contributions by Geography Last 12 Months'
          contentHeight='350px'
        >
          <ContributionsByAreaChart />
        </GraphCard>
      </section>
    </>
  )
}

export default Charts
