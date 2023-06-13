const formatDate = (timestamp, options = {}) => {
  const timeZone = new Intl.DateTimeFormat().resolvedOptions().timeZone
  const date = new Date(timestamp)
  return date.toLocaleDateString('en-US', { timeZone, ...options })
}

export default formatDate
