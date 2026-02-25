import { Icon } from '@/components/Icon'
import { IMAGE_TRANSFORMS, applyCloudinaryTransform } from '@/utils/cloudinary'

type LogoImageProps = {
  src?: string | null
  alt: string
  className?: string
  imageClassName?: string
  fallbackClassName?: string
}

export default function LogoImage({
  src,
  alt,
  className = '',
  imageClassName = '',
  fallbackClassName = '',
}: LogoImageProps) {
  const normalizedSrc = src ? applyCloudinaryTransform(src, IMAGE_TRANSFORMS.logo) : ''

  return (
    <div
      className={`flex aspect-square items-center justify-center overflow-hidden rounded-2xl bg-slate-100 p-2 ${className}`.trim()}
    >
      {normalizedSrc ? (
        <img
          src={normalizedSrc}
          alt={alt}
          loading="lazy"
          decoding="async"
          className={`h-full w-full object-contain ${imageClassName}`.trim()}
        />
      ) : (
        <div
          className={`flex h-full w-full items-center justify-center rounded-xl bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 ${fallbackClassName}`.trim()}
        >
          <Icon name="store" size={24} className="text-white" />
        </div>
      )}
    </div>
  )
}
