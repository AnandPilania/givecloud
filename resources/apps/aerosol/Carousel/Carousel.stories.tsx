import { Carousel } from './Carousel'
import { CarouselItem } from './CarouselItem'
import { CarouselItems } from './CarouselItems'
import { CarouselNextButton, CarouselPreviousButton, CarouselButton } from './CarouselButton'
export default {
  title: 'Aerosol/Carousel',
  component: Carousel,
}

export const Default = () => {
  return (
    <Carousel name='storybook'>
      <CarouselItems>
        <CarouselItem>
          <div className='bg-brand-blue text-white w-full font-bold text-center'>One</div>
        </CarouselItem>
        <CarouselItem>
          <div className='bg-brand-purple text-white w-full font-bold text-center'>Two</div>
        </CarouselItem>
        <CarouselItem>
          <div className='bg-green-400 text-white w-full font-bold text-center'>three</div>
        </CarouselItem>
        <CarouselItem>
          <div className='bg-brand-pink text-white w-full font-bold text-center'>four</div>
        </CarouselItem>
        <CarouselItem>
          <div className='bg-brand-teal text-white w-full font-bold text-center'>five</div>
        </CarouselItem>
      </CarouselItems>
      <CarouselPreviousButton>previous</CarouselPreviousButton>
      <CarouselNextButton>next</CarouselNextButton>
      <div className='flex my-4 mx-2'>
        <CarouselButton indexToNavigate={4}>see number 5</CarouselButton>
        <CarouselButton indexToNavigate={1}>see number 2</CarouselButton>
        <CarouselButton indexToNavigate={3}>see number 4</CarouselButton>
      </div>
    </Carousel>
  )
}
