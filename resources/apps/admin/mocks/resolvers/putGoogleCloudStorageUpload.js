import { rest } from 'msw'
import { ROUTES } from './routes'

const putGoogleCloudStorageUpload = () =>
  rest.put(ROUTES.googleCloudStorageUpload, (_, response, { status }) => {
    return response(status(200))
  })

export { putGoogleCloudStorageUpload }
