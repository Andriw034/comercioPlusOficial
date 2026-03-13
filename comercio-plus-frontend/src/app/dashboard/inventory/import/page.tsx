import { useMemo, useState, type ChangeEvent, type DragEvent } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'

type PreviewResponse = {
  headers: string[]
  preview_rows: string[][]
  analysis_rows?: Array<{
    row: number
    matched_by: 'barcode' | 'sku' | 'name' | 'none'
    action: 'create' | 'update' | 'skip'
    resolved_sku: string | null
    resolved_barcode: string | null
    barcode_source: 'excel' | 'generated' | 'existing' | 'none'
  }>
  total_rows: number
}


type ImportResponse = {
  success: boolean
  imported: number
  updated: number
  failed: number
  errors: Array<{ row: number; error: string }>
}


const PREVIEW_TIMEOUT_MS = 120_000
const IMPORT_TIMEOUT_MS = 600_000

export default function InventoryImportPage() {
  const [file, setFile] = useState<File | null>(null)
  const [upsert, setUpsert] = useState(true)
  const [loading, setLoading] = useState(false)
  const [downloadingTemplate, setDownloadingTemplate] = useState(false)
  const [preview, setPreview] = useState<PreviewResponse | null>(null)
  const [result, setResult] = useState<ImportResponse | null>(null)

  const fileSizeLabel = useMemo(() => {
    if (!file) return ''
    return `${(file.size / 1024).toFixed(1)} KB`
  }, [file])

  const selectFile = (f: File | null) => {
    if (!f) return
    setFile(f)
    setPreview(null)
    setResult(null)
  }

  const onInputChange = (event: ChangeEvent<HTMLInputElement>) => {
    selectFile(event.target.files?.[0] ?? null)
  }

  const onDrop = (event: DragEvent<HTMLDivElement>) => {
    event.preventDefault()
    selectFile(event.dataTransfer.files?.[0] ?? null)
  }

  const onDragOver = (event: DragEvent<HTMLDivElement>) => {
    event.preventDefault()
  }

  const requestPreview = async () => {
    if (!file) return
    setLoading(true)
    try {
      const form = new FormData()
      form.append('file', file)
      const { data } = await API.post<PreviewResponse>('/inventory/preview', form, {
        timeout: PREVIEW_TIMEOUT_MS,
      })
      setPreview(data)
    } catch (error) {
      console.error('[inventory-import] preview error', error)
      const isTimeout = (error as any)?.code === 'ECONNABORTED'
      if (isTimeout) {
        alert('La vista previa tardo demasiado. Intenta nuevamente o divide el archivo.')
        return
      }

      const apiError = (error as any)?.response?.data
      if (apiError && typeof apiError === 'object') {
        const payload = apiError as { message?: string; error?: string; error_class?: string }
        const message = String(payload.error || payload.message || 'No se pudo generar la vista previa del archivo.')
        alert(message)
      } else {
        alert('No se pudo generar la vista previa del archivo.')
      }
    } finally {
      setLoading(false)
    }
  }

  const confirmImport = async () => {
    if (!file) return
    setLoading(true)
    try {
      const form = new FormData()
      form.append('file', file)
      form.append('upsert', upsert ? '1' : '0')
      const { data } = await API.post<ImportResponse>('/inventory/import', form, {
        timeout: IMPORT_TIMEOUT_MS,
      })
      setResult(data)

      if (data.success && typeof window !== 'undefined') {
        const payload = {
          imported: Number(data.imported || 0),
          updated: Number(data.updated || 0),
          failed: Number(data.failed || 0),
          at: Date.now(),
        }
        localStorage.setItem('inventory:last-import-at', String(payload.at))
        window.dispatchEvent(new CustomEvent('inventory:imported', { detail: payload }))
      }
    } catch (error) {
      console.error('[inventory-import] import error', error)
      const isTimeout = (error as any)?.code === 'ECONNABORTED'
      if (isTimeout) {
        alert('La importacion tardo mas de lo esperado. El servidor sigue procesando un archivo grande; intenta de nuevo y espera a que termine.')
        return
      }

      const apiError = (error as any)?.response?.data
      if (apiError && typeof apiError === 'object') {
        const importPayload = apiError as Partial<ImportResponse> & { message?: string }
        if (Array.isArray(importPayload.errors)) {
          setResult({
            success: Boolean(importPayload.success),
            imported: Number(importPayload.imported || 0),
            updated: Number(importPayload.updated || 0),
            failed: Number(importPayload.failed || 0),
            errors: importPayload.errors.map((item: any) => ({
              row: Number(item?.row || 0),
              error: String(item?.error || 'Error de importacion'),
            })),
          })
        }
        const firstError = importPayload.errors?.[0]?.error
        const message = firstError || importPayload.message || 'No se pudo importar el archivo.'
        alert(String(message))
      } else {
        alert('No se pudo importar el archivo.')
      }
    } finally {
      setLoading(false)
    }
  }

  const downloadTemplate = async () => {
    setDownloadingTemplate(true)
    try {
      const response = await API.get('/inventory/template', { responseType: 'blob' })
      const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url

      const disposition = String(response.headers?.['content-disposition'] || '')
      const filenameMatch = disposition.match(/filename="?([^"]+)"?/i)
      link.download = filenameMatch?.[1] || 'inventario-template.csv'

      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error('[inventory-import] template error', error)
      alert('No se pudo descargar el template de inventario.')
    } finally {
      setDownloadingTemplate(false)
    }
  }

  const resetAll = () => {
    setFile(null)
    setPreview(null)
    setResult(null)
    setUpsert(true)
  }

  return (
    <div className="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
      <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">Inventario</p>
        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Importacion masiva</h1>
        <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
          Sube archivos CSV o XLSX (max 10MB). Columnas no estandar se guardan en metadata.
        </p>
        <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Las imagenes son opcionales en este paso. Puedes agregarlas despues por URL o carga manual.
        </p>
      </div>

      <div className="rounded-2xl border border-dashed border-orange-300 bg-orange-50/40 p-6 dark:border-orange-400/40 dark:bg-orange-500/5">
        <div
          className="cursor-pointer rounded-xl border border-orange-200 bg-white p-6 text-center dark:border-orange-400/30 dark:bg-slate-900"
          onDrop={onDrop}
          onDragOver={onDragOver}
          onClick={() => document.getElementById('inventory-import-file')?.click()}
        >
          <p className="text-sm font-semibold text-slate-800 dark:text-slate-100">Arrastra el archivo aqui o haz clic</p>
          <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">Formatos: .csv .xlsx .xls</p>
          <input id="inventory-import-file" type="file" accept=".csv,.xlsx,.xls" className="hidden" onChange={onInputChange} />
        </div>

        {file ? (
          <div className="mt-4 flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-slate-900">
            <div>
              <p className="font-semibold text-slate-900 dark:text-slate-100">{file.name}</p>
              <p className="text-xs text-slate-500 dark:text-slate-400">{fileSizeLabel}</p>
            </div>
            <button onClick={resetAll} className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">
              Limpiar
            </button>
          </div>
        ) : null}

        <label className="mt-4 inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
          <input type="checkbox" checked={upsert} onChange={(e) => setUpsert(e.target.checked)} />
          Modo upsert (actualizar SKU existente)
        </label>

        <div className="mt-4 flex flex-wrap gap-3">
          <button
            onClick={downloadTemplate}
            disabled={loading || downloadingTemplate}
            className="rounded-lg border border-orange-300 px-4 py-2 text-sm font-semibold text-orange-700 disabled:opacity-60 dark:border-orange-400/50 dark:text-orange-300"
          >
            {downloadingTemplate ? 'Descargando...' : 'Descargar template'}
          </button>
          <button
            onClick={requestPreview}
            disabled={!file || loading}
            className="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            {loading ? 'Procesando...' : 'Ver vista previa'}
          </button>
          <button
            onClick={confirmImport}
            disabled={!file || loading}
            className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60 dark:bg-white dark:text-slate-900"
          >
            {loading ? 'Importando...' : 'Confirmar importacion'}
          </button>
          <Link to="/dashboard/products" className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">
            Ver productos
          </Link>
        </div>
      </div>

      {preview ? (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
          <h2 className="text-sm font-bold uppercase tracking-[0.11em] text-slate-600 dark:text-slate-300">
            Vista previa ({preview.total_rows} filas)
          </h2>
          <div className="mt-4 overflow-x-auto">
            <table className="min-w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-200 dark:border-white/10">
                  {preview.headers.map((header) => (
                    <th key={header} className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">{header}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {preview.preview_rows.map((row, rowIndex) => (
                  <tr key={rowIndex} className="border-b border-slate-100 dark:border-white/5">
                    {row.map((cell, colIndex) => (
                      <td key={`${rowIndex}-${colIndex}`} className="px-3 py-2 text-slate-600 dark:text-slate-300">{cell || '-'}</td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {Array.isArray(preview.analysis_rows) && preview.analysis_rows.length > 0 ? (
            <div className="mt-5 overflow-x-auto">
              <h3 className="mb-2 text-[11px] font-bold uppercase tracking-[0.11em] text-slate-500 dark:text-slate-300">
                Resolucion de importacion (primeras filas)
              </h3>
              <table className="min-w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-200 dark:border-white/10">
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">Fila</th>
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">Match</th>
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">Accion</th>
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">SKU final</th>
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">Barcode final</th>
                    <th className="px-3 py-2 font-semibold text-slate-700 dark:text-slate-100">Origen barcode</th>
                  </tr>
                </thead>
                <tbody>
                  {preview.analysis_rows.map((item) => (
                    <tr key={`analysis-${item.row}`} className="border-b border-slate-100 dark:border-white/5">
                      <td className="px-3 py-2 text-slate-600 dark:text-slate-300">{item.row}</td>
                      <td className="px-3 py-2 text-slate-600 dark:text-slate-300">{item.matched_by}</td>
                      <td className="px-3 py-2 text-slate-600 dark:text-slate-300">{item.action}</td>
                      <td className="px-3 py-2 text-slate-600 dark:text-slate-300">{item.resolved_sku || '-'}</td>
                      <td className="px-3 py-2 font-mono text-slate-700 dark:text-slate-200">{item.resolved_barcode || '-'}</td>
                      <td className="px-3 py-2 text-slate-600 dark:text-slate-300">{item.barcode_source}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : null}
        </div>
      ) : null}

      {result ? (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900">
          <h2 className="text-sm font-bold uppercase tracking-[0.11em] text-slate-600 dark:text-slate-300">Resultado</h2>
          <div className="mt-4 grid gap-3 sm:grid-cols-4">
            <StatCard label="Estado" value={result.success ? 'OK' : 'ERROR'} />
            <StatCard label="Importados" value={String(result.imported)} />
            <StatCard label="Actualizados" value={String(result.updated)} />
            <StatCard label="Fallidos" value={String(result.failed)} />
          </div>
          {result.errors.length > 0 ? (
            <div className="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-100">
              {result.errors.slice(0, 10).map((item, index) => (
                <p key={`${item.row}-${index}`}>Fila {item.row}: {item.error}</p>
              ))}
            </div>
          ) : null}
        </div>
      ) : null}
    </div>
  )
}

function StatCard({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-800/60">
      <p className="text-[11px] font-semibold uppercase tracking-[0.11em] text-slate-500 dark:text-slate-300">{label}</p>
      <p className="mt-1 text-xl font-black text-slate-900 dark:text-white">{value}</p>
    </div>
  )
}
