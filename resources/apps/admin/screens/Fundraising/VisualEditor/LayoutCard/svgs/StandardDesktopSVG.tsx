import type { FC, SVGProps } from 'react'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import styles from './svgs.styles.scss'

interface Props extends SVGProps<SVGRectElement> {
  isHeadlineFocused?: boolean
  isDescriptionFocused?: boolean
  isPreviewHovered?: boolean
  headlineOnClick?: () => void
  descriptionOnClick?: () => void
}

const StandardDesktopSVG: FC<Props> = ({
  isPreviewHovered,
  isHeadlineFocused,
  isDescriptionFocused,
  headlineOnClick,
  descriptionOnClick,
}) => {
  const {
    layoutValue: { backgroundImage },
    brandingValue: { brandingLogo, brandingColour },
  } = useFundraisingFormState()

  const renderHeadlineOverlay = () =>
    isPreviewHovered || isHeadlineFocused ? (
      <rect x='160' y='243' width='215' height='80' rx='5' className={styles.overlay} onClick={headlineOnClick} />
    ) : null

  const renderDescriptionOverlay = () =>
    isPreviewHovered || isDescriptionFocused ? (
      <rect x='160' y='329' width='215' height='55' rx='5' className={styles.overlay} onClick={descriptionOnClick} />
    ) : null

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
        d='M0 15C0 6.71573 6.71573 -7.62939e-06 15 -7.62939e-06H734.909C743.193 -7.62939e-06 749.909 6.71572 749.909 15V499.4C749.909 507.684 743.193 514.4 734.909 514.4H15C6.71573 514.4 0 507.684 0 499.4V15Z'
        fill='white'
      />
      <mask
        id='mask0_7_3'
        style={{ maskType: 'alpha' }}
        maskUnits='userSpaceOnUse'
        x='418'
        y='0'
        width='332'
        height='515'
      >
        <path
          d='M515.625 -7.62939e-06H731.707C741.81 -7.62939e-06 750 8.18861 750 18.2898V496.11C750 506.211 741.81 514.4 731.707 514.4H418.445L515.625 -7.62939e-06Z'
          fill='#D9D9D9'
        />
      </mask>
      <g mask='url(#mask0_7_3)'>
        <g filter='url(#filter0_f_7_3)'>
          <path
            d='M500.999 -65.8432H763.874C776.165 -65.8432 786.128 -55.8858 786.128 -43.6028V537.427C786.128 549.71 776.165 559.667 763.874 559.667H382.774L500.999 -65.8432Z'
            fill={backgroundImage.full ? 'url(#background-img)' : '#D9D9D9'}
          />
        </g>
      </g>
      <g filter='url(#filter1_d_7_3)'>
        <path
          d='M392.15 165.205C392.15 156.921 398.866 150.205 407.15 150.205H550.93C559.215 150.205 565.93 156.921 565.93 165.205V386.689C565.93 394.974 559.215 401.689 550.93 401.689H407.15C398.866 401.689 392.15 394.974 392.15 386.689V165.205Z'
          fill='white'
        />
      </g>
      <rect x='419.589' y='319.385' width='121.189' height='16.0036' rx='2' fill='#828282' fillOpacity='0.3' />
      <rect x='436.737' y='200.959' width='84.375' height='65.8432' rx='2' fill='#828282' fillOpacity='0.3' />
      <rect x='419.589' y='346.82' width='121.189' height='29.7209' rx='2' fill={brandingColour.code} />
      <rect x='185.214' y='253.588' width='111.128' height='24.6912' rx='2' fill='#828282' />
      <rect x='185.214' y='288.567' width='150.229' height='24.6912' rx='2' fill='#828282' />
      <rect x='185.214' y='335.664' width='121.189' height='16.0036' rx='2' fill='#828282' fillOpacity='0.5' />
      <rect x='28.811' y='471.19' width='41.1585' height='16.4608' rx='2' fill='#828282' fillOpacity='0.5' />
      <rect x='84.3749' y='471.19' width='100.838' height='16.4608' rx='2' fill='#828282' fillOpacity='0.5' />
      <rect x='185.214' y='360.812' width='162.348' height='16.0036' rx='2' fill='#828282' fillOpacity='0.5' />
      <rect x='29' y='75' width='56.56' height='23' fill={brandingLogo.full ? 'transparent' : 'white'} />
      <svg x='29' y='75' width='56.56' height='23' viewBox='0 0 56.56 23' preserveAspectRatio='xMinYMin slice'>
        <image width='100%' height='100%' xlinkHref={brandingLogo.full} />
      </svg>
      <path
        d='M0 16.4608C0 7.36974 7.37092 -7.62939e-06 16.4634 -7.62939e-06H733.446C742.538 -7.62939e-06 749.909 7.36974 749.909 16.4608V43.2096H0V16.4608Z'
        fill='#BDBDBD'
      />
      <ellipse cx='22.6372' cy='22.6336' rx='6.17378' ry='6.1728' fill='white' />
      <ellipse cx='43.2164' cy='22.6336' rx='6.17378' ry='6.1728' fill='white' />
      <ellipse cx='63.7957' cy='22.6336' rx='6.17378' ry='6.1728' fill='white' />
      <circle cx='220' cy='205' r='30' fill={backgroundImage.full ? 'transparent' : '#D9D9D9'} />
      <svg x='170' y='155' viewBox='0 0 100 100' width='100' height='100'>
        <clipPath id='circle-clip'>
          <use xlinkHref={backgroundImage.full} />
          <circle id='circle' cx='50' cy='50' r='30' vectorEffect='non-scaling-stroke' />
        </clipPath>
        <image
          xlinkHref={backgroundImage.full}
          width='100%'
          height='100%'
          preserveAspectRatio='xMidYMid slice'
          clipPath='url(#circle-clip)'
        />
      </svg>

      {renderHeadlineOverlay()}
      {renderDescriptionOverlay()}
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
        <pattern
          id='logo-img'
          height='100%'
          width='100%'
          patternContentUnits='objectBoundingBox'
          viewBox='0 0 1 1'
          preserveAspectRatio='xMinYMin meet'
        >
          <image xlinkHref={brandingLogo.full} x='0' y='0' height='1' width='1' preserveAspectRatio='none' />
        </pattern>
        <filter
          id='filter0_f_7_3'
          x='352.774'
          y='-95.8432'
          width='463.354'
          height='685.51'
          filterUnits='userSpaceOnUse'
          colorInterpolationFilters='sRGB'
        >
          <feFlood floodOpacity='0' result='BackgroundImageFix' />
          <feBlend mode='normal' in='SourceGraphic' in2='BackgroundImageFix' result='shape' />
          <feGaussianBlur stdDeviation='15' result='effect1_foregroundBlur_7_3' />
        </filter>
        <filter
          id='filter1_d_7_3'
          x='388.15'
          y='150.205'
          width='181.781'
          height='259.484'
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
          <feBlend mode='normal' in2='BackgroundImageFix' result='effect1_dropShadow_7_3' />
          <feBlend mode='normal' in='SourceGraphic' in2='effect1_dropShadow_7_3' result='shape' />
        </filter>
      </defs>
    </svg>
  )
}

export { StandardDesktopSVG }
