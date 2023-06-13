import {
  postRestoreFundraisingForm,
  getFundraisingForm,
  getFundraisingForms,
  getFundraisingSettings,
  patchFundraisingSettings,
  getGlobalSettings,
  patchFundraisingForm,
  postFundraisingForm,
  getOrgSettings,
  patchOrgSettings,
  getBrandingSettings,
  patchBrandingSettings,
  getAcceptDonations,
  patchAcceptDonations,
  patchDisconnectAcceptDonations,
  postMediaCdnSign,
  postMediaCdnDone,
  putGoogleCloudStorageUpload,
} from '@/mocks/resolvers'

const handlers = [
  {
    setter: 'setPostRestoreFundraisingForm',
    getter: postRestoreFundraisingForm,
  },
  {
    setter: 'setFundraisingForm',
    getter: getFundraisingForm,
  },
  {
    setter: 'setFundraisingForms',
    getter: getFundraisingForms,
  },
  {
    setter: 'setGlobalSettings',
    getter: getGlobalSettings,
  },
  {
    setter: 'setPatchFundraisingForms',
    getter: patchFundraisingForm,
  },
  {
    setter: 'setPostFundraisingForm',
    getter: postFundraisingForm,
  },
  {
    setter: 'setFundraisingSettings',
    getter: getFundraisingSettings,
  },
  {
    setter: 'setPatchFundraisingSettings',
    getter: patchFundraisingSettings,
  },
  {
    setter: 'setOrgSettings',
    getter: getOrgSettings,
  },
  {
    setter: 'setPatchOrgSettings',
    getter: patchOrgSettings,
  },
  {
    setter: 'setBrandingSettings',
    getter: getBrandingSettings,
  },
  {
    setter: 'setPatchBrandingSettings',
    getter: patchBrandingSettings,
  },
  {
    setter: 'setAcceptDonations',
    getter: getAcceptDonations,
  },
  {
    setter: 'setPatchAcceptDonations',
    getter: patchAcceptDonations,
  },
  {
    setter: 'setPatchDisconnectAcceptDonations',
    getter: patchDisconnectAcceptDonations,
  },
  {
    setter: 'setPostMediaCdnSign',
    getter: postMediaCdnSign,
  },
  {
    setter: 'setPostMediaCdnDone',
    getter: postMediaCdnDone,
  },
  {
    setter: 'setPutGoogleCloudStorageUpload',
    getter: putGoogleCloudStorageUpload,
  },
]

const createGetters = () => handlers.map(({ getter }) => getter())

const createSetters = (use) =>
  handlers.reduce(
    (rest, { setter, getter }) => ({
      ...rest,
      [setter]: (mockedResponse = {}) => use(getter(mockedResponse)),
    }),
    {}
  )

export { handlers, createGetters, createSetters }
