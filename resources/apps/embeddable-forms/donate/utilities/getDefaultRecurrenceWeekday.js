const getDefaultRecurrenceWeekday = (weekdays) => {
  const keys = weekdays ? Object.keys(weekdays) : []
  if (keys.length > 0) {
    return keys[0]
  } else {
    return null
  }
}

export default getDefaultRecurrenceWeekday
