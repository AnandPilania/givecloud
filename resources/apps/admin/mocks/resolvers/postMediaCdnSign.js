import { rest } from 'msw'
import { ROUTES } from './routes'

const postMediaCdnSign = () =>
  rest.post(ROUTES.mediaCdnSign, (_, response, { json }) => {
    return response(json({ filename: 'hello.png', signed_upload_url: 'http://google-cloud-storage.test/upload' }))
  })

export { postMediaCdnSign }
