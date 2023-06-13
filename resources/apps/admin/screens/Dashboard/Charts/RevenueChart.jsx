import { useRecoilValue } from 'recoil'
import { Line } from 'react-chartjs-2'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip } from 'chart.js'
import moment from 'moment'
import configState from '@/atoms/config'
import { formatMoney } from '@/shared/utilities/formatMoney'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip)

const RevenueChart = ({ data }) => {
  const { currency = {} } = useRecoilValue(configState)

  if (!data) {
    return null
  }

  if (data?.message) {
    return <div className='flex items-center justify-center w-full h-full'>{data.message}</div>
  }

  return (
    <Line
      data={{
        labels: data.map((point) => point.order_date),
        datasets: [
          {
            label: 'Recurring',
            backgroundColor: '#0066FF',
            fill: {
              target: 'origin',
              above: '#0066FF',
            },
            data: data.map((point) => point.recurring),
          },
          {
            label: 'One Time',
            backgroundColor: '#42CFFC',
            fill: {
              target: 'origin',
              above: '#42CFFC',
            },
            data: data.map((point) => point.one_time),
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
            stacked: true,
            offset: true,
            grid: {
              drawOnChartArea: false,
            },
            ticks: {
              autoSkip: true,
              autoSkipPadding: 75,
              maxRotation: 0,
              padding: 4,
              color: '#999999',
              callback: function (_, index) {
                return moment(data[index].order_date).format('MMM DD')
              },
            },
          },
          y: {
            stacked: true,
            grid: {
              drawBorder: false,
              drawTicks: false,
            },
            beginAtZero: true,
            ticks: {
              maxTicksLimit: 5,
              padding: 8,
              color: '#999999',
              callback: function (value) {
                return formatMoney({ amount: value, currency: currency.code })
              },
            },
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                let label = context.dataset.label || ''
                if (label) {
                  label += ': '
                }
                if (context.parsed.y !== null) {
                  label += formatMoney({ amount: context.parsed.y, currency: currency.code })
                }
                return label
              },
            },
          },
        },
      }}
    />
  )
}

export default RevenueChart
