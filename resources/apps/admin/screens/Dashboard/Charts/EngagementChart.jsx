import { Doughnut } from 'react-chartjs-2'
import { Chart as ChartJS, ArcElement } from 'chart.js'

ChartJS.register(ArcElement)

const EngagementChart = ({ data }) => {
  if (!data) {
    return null
  }

  if (data?.message) {
    return <div className='flex items-center justify-center w-full h-full'>{data.message}</div>
  }

  return (
    <Doughnut
      data={{
        labels: data?.map((point) => point.label),
        datasets: [
          {
            label: 'Engagement',
            backgroundColor: data?.map((point) => point.color),
            data: data?.map((point) => point.value),
          },
        ],
      }}
      options={{
        responsive: true,
        maintainAspectRatio: false,
      }}
    />
  )
}

export default EngagementChart
