import { useEffect, useMemo, useState, type ReactNode } from 'react'
import { IMAGE_TRANSFORMS, applyCloudinaryTransform } from '@/utils/cloudinary'
import { getImageBrightness, getThemeClassesByBrightness, type ImageBrightness } from '@/utils/imageTheme'

type CoverRatio = '21/9' | '16/9' | 'free'

type CoverImageProps = {
  src?: string | null
  alt?: string
  ratio?: CoverRatio
  className?: string
  imageClassName?: string
  overlay?: boolean
  overlayMode?: 'default' | 'header'
  brightness?: ImageBrightness
  onBrightnessChange?: (value: ImageBrightness) => void
  children?: ReactNode
}

const ratioClassMap: Record<Exclude<CoverRatio, 'free'>, string> = {
  '21/9': 'aspect-[21/9]',
  '16/9': 'aspect-[16/9]',
}

export default function CoverImage({
  src,
  alt = '',
  ratio = '21/9',
  className = '',
  imageClassName = '',
  overlay = true,
  overlayMode = 'default',
  brightness,
  onBrightnessChange,
  children,
}: CoverImageProps) {
  const transformedSrc = useMemo(() => {
    if (!src) return ''

    const transform = ratio === '16/9' ? IMAGE_TRANSFORMS.cover16x9 : IMAGE_TRANSFORMS.cover21x9
    return applyCloudinaryTransform(src, transform)
  }, [ratio, src])

  const [computedBrightness, setComputedBrightness] = useState<ImageBrightness>('dark')

  useEffect(() => {
    if (brightness) return

    let cancelled = false
    getImageBrightness(transformedSrc).then((value) => {
      if (cancelled) return
      setComputedBrightness(value)
      onBrightnessChange?.(value)
    })

    return () => {
      cancelled = true
    }
  }, [brightness, onBrightnessChange, transformedSrc])

  const ratioClass = ratio === 'free' ? '' : ratioClassMap[ratio]
  const themeClasses = getThemeClassesByBrightness(brightness || computedBrightness)
  const overlayClassName =
    overlayMode === 'header'
      ? 'from-slate-950/45 via-slate-950/62 to-slate-950/82'
      : themeClasses.overlay

  return (
    <div className={`relative overflow-hidden bg-slate-900 ${ratioClass} ${className}`.trim()}>
      {transformedSrc ? (
        <img
          src={transformedSrc}
          alt={alt}
          loading="lazy"
          decoding="async"
          className={`h-full w-full object-cover ${imageClassName}`.trim()}
        />
      ) : (
        <div className="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-comercioplus-900" />
      )}

      {overlay ? <div className={`absolute inset-0 bg-gradient-to-b ${overlayClassName}`} /> : null}
      {children ? <div className="relative z-10 h-full">{children}</div> : null}
    </div>
  )
}
