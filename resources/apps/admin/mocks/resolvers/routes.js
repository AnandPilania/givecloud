const getRoute = (url) => `http://localhost/jpanel/${url}`
const getApiRoute = (url) => getRoute(`api/v1/${url}`)

export const ROUTES = {
  acceptDonations: getApiRoute('settings/accept-donations'),
  brandingSettings: getApiRoute('settings/branding'),
  disconnectAcceptDonations: getApiRoute('settings/accept-donations/disconnect'),
  fundraisingForm: getApiRoute('donation-forms/:id'),
  fundraisingForms: getApiRoute('donation-forms'),
  fundraisingSettings: getApiRoute('settings/fundraising'),
  restoreFundraisingForm: getApiRoute('donation-forms/:id/restore'),
  globalSettings: getApiRoute('donation-forms/global-settings'),
  orgSettings: getApiRoute('settings/organization'),
  mediaCdnSign: getRoute('media/cdn/sign'),
  mediaCdnDone: getRoute('media/cdn/done'),
  googleCloudStorageUpload: 'http://google-cloud-storage.test/upload',
}
