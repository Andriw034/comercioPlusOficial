import { useEffect, useRef, useState } from 'react'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Textarea from '@/components/ui/Textarea'

// -- Types ---------------------------------------------------------------------

interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  parent_id: number | null
  is_active: boolean
  product_count: number
  children?: Category[]
}

type ModalMode = 'create' | 'edit' | null

const EMPTY_FORM = {
  name: '',
  slug: '',
  description: '',
  parent_id: null as number | null,
  is_active: true,
}

// -- Helpers -------------------------------------------------------------------

function slugify(text: string) {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-+/g, '-')
    .trim()
}

function buildTree(cats: Category[]): Category[] {
  const map: Record<number, Category> = {}
  const roots: Category[] = []
  cats.forEach((c) => { map[c.id] = { ...c, children: [] } })
  cats.forEach((c) => {
    if (c.parent_id && map[c.parent_id]) {
      map[c.parent_id].children!.push(map[c.id])
    } else {
      roots.push(map[c.id])
    }
  })
  return roots
}

// -- Sub-components -------------------------------------------------------------

function StatCard({ label, value, accent }: { label: string; value: number; accent?: boolean }) {
  return (
    <div className="flex-1 min-w-[100px] rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-slate-50 to-orange-50/40 px-4 py-3 shadow-[0_8px_20px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5">
      <p className="text-[11px] uppercase tracking-wider text-slate-600 dark:text-white/40">{label}</p>
      <p className={`mt-1 text-3xl font-black ${accent ? 'text-orange-500' : 'text-slate-900 dark:text-white'}`}>
        {value}
      </p>
    </div>
  )
}

function EmptyState({ onAdd }: { onAdd: () => void }) {
  return (
    <div className="flex flex-col items-center justify-center py-16 text-center">
      <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 dark:border-white/20">
        <span className="text-2xl">🗂️</span>
      </div>
      <p className="text-[15px] font-semibold text-slate-800 dark:text-white">Sin categorias</p>
      <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
        Crea tu primera categoria para organizar el catalogo.
      </p>
      <Button className="mt-5" onClick={onAdd}>
        + Nueva categoria
      </Button>
    </div>
  )
}

function CategoryRow({
  cat,
  depth = 0,
  expanded,
  onToggle,
  onEdit,
  onDelete,
}: {
  cat: Category
  depth?: number
  expanded: Set<number>
  onToggle: (id: number) => void
  onEdit: (cat: Category) => void
  onDelete: (cat: Category) => void
}) {
  const isExpanded = expanded.has(cat.id)
  const hasChildren = (cat.children?.length ?? 0) > 0

  return (
    <>
      <tr className="group border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5">
        <td className="py-3 pl-4 pr-2">
          <div className="flex items-center gap-2" style={{ paddingLeft: `${depth * 20}px` }}>
            {hasChildren ? (
              <button
                onClick={() => onToggle(cat.id)}
                className="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded text-slate-400 transition-colors hover:text-slate-700 dark:text-white/40 dark:hover:text-white"
              >
                <span className={`text-[10px] transition-transform duration-150 ${isExpanded ? 'rotate-90' : ''}`}>▸</span>
              </button>
            ) : (
              <span className="w-5 flex-shrink-0 text-center text-slate-300 dark:text-white/20">
                {depth > 0 ? '•' : ''}
              </span>
            )}
            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border border-orange-200 bg-gradient-to-br from-orange-100 to-amber-100 text-[13px] shadow-sm dark:border-orange-500/20 dark:bg-orange-500/10">
              🏷️
            </div>
            <div>
              <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{cat.name}</p>
              {cat.slug && (
                <p className="text-[11px] font-mono text-slate-400 dark:text-white/30">/{cat.slug}</p>
              )}
            </div>
            {hasChildren && (
              <span className="ml-1 rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-white/10 dark:text-white/40">
                {cat.children!.length}
              </span>
            )}
          </div>
        </td>
        <td className="py-3 px-3 text-[13px] text-slate-600 dark:text-white/60">
          <span className="font-semibold text-slate-900 dark:text-white">{cat.product_count}</span>{' '}
          producto{cat.product_count !== 1 ? 's' : ''}
        </td>
        <td className="py-3 px-3">
          <Badge variant={cat.is_active ? 'success' : 'neutral'}>
            {cat.is_active ? 'Activa' : 'Oculta'}
          </Badge>
        </td>
        <td className="py-3 pl-3 pr-4">
          <div className="flex items-center gap-2 opacity-100">
            <button
              onClick={() => onEdit(cat)}
              className="rounded-lg border border-sky-200 bg-gradient-to-r from-sky-50 to-blue-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700 shadow-sm transition-colors hover:border-sky-300 hover:from-sky-100 hover:to-blue-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300"
            >
              ✏️ Editar
            </button>
            <button
              onClick={() => onDelete(cat)}
              className="rounded-lg border border-rose-200 bg-gradient-to-r from-rose-50 to-red-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 shadow-sm transition-colors hover:border-rose-300 hover:from-rose-100 hover:to-red-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"
            >
              🗑️ Eliminar
            </button>
          </div>
        </td>
      </tr>
      {isExpanded &&
        cat.children?.map((child) => (
          <CategoryRow
            key={child.id}
            cat={child}
            depth={depth + 1}
            expanded={expanded}
            onToggle={onToggle}
            onEdit={onEdit}
            onDelete={onDelete}
          />
        ))}
    </>
  )
}

