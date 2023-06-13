import { Line } from 'react-chartjs-2'
import { useEffect, useState, useRef } from 'react'
import PropTypes from 'prop-types'

const chartOptions = {
  animation: false,
  responsive: true,
  plugins: {
    legend: {
      display: false,
    },
  },
  elements: {
    line: {
      borderColor: '#6D6DBB',
      borderWidth: 2,
    },
    point: {
      radius: 0,
    },
  },
  scales: {
    y: { display: false },
    x: { display: false },
  },
}

const SparkLine = ({ data }) => {
  const chartRef = useRef(null)

  const [chartData, setChartData] = useState({
    labels: Object.keys(data).slice(-30),
    datasets: [
      {
        cubicInterpolationMode: 'monotone',
        data: Object.values(data).slice(-30),
      },
    ],
  })

  useEffect(() => {
    const chart = chartRef?.current

    if (!chart) {
      return
    }

    const backgroundColor = chart.ctx.createLinearGradient(0, 0, 0, chart.chartArea.height)

    backgroundColor.addColorStop(0, '#6D6DBB')
    backgroundColor.addColorStop(1, '#fff')

    setChartData({
      labels: Object.keys(data).slice(-30),
      datasets: [
        {
          fill: true,
          backgroundColor,
          cubicInterpolationMode: 'monotone',
          data: Object.values(data).slice(-30),
        },
      ],
    })
  }, [data])

  return <Line ref={chartRef} data={chartData} options={chartOptions} />
}

SparkLine.propTypes = {
  data: PropTypes.object,
}

SparkLine.defaultProps = {
  data: {},
}

export { SparkLine }
