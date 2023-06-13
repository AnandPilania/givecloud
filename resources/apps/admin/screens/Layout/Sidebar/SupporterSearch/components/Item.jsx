import classnames from 'classnames'

export default function Item({ result, active }) {
  return (
    <div
      className={classnames('mb-2 rounded p-2 cursor-default select-none', {
        'bg-brand-blue opacity-80 text-white': active,
        'text-gray-800': !active,
      })}
    >
      <p className='text-sm font-semibold truncate' dangerouslySetInnerHTML={{ __html: result.name }} />
      {result.invoicenumber && (
        <p
          className='text-xs font-semibold'
          dangerouslySetInnerHTML={{ __html: `CONTRIBUTION ${result.invoicenumber} (${result.date})` }}
        />
      )}
      <p
        className={classnames('text-xs truncate', { 'text-gray-600': !active, 'text-white': active })}
        dangerouslySetInnerHTML={{
          __html: [result.address_line_1, result.city, result.state, result.country, result.zip]
            .filter((n) => n)
            .join(', '),
        }}
      ></p>
      <p
        className={classnames('text-xs truncate', { 'text-gray-600': !active, 'text-white': active })}
        dangerouslySetInnerHTML={{ __html: result.email }}
      />
      <p
        className={classnames('text-xs truncate', { 'text-gray-600': !active, 'text-white': active })}
        dangerouslySetInnerHTML={{ __html: result.phone }}
      />
    </div>
  )
}
