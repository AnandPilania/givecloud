import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js'
import { Bar } from 'react-chartjs-2'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip)

const BestPerformingChart = ({ data }) => {
  if (!data) {
    return null
  }

  if (data?.message) {
    return <div className='flex items-center justify-center w-full h-full'>{data.message}</div>
  }

  return (
    <Bar
      data={{
        labels: data.map((point) => point.name),
        datasets: [
          {
            label: 'Best Sellers',
            backgroundColor: '#42CFFC',
            data: data.map((point) => point.sales_count),
          },
        ],
      }}
      options={{
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            display: false,
          },
          y: {
            display: false,
          },
        },
      }}
    />
  )
}

export default BestPerformingChart
