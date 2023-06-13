export const getConfig = () => window.donationFormConfig || {}

export const getStateFromPreviousVisit = () => {
  const config = getConfig()
  return JSON.parse(localStorage.getItem(`fundraisingForm_${config.id}`) || null)
}

export const setStateForCurrentVisit = (state) => {
  const config = getConfig()
  const localStorageKey = `fundraisingForm_${config.id}`

  if (state) {
    localStorage.setItem(localStorageKey, JSON.stringify(state))
  } else {
    localStorage.removeItem(localStorageKey)
  }
}

export default getConfig
