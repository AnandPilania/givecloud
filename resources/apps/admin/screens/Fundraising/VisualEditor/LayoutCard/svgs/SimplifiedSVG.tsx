import type { FC } from 'react'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'

const SimplifiedSVG: FC = () => {
  const {
    layoutValue: { backgroundImage },
    brandingValue: { brandingLogo, brandingColour },
  } = useFundraisingFormState()

  return (
    <svg
      width='500'
      height='515'
      viewBox='0 0 750 515'
      fill='currentColor'
      xmlns='http://www.w3.org/2000/svg'
      aria-hidden='true'
      focusable='false'
    >
      <path
        d='M0 43.2099H750V504.403C750 509.926 745.523 514.403 740 514.403H9.99999C4.47714 514.403 0 509.926 0 504.403V43.2099Z'
        fill={backgroundImage.full ? 'url(#background-img)' : '#D9D9D9'}
      />
      <g filter='url(#filter1_d_1_4)'>
        <rect x='294.239' y='137.86' width='172.84' height='279.835' rx='8' fill='white' />
      </g>
      <rect x='321.674' y='335.848' width='121.171' height='16.0037' rx='2' fill='#828282' fillOpacity='0.3' />
      <rect x='338.819' y='222.222' width='84.3621' height='65.8436' rx='2' fill='#828282' fillOpacity='0.3' />
      <rect
        x='321.674'
        y='363.283'
        width='121.171'
        height='29.7211'
        rx='2'
        fill={brandingColour.code ? brandingColour.code : '#D9D9D9'}
      />
      <rect x='353.91' y='154.32' width='56.56' height='23' fill={brandingLogo.full ? 'transparent' : 'white'} />
      <svg x='353.91' y='154.32' width='56.56' height='23' viewBox='0 0 56.56 23' preserveAspectRatio='xMinYMin slice'>
        <image width='100%' height='100%' xlinkHref={brandingLogo.full} />
      </svg>
      <path
        d='M0 16.4609C0 7.3698 7.3698 0 16.4609 0H733.334C742.425 0 749.795 7.3698 749.795 16.4609V43.2099H0V16.4609Z'
        fill='#BDBDBD'
      />
      <circle cx='22.6337' cy='22.6337' r='6.17284' fill='white' />
      <circle cx='43.2099' cy='22.6337' r='6.17284' fill='white' />
      <circle cx='63.786' cy='22.6337' r='6.17284' fill='white' />
      <defs>
        <pattern id='background-img' patternUnits='userSpaceOnUse' width='100%' height='100%'>
          <image
            xlinkHref={backgroundImage.full}
            x='0'
            y='0'
            width='100%'
            height='100%'
            preserveAspectRatio='xMinYMin slice'
          />
        </pattern>
        <pattern id='logo-img' patternContentUnits='objectBoundingBox' width='100%' height='100%'>
          <image xlinkHref={brandingLogo.full} x='0' y='0' width='1' height='1' preserveAspectRatio='none' />
        </pattern>
        <filter
          id='filter0_d_1_4'
          x='284.239'
          y='131.86'
          width='192.84'
          height='299.835'
          filterUnits='userSpaceOnUse'
          colorInterpolationFilters='sRGB'
        >
          <feFlood floodOpacity='0' result='BackgroundImageFix' />
          <feColorMatrix
            in='SourceAlpha'
            type='matrix'
            values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
            result='hardAlpha'
          />
          <feOffset dy='4' />
          <feGaussianBlur stdDeviation='5' />
          <feComposite in2='hardAlpha' operator='out' />
          <feColorMatrix type='matrix' values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0' />
          <feBlend mode='normal' in2='BackgroundImageFix' result='effect1_dropShadow_1_4' />
          <feBlend mode='normal' in='SourceGraphic' in2='effect1_dropShadow_1_4' result='shape' />
        </filter>
        <filter
          id='filter1_d_1_4'
          x='290.239'
          y='137.86'
          width='180.84'
          height='287.835'
          filterUnits='userSpaceOnUse'
          colorInterpolationFilters='sRGB'
        >
          <feFlood floodOpacity='0' result='BackgroundImageFix' />
          <feColorMatrix
            in='SourceAlpha'
            type='matrix'
            values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
            result='hardAlpha'
          />
          <feOffset dy='4' />
          <feGaussianBlur stdDeviation='2' />
          <feComposite in2='hardAlpha' operator='out' />
          <feColorMatrix type='matrix' values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0' />
          <feBlend mode='normal' in2='BackgroundImageFix' result='effect1_dropShadow_1_4' />
          <feBlend mode='normal' in='SourceGraphic' in2='effect1_dropShadow_1_4' result='shape' />
        </filter>
      </defs>
    </svg>
  )
}

export { SimplifiedSVG }
