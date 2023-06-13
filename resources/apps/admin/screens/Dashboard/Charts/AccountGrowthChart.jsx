import { Line } from 'react-chartjs-2'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip)

const AccountGrowthChart = ({ data }) => {
  if (!data) {
    return null
  }

  if (data?.message) {
    return <div className='flex items-center justify-center w-full h-full'>{data.message}</div>
  }

  return (
    <Line
      data={{
        labels: data.map((point) => point.created_at),
        datasets: [
          {
            label: 'Account Growth',
            backgroundColor: '#FC58AF',
            fill: {
              target: 'origin',
              above: '#FC58AF',
            },
            data: data.map((point) => point.growth),
          },
        ],
      }}
      options={{
        elements: {
          point: {
            radius: 0,
            hitRadius: 10,
            borderWidth: 0,
            borderColor: 'rgba(255,255,255,0.5)',
          },
        },
        interaction: {
          mode: 'index',
        },
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

export default AccountGrowthChart
