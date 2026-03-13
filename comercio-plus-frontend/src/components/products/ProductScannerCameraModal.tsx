import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Icon } from '@/components/Icon'

type Props = {
  open: boolean
  onClose: () => void
  onDetected: (rawCode: string) => void
}

type CameraState = 'checking' | 'ready' | 'unsupported' | 'error'

type BarcodeLike = {
  rawValue?: string
}

const DETECTION_INTERVAL_MS = 350
const REPEAT_COOLDOWN_MS = 1200

export default function ProductScannerCameraModal({ open, onClose, onDetected }: Props) {
  const videoRef = useRef<HTMLVideoElement | null>(null)
  const streamRef = useRef<MediaStream | null>(null)
  const detectTimerRef = useRef<number | null>(null)
  const detectorRef = useRef<any>(null)
  const lastHitRef = useRef<{ value: string; at: number } | null>(null)
  const detectingRef = useRef(false)

  const [manualCode, setManualCode] = useState('')
  const [cameraState, setCameraState] = useState<CameraState>('checking')
  const [cameraMessage, setCameraMessage] = useState('Preparando camara...')
  const [retryNonce, setRetryNonce] = useState(0)

  const canUseBarcodeDetector = useMemo(() => {
    return typeof window !== 'undefined' && 'BarcodeDetector' in window
  }, [])

  const stopCamera = useCallback(() => {
    if (detectTimerRef.current) {
      window.clearInterval(detectTimerRef.current)
      detectTimerRef.current = null
    }

    if (streamRef.current) {
      streamRef.current.getTracks().forEach((track) => track.stop())
      streamRef.current = null
    }

    detectorRef.current = null
    detectingRef.current = false
  }, [])

  const buildCameraErrorMessage = (error: unknown): string => {
    const fallback = 'No pude abrir la camara. Verifica permisos y vuelve a intentar.'
    const name = (error as { name?: string } | null)?.name

    if (name === 'NotAllowedError' || name === 'SecurityError') {
      return 'Permiso de camara denegado. Habilitalo en el navegador y reintenta.'
    }
    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
      return 'No encontre una camara disponible en este dispositivo.'
    }
    if (name === 'NotReadableError' || name === 'TrackStartError') {
      return 'La camara esta en uso por otra aplicacion. Cierra esa app y reintenta.'
    }
    if (name === 'OverconstrainedError') {
      return 'No pude abrir la camara trasera. Prueba de nuevo o usa ingreso manual.'
    }

    return fallback
  }

  const applyDetectedCode = useCallback((value: string) => {
    const normalized = value.trim()
    if (!normalized) return

    const now = Date.now()
    const lastHit = lastHitRef.current
    if (lastHit && lastHit.value === normalized && now - lastHit.at < REPEAT_COOLDOWN_MS) {
      return
    }
    lastHitRef.current = { value: normalized, at: now }

    onDetected(normalized)
    onClose()
  }, [onClose, onDetected])

  const startDetectionLoop = useCallback(() => {
    if (!videoRef.current || !detectorRef.current || detectTimerRef.current) return

    detectTimerRef.current = window.setInterval(async () => {
      if (!videoRef.current || !detectorRef.current || detectingRef.current) return
      if (videoRef.current.readyState < 2) return

      try {
        detectingRef.current = true
        const barcodes = (await detectorRef.current.detect(videoRef.current)) as BarcodeLike[]
        if (!Array.isArray(barcodes) || barcodes.length === 0) return

        const hit = barcodes.find((item) => typeof item?.rawValue === 'string' && item.rawValue.trim().length > 0)
        if (!hit?.rawValue) return

        applyDetectedCode(hit.rawValue)
      } catch {
        // ignore intermittent detector errors from camera frames
      } finally {
        detectingRef.current = false
      }
    }, DETECTION_INTERVAL_MS)
  }, [applyDetectedCode])

  const openCamera = useCallback(async () => {
    if (!navigator?.mediaDevices?.getUserMedia) {
      setCameraState('unsupported')
      setCameraMessage('Este navegador no soporta acceso a camara. Usa ingreso manual o lector.')
      return
    }

    const isLocalhost =
      typeof window !== 'undefined' &&
      (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')

    if (typeof window !== 'undefined' && !window.isSecureContext && !isLocalhost) {
      setCameraState('unsupported')
      setCameraMessage('La camara requiere HTTPS en produccion. Usa https:// para escanear.')
      return
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        audio: false,
        video: {
          facingMode: { ideal: 'environment' },
        },
      })

      streamRef.current = stream
      if (videoRef.current) {
        videoRef.current.srcObject = stream
        videoRef.current.playsInline = true
        await videoRef.current.play()
      }

      if (!canUseBarcodeDetector) {
        setCameraState('unsupported')
        setCameraMessage('Tu navegador no soporta deteccion directa. Puedes ingresar el codigo manual.')
        return
      }

      const Detector = (window as any).BarcodeDetector
      detectorRef.current = new Detector({
        formats: ['qr_code', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39'],
      })

      setCameraState('ready')
      setCameraMessage('Escanea el codigo dentro del recuadro.')
      startDetectionLoop()
    } catch (error) {
      setCameraState('error')
      setCameraMessage(buildCameraErrorMessage(error))
    }
  }, [canUseBarcodeDetector, startDetectionLoop])

  const handleRetryCamera = () => {
    stopCamera()
    setCameraState('checking')
    setCameraMessage('Reintentando camara...')
    setRetryNonce((previous) => previous + 1)
  }

  useEffect(() => {
    if (!open) {
      stopCamera()
      setManualCode('')
      setCameraState('checking')
      setCameraMessage('Preparando camara...')
      return
    }

    const previous = document.activeElement as HTMLElement | null
    document.body.style.overflow = 'hidden'
    setCameraState('checking')
    setCameraMessage('Solicitando permiso de camara...')

    void openCamera()

    return () => {
      stopCamera()
      document.body.style.overflow = ''
      previous?.focus()
    }
  }, [open, openCamera, retryNonce, stopCamera])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="scanner-camera-title"
        className="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_30px_80px_rgba(2,6,23,0.35)] dark:border-white/10 dark:bg-slate-900"
      >
        <div className="mb-3 flex items-start justify-between gap-3">
          <div>
            <h3 id="scanner-camera-title" className="text-[18px] font-bold text-slate-900 dark:text-white">
              Escanear con camara
            </h3>
            <p className="text-[12px] text-slate-500 dark:text-white/60">{cameraMessage}</p>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg border border-slate-200 bg-white p-1 text-slate-500 transition hover:bg-slate-50 dark:border-white/15 dark:bg-white/5 dark:text-white/70"
            aria-label="Cerrar modal de camara"
          >
            <Icon name="x" size={16} />
          </button>
        </div>

        <div className="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-900 dark:border-white/10">
          <video ref={videoRef} className="h-[320px] w-full object-cover" muted autoPlay playsInline />
          <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div className="h-[56%] w-[74%] rounded-xl border-2 border-orange-400/85 shadow-[0_0_0_9999px_rgba(2,6,23,0.28)]" />
          </div>
          {cameraState === 'ready' && (
            <div className="pointer-events-none absolute left-0 right-0 h-px animate-scanner-line bg-gradient-to-r from-transparent via-orange-500 to-transparent shadow-[0_0_8px_rgba(255,107,53,0.7)]" />
          )}
          {cameraState !== 'ready' && (
            <div className="absolute inset-0 flex items-center justify-center bg-slate-950/45">
              <p className="rounded-lg bg-slate-900/70 px-3 py-2 text-[12px] text-white">{cameraMessage}</p>
            </div>
          )}
        </div>

        <div className="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
          {cameraState !== 'ready' && (
            <div className="mb-3 flex flex-wrap gap-2">
              <button
                type="button"
                onClick={handleRetryCamera}
                className="rounded-xl border border-slate-300 bg-white px-3 py-2 text-[12px] font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/20 dark:bg-slate-900 dark:text-white"
              >
                Reintentar camara
              </button>
              <button
                type="button"
                onClick={onClose}
                className="rounded-xl border border-slate-300 bg-white px-3 py-2 text-[12px] font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/20 dark:bg-slate-900 dark:text-white"
              >
                Continuar manual
              </button>
            </div>
          )}

          <p className="mb-2 text-[12px] font-semibold text-slate-700 dark:text-white/85">Fallback manual</p>
          <div className="flex flex-col gap-2 sm:flex-row">
            <input
              value={manualCode}
              onChange={(event) => setManualCode(event.target.value)}
              onKeyDown={(event) => {
                if (event.key === 'Enter') {
                  event.preventDefault()
                  applyDetectedCode(manualCode)
                }
              }}
              placeholder="Escribe o pega el codigo si la camara falla"
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-700 outline-none focus:border-orange-400 dark:border-white/15 dark:bg-slate-900 dark:text-white"
            />
            <button
              type="button"
              onClick={() => applyDetectedCode(manualCode)}
              className="rounded-xl bg-orange-500 px-4 py-2 text-[13px] font-semibold text-white transition hover:bg-orange-600"
            >
              Usar codigo
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
