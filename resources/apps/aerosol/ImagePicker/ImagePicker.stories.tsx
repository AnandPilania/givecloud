import type { ComponentMeta, ComponentStory } from '@storybook/react'
import type { ImageData } from './ImagePicker'
import { useState } from 'react'
import { Column } from '@/aerosol/Column'
import { ImagePicker } from './ImagePicker'

export default {
  title: 'Aerosol/Image Picker',
  component: ImagePicker,
} as ComponentMeta<typeof ImagePicker>

export const Default: ComponentStory<typeof ImagePicker> = () => {
  const url = 'https://cdn.pixabay.com/photo/2015/04/23/22/00/tree-736885__480.jpg'
  const [image, setImage] = useState<ImageData>({ id: '1', url, name: '' })

  const handleChange = (image: ImageData) => setImage({ id: '1', url: image.url, name: 'image' })
  const handleRemove = () => setImage({ id: '', url: '', name: '' })

  return (
    <Column columnWidth='one'>
      <ImagePicker
        id='image'
        label='Image Picker'
        aria-label='whoohoo'
        image={image.url}
        onChange={handleChange}
        removeImage={handleRemove}
      />
    </Column>
  )
}
