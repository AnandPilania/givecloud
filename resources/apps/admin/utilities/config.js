export const getConfig = () => window.adminSpaData || {}
export const setConfig = (config) => (window.adminSpaData = { ...getConfig(), ...config })

export default getConfig
