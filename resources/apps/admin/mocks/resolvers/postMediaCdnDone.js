import { rest } from 'msw'
import { ROUTES } from './routes'

const postMediaCdnDone = () =>
  rest.post(ROUTES.mediaCdnDone, (_, response, { json }) => {
    return response(
      json({
        id: 6,
        collection_name: 'files',
        name: 'hello',
        filename: 'hello.png',
        content_type: 'image/png',
        size: 89167,
        is_audio: false,
        is_image: true,
        is_video: false,
        public_url: 'https://cdn.givecloud.test/s/files/1/0000/0002/files/hello.png',
        thumbnail_url: 'https://cdn.givecloud.test/s/files/2/0000/0002/files/hello_300x_cropped_top.png',
        created_by: 1,
        created_at: '2022-10-21 06:10:02',
        updated_by: 1,
        updated_at: '2022-10-21 06:10:02',
      })
    )
  })

export { postMediaCdnDone }
