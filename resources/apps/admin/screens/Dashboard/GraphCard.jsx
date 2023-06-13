const GraphCard = (props) => {
  const { accentColor, icon: Icon, label, contentHeight = 'auto', children } = props

  return (
    <div className='bg-white shadow rounded-lg px-6 py-4 pb-3'>
      <div className='flex gap-2'>
        <div
          className='flex justify-center items-center text-white rounded-full w-6 h-6'
          style={{ backgroundColor: accentColor }}
        >
          <Icon />
        </div>
        <div className='font-medium text-gray-500'>{label}</div>
      </div>
      <div style={{ height: contentHeight }} className='my-3'>
        {children}
      </div>
    </div>
  )
}

export default GraphCard
