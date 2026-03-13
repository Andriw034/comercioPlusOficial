import { Component, type ErrorInfo, type ReactNode } from 'react'

interface Props {
  children: ReactNode
  fallback?: ReactNode
}

interface State {
  hasError: boolean
  error: Error | null
}

export default class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props)
    this.state = { hasError: false, error: null }
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[ErrorBoundary]', error, info.componentStack)
  }

  render() {
    if (this.state.hasError) {
      if (this.props.fallback) return this.props.fallback

      return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 text-center">
          <div className="max-w-md rounded-2xl border border-red-200 bg-white p-8 shadow-md">
            <p className="text-4xl mb-4">⚠️</p>
            <h1 className="text-lg font-bold text-gray-900 mb-2">Algo salió mal</h1>
            <p className="text-sm text-gray-500 mb-6">
              Ocurrió un error inesperado. Por favor recarga la página o intenta de nuevo.
            </p>
            <button
              type="button"
              onClick={() => window.location.reload()}
              className="bg-orange-500 hover:bg-orange-600 text-white font-medium px-5 py-2.5 rounded-lg text-sm transition"
            >
              Recargar página
            </button>
          </div>
        </div>
      )
    }

    return this.props.children
  }
}
