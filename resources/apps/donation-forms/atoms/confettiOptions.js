import { atom } from 'recoil'
import { primaryColours } from '@/utilities/theme'

const confettiOptions = atom({
  key: 'confettiOptions',
  default: {
    width: window.innerWidth,
    height: window.innerHeight,
    recycle: false,
    colors: primaryColours,
    drawShape: (ctx) => {
      // Heart shaped confetti
      const k = 10
      const d = Math.min(13, 13)
      ctx.beginPath()
      ctx.moveTo(k, k + d / 4)
      ctx.quadraticCurveTo(k, k, k + d / 4, k)
      ctx.quadraticCurveTo(k + d / 2, k, k + d / 2, k + d / 4)
      ctx.quadraticCurveTo(k + d / 2, k, k + (d * 3) / 4, k)
      ctx.quadraticCurveTo(k + d, k, k + d, k + d / 4)
      ctx.quadraticCurveTo(k + d, k + d / 2, k + (d * 3) / 4, k + (d * 3) / 4)
      ctx.lineTo(k + d / 2, k + d)
      ctx.lineTo(k + d / 4, k + (d * 3) / 4)
      ctx.quadraticCurveTo(k, k + d / 2, k, k + d / 4)
      ctx.fill()
      ctx.stroke()
      ctx.closePath()
    },
  },
})

export default confettiOptions
