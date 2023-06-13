import classNames from 'classnames'
export default function Stamp(props) {
  return (
    <div
      className={classNames(
        { 'shadow-sm': props.bgColor },
        props.bgColor,
        'flex justify-center m-auto pt-6  w-20 h-20 rounded-full text-center'
      )}
    >
      {props.children}
    </div>
  )
}