// -- Modal ---------------------------------------------------------------------

function CategoryModal({
  mode,
  initial,
  parents,
  onClose,
  onSave,
}: {
  mode: ModalMode
  initial: typeof EMPTY_FORM & { id?: number }
  parents: Category[]
  onClose: () => void
  onSave: (data: typeof EMPTY_FORM & { id?: number }) => Promise<void>
}) {
  const [form, setForm] = useState(initial)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const nameRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    setTimeout(() => nameRef.current?.focus(), 50)
  }, [])

  const set = (key: keyof typeof EMPTY_FORM, value: any) => {
    setForm((prev) => {
      const next = { ...prev, [key]: value }
      if (key === 'name' && mode === 'create') next.slug = slugify(value)
      return next
    })
  }

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!form.name.trim()) { setError('El nombre es requerido.'); return }
    setSaving(true)
    setError('')
    try {
      await onSave(form)
      onClose()
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Error al guardar la categoria.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={onClose} />
      <div className="relative z-10 w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
        <div className="mb-5 flex items-center justify-between">
          <h2 className="text-[18px] font-semibold text-slate-900 dark:text-white">
            {mode === 'create' ? 'Nueva categoria' : 'Editar categoria'}
          </h2>
          <button
            onClick={onClose}
            className="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10"
          >
            ✕
          </button>
        </div>

        <form onSubmit={submit} className="space-y-4">
          <Input
            ref={nameRef}
            label="Nombre *"
            value={form.name}
            onChange={(e) => set('name', e.target.value)}
            placeholder="Ej: Cascos"
          />
          <Input
            label="Slug"
            value={form.slug}
            onChange={(e) => set('slug', e.target.value)}
            placeholder="ej: cascos"
          />
          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">
              Categoria padre (opcional)
            </label>
            <select
              value={form.parent_id ?? ''}
              onChange={(e) => set('parent_id', e.target.value ? Number(e.target.value) : null)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
              <option value="">-- Sin padre (raiz) --</option>
              {parents
                .filter((p) => p.id !== initial.id)
                .map((p) => (
                  <option key={p.id} value={p.id}>{p.name}</option>
                ))}
            </select>
          </div>
          <Textarea
            label="Descripcion"
            rows={2}
            value={form.description}
            onChange={(e) => set('description', e.target.value)}
            placeholder="Descripcion breve (opcional)"
          />
          <label className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
            <span className="text-[13px] font-medium text-slate-700 dark:text-white/80">Visible al publico</span>
            <input
              type="checkbox"
              checked={form.is_active}
              onChange={(e) => set('is_active', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400/40"
            />
          </label>

          {error && (
            <p className="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
              {error}
            </p>
          )}

          <div className="flex gap-2 pt-1">
            <Button type="button" variant="outline" className="flex-1" onClick={onClose}>
              Cancelar
            </Button>
            <Button type="submit" className="flex-1" loading={saving}>
              {saving ? 'Guardando...' : mode === 'create' ? 'Crear categoria' : 'Guardar cambios'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  )
}

// -- DeleteConfirm -------------------------------------------------------------

function DeleteConfirm({
  cat,
  onClose,
  onConfirm,
}: {
  cat: Category
  onClose: () => void
  onConfirm: () => Promise<void>
}) {
  const [deleting, setDeleting] = useState(false)
  const [error, setError] = useState('')

  const confirm = async () => {
    setDeleting(true)
    setError('')
    try {
      await onConfirm()
      onClose()
    } catch (err: any) {
      setError(err?.response?.data?.message || 'No se pudo eliminar.')
      setDeleting(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onClick={onClose} />
      <div className="relative z-10 w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-500/15">
          <span className="text-xl">⚠️</span>
        </div>
        <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Eliminar categoria</h2>
        <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
          Seguro que quieres eliminar <strong className="text-slate-800 dark:text-white">{cat.name}</strong>?
          {(cat.children?.length ?? 0) > 0 && (
            <> Esta accion tambien eliminara sus <strong>{cat.children!.length} subcategorias</strong>.</>
          )}
        </p>
        {error && (
          <p className="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
            {error}
          </p>
        )}
        <div className="mt-5 flex gap-2">
          <Button variant="outline" className="flex-1" onClick={onClose}>Cancelar</Button>
          <button
            onClick={confirm}
            disabled={deleting}
            className="flex-1 rounded-xl bg-red-500 py-2.5 text-[13px] font-semibold text-white transition-colors hover:bg-red-600 disabled:opacity-60"
          >
            {deleting ? 'Eliminando...' : 'Si, eliminar'}
          </button>
        </div>
      </div>
    </div>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardCategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [search, setSearch] = useState('')
  const [expanded, setExpanded] = useState<Set<number>>(new Set())
  const [modalMode, setModalMode] = useState<ModalMode>(null)
  const [editTarget, setEditTarget] = useState<Category | null>(null)
  const [deleteTarget, setDeleteTarget] = useState<Category | null>(null)

  // -- Data --------------------------------------------------------------------

  const load = async () => {
    setLoading(true)
    setLoadError('')
    try {
      const { data } = await API.get('/categories')
      setCategories(Array.isArray(data) ? data : data?.data ?? [])
    } catch (err: any) {
      setLoadError(err?.response?.data?.message || 'No se pudo cargar las categorias.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [])

  // -- Derived -----------------------------------------------------------------

  const flat = categories
  const tree = buildTree(flat)
  const rootCategories = flat.filter((c) => !c.parent_id)
  const totalProducts = flat.reduce((sum, c) => sum + (c.product_count ?? 0), 0)
  const active = flat.filter((c) => c.is_active).length

  const filtered = search.trim()
    ? flat.filter((c) =>
        c.name.toLowerCase().includes(search.toLowerCase()) ||
        (c.slug || '').toLowerCase().includes(search.toLowerCase())
      )
    : null

  // -- Actions -----------------------------------------------------------------

  const toggleExpanded = (id: number) =>
    setExpanded((prev) => {
      const next = new Set(prev)
      next.has(id) ? next.delete(id) : next.add(id)
      return next
    })

  const openCreate = () => {
    setEditTarget(null)
    setModalMode('create')
  }

  const openEdit = (cat: Category) => {
    setEditTarget(cat)
    setModalMode('edit')
  }

  const handleSave = async (form: typeof EMPTY_FORM & { id?: number }) => {
    const payload = {
      name: form.name.trim(),
      slug: form.slug.trim() || slugify(form.name),
      description: form.description.trim() || null,
      parent_id: form.parent_id,
      is_active: form.is_active,
    }
    if (form.id) {
      await API.put(`/categories/${form.id}`, payload)
    } else {
      await API.post('/categories', payload)
    }
    await load()
  }

  const handleDelete = async (cat: Category) => {
    await API.delete(`/categories/${cat.id}`)
    await load()
  }

  // -- Render ------------------------------------------------------------------

  const displayTree = filtered
    ? buildTree(filtered)
    : tree

  const hasData = flat.length > 0

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
          <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Categorias</h1>
        </div>
        <Button onClick={openCreate}>+ Nueva categoria</Button>
      </div>

      {/* Stats */}
      <div className="flex flex-wrap gap-3">
        <StatCard label="Total categorias" value={flat.length} />
        <StatCard label="Activas" value={active} accent />
        <StatCard label="Raiz" value={rootCategories.length} />
        <StatCard label="Productos asignados" value={totalProducts} />
      </div>

      {/* Error */}
      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
          <span>⚠️</span>
          <span>{loadError}</span>
          <button onClick={load} className="ml-auto text-[12px] underline opacity-70 hover:opacity-100">
            Reintentar
          </button>
        </div>
      )}

      {/* Main card */}
      <GlassCard className="overflow-hidden border border-slate-200/90 bg-gradient-to-br from-white via-white to-slate-50/70 p-0 shadow-[0_18px_45px_rgba(15,23,42,0.08)] dark:border-white/10 dark:bg-white/5">
        {/* Toolbar */}
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <div className="relative flex-1 min-w-[200px]">
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-white/30">🔎</span>
            <input
              type="search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Buscar por nombre o slug..."
              className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-[13px] text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/30"
            />
          </div>
          <button
            onClick={() => setExpanded(new Set(flat.map((c) => c.id)))}
            className="text-[12px] font-medium text-slate-500 hover:text-orange-500 dark:text-white/40 dark:hover:text-orange-400"
          >
            Expandir todo
          </button>
          <button
            onClick={() => setExpanded(new Set())}
            className="text-[12px] font-medium text-slate-500 hover:text-orange-500 dark:text-white/40 dark:hover:text-orange-400"
          >
            Colapsar todo
          </button>
        </div>

        {/* Table */}
        {loading ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-white/40">
            <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
            <p className="text-[13px]">Cargando categorias...</p>
          </div>
        ) : !hasData ? (
          <EmptyState onAdd={openCreate} />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 dark:border-white/5">
                  {['Categoria', 'Productos', 'Estado', 'Acciones'].map((h) => (
                    <th
                      key={h}
                      className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30"
                    >
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {displayTree.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-8 text-center text-[13px] text-slate-400 dark:text-white/30">
                      Sin resultados para "{search}"
                    </td>
                  </tr>
                ) : (
                  displayTree.map((cat) => (
                    <CategoryRow
                      key={cat.id}
                      cat={cat}
                      expanded={expanded}
                      onToggle={toggleExpanded}
                      onEdit={openEdit}
                      onDelete={setDeleteTarget}
                    />
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
      </GlassCard>

      {/* Modal create/edit */}
      {modalMode && (
        <CategoryModal
          mode={modalMode}
          initial={
            editTarget
              ? {
                  id: editTarget.id,
                  name: editTarget.name,
                  slug: editTarget.slug,
                  description: editTarget.description ?? '',
                  parent_id: editTarget.parent_id,
                  is_active: editTarget.is_active,
                }
              : EMPTY_FORM
          }
          parents={rootCategories}
          onClose={() => setModalMode(null)}
          onSave={handleSave}
        />
      )}

      {/* Delete confirm */}
      {deleteTarget && (
        <DeleteConfirm
          cat={deleteTarget}
          onClose={() => setDeleteTarget(null)}
          onConfirm={() => handleDelete(deleteTarget)}
        />
      )}
    </div>
  )
}


