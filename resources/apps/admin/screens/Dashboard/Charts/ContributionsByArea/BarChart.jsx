import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip } from 'chart.js'
import { Bar } from 'react-chartjs-2'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip)

const BarChart = ({ data = [] }) => {
  const chartData = {
    labels: data.map((point) => point.label),
    datasets: [
      {
        backgroundColor: '#6C87F0',
        data: data.map((point) => point.value),
      },
    ],
  }

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  }

  return (
    <div className='pb-8 h-full'>
      <Bar data={chartData} options={options} />
    </div>
  )
}

export default BarChart
