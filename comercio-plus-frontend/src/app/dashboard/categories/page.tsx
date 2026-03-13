import { useEffect, useMemo, useRef, useState } from 'react'
import API from '@/lib/api'
import Input from '@/components/ui/Input'
import Textarea from '@/components/ui/Textarea'
import Switch from '@/components/ui/Switch'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'

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

type CategoryForm = typeof EMPTY_FORM & { id?: number }
type TreeAction = '' | 'expand' | 'collapse'

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
  cats.forEach((c) => {
    map[c.id] = { ...c, children: [] }
  })
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
      <ErpBtn className="mt-5" onClick={onAdd} variant="primary" size="md">
        + Nueva categoria
      </ErpBtn>
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
              {cat.slug ? <p className="text-[11px] font-mono text-slate-400 dark:text-white/30">/{cat.slug}</p> : null}
            </div>
            {hasChildren ? (
              <span className="ml-1 rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-white/10 dark:text-white/40">
                {cat.children!.length}
              </span>
            ) : null}
          </div>
        </td>

        <td className="px-3 py-3 text-[13px] text-slate-600 dark:text-white/60">
          <span className="font-semibold text-slate-900 dark:text-white">{cat.product_count}</span>{' '}
          producto{cat.product_count !== 1 ? 's' : ''}
        </td>

        <td className="px-3 py-3">
          <ErpBadge status={cat.is_active ? 'active' : 'inactive'} label={cat.is_active ? 'Activa' : 'Oculta'} />
        </td>

        <td className="pl-3 pr-4 py-3">
          <div className="flex items-center gap-2">
            <ErpBtn onClick={() => onEdit(cat)} variant="secondary" size="sm">
              ✏️ Editar
            </ErpBtn>
            <ErpBtn onClick={() => onDelete(cat)} variant="danger" size="sm">
              🗑️ Eliminar
            </ErpBtn>
          </div>
        </td>
      </tr>

      {isExpanded
        ? cat.children?.map((child) => (
            <CategoryRow
              key={child.id}
              cat={child}
              depth={depth + 1}
              expanded={expanded}
              onToggle={onToggle}
              onEdit={onEdit}
              onDelete={onDelete}
            />
          ))
        : null}
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
  initial: CategoryForm
  parents: Category[]
  onClose: () => void
  onSave: (data: CategoryForm) => Promise<void>
}) {
  const [form, setForm] = useState<CategoryForm>(initial)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const nameRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    setTimeout(() => nameRef.current?.focus(), 50)
  }, [])

  const setField = <K extends keyof typeof EMPTY_FORM>(key: K, value: (typeof EMPTY_FORM)[K]) => {
    setForm((prev) => {
      const next = { ...prev, [key]: value }
      if (key === 'name' && mode === 'create') next.slug = slugify(String(value))
      return next
    })
  }

  const submit = async (event: React.FormEvent) => {
    event.preventDefault()
    if (!form.name.trim()) {
      setError('El nombre es requerido.')
      return
    }
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
            onChange={(event) => setField('name', event.target.value)}
            placeholder="Ej: Cascos"
          />
          <Input
            label="Slug"
            value={form.slug}
            onChange={(event) => setField('slug', event.target.value)}
            placeholder="ej: cascos"
          />
          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">
              Categoria padre (opcional)
            </label>
            <select
              value={form.parent_id ?? ''}
              onChange={(event) => setField('parent_id', event.target.value ? Number(event.target.value) : null)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
              <option value="">-- Sin padre (raiz) --</option>
              {parents
                .filter((p) => p.id !== initial.id)
                .map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
            </select>
          </div>
          <Textarea
            label="Descripcion"
            rows={2}
            value={form.description}
            onChange={(event) => setField('description', event.target.value)}
            placeholder="Descripcion breve (opcional)"
          />

          <label className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
            <span className="text-[13px] font-medium text-slate-700 dark:text-white/80">Visible al publico</span>
            <Switch checked={form.is_active} onCheckedChange={(value) => setField('is_active', value)} size="sm" />
          </label>

          {error ? (
            <p className="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
              {error}
            </p>
          ) : null}

          <div className="flex gap-2 pt-1">
            <ErpBtn type="button" variant="secondary" className="flex-1 justify-center" onClick={onClose}>
              Cancelar
            </ErpBtn>
            <ErpBtn type="submit" variant="primary" className="flex-1 justify-center" disabled={saving}>
              {saving ? 'Guardando...' : mode === 'create' ? 'Crear categoria' : 'Guardar cambios'}
            </ErpBtn>
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
        <div className="mb-4 flex items-center gap-2">
          <ErpBadge status="critical" label="Accion irreversible" />
        </div>

        <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Eliminar categoria</h2>
        <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
          Seguro que quieres eliminar <strong className="text-slate-800 dark:text-white">{cat.name}</strong>?
          {(cat.children?.length ?? 0) > 0 ? (
            <> Esta accion tambien eliminara sus <strong>{cat.children!.length} subcategorias</strong>.</>
          ) : null}
        </p>

        {error ? (
          <p className="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
            {error}
          </p>
        ) : null}

        <div className="mt-5 flex gap-2">
          <ErpBtn variant="secondary" className="flex-1 justify-center" onClick={onClose}>
            Cancelar
          </ErpBtn>
          <ErpBtn variant="danger" className="flex-1 justify-center" onClick={confirm} disabled={deleting}>
            {deleting ? 'Eliminando...' : 'Si, eliminar'}
          </ErpBtn>
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
  const [treeAction, setTreeAction] = useState<TreeAction>('')
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

  useEffect(() => {
    load()
  }, [])

  // -- Derived -----------------------------------------------------------------

  const flat = categories
  const tree = useMemo(() => buildTree(flat), [flat])
  const rootCategories = useMemo(() => flat.filter((c) => !c.parent_id), [flat])
  const totalProducts = useMemo(() => flat.reduce((sum, c) => sum + (c.product_count ?? 0), 0), [flat])
  const active = useMemo(() => flat.filter((c) => c.is_active).length, [flat])

  const filtered = useMemo(
    () =>
      search.trim()
        ? flat.filter(
            (c) =>
              c.name.toLowerCase().includes(search.toLowerCase()) ||
              (c.slug || '').toLowerCase().includes(search.toLowerCase()),
          )
        : null,
    [flat, search],
  )

  // -- Actions -----------------------------------------------------------------

  const toggleExpanded = (id: number) =>
    setExpanded((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
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

  const handleSave = async (form: CategoryForm) => {
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

  const applyTreeAction = (value: string) => {
    const next = (value || '') as TreeAction
    setTreeAction(next)
    if (next === 'expand') {
      setExpanded(new Set(flat.map((category) => category.id)))
    }
    if (next === 'collapse') {
      setExpanded(new Set())
    }
  }

  // -- Render ------------------------------------------------------------------

  const displayTree = filtered ? buildTree(filtered) : tree
  const hasData = flat.length > 0

  return (
    <div className="space-y-6">
      <ErpPageHeader
        breadcrumb="Dashboard"
        title="Categorias"
        subtitle="Organiza tu catalogo por jerarquias"
        actions={
          <>
            <ErpBtn variant="secondary" size="md" onClick={load}>
              Recargar
            </ErpBtn>
            <ErpBtn variant="primary" size="md" onClick={openCreate}>
              + Nueva categoria
            </ErpBtn>
          </>
        }
      />

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Total categorias"
          value={flat.length}
          hint="Categorias creadas"
          icon="folder"
          iconBg="rgba(59,130,246,0.12)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="Activas"
          value={active}
          hint="Visibles al publico"
          icon="check-circle"
          iconBg="rgba(16,185,129,0.12)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Categorias raiz"
          value={rootCategories.length}
          hint="Nivel principal"
          icon="grid"
          iconBg="rgba(139,92,246,0.12)"
          iconColor="#8B5CF6"
        />
        <ErpKpiCard
          label="Productos asignados"
          value={totalProducts}
          hint="Suma por categoria"
          icon="package"
          iconBg="rgba(255,161,79,0.12)"
          iconColor="#FFA14F"
        />
      </div>

      {loadError ? (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
          <ErpBadge status="rejected" label="Error" />
          <span>{loadError}</span>
          <ErpBtn variant="ghost" size="sm" className="ml-auto !text-red-700" onClick={load}>
            Reintentar
          </ErpBtn>
        </div>
      ) : null}

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-[0_10px_28px_rgba(15,23,42,0.07)] dark:border-white/10 dark:bg-white/5">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <div className="min-w-[220px] flex-1">
            <ErpSearchBar
              value={search}
              onChange={(value: string) => setSearch(value)}
              placeholder="Buscar por nombre o slug..."
            />
          </div>
          <ErpFilterSelect
            value={treeAction}
            onChange={applyTreeAction}
            options={[
              { value: 'expand', label: 'Expandir todo' },
              { value: 'collapse', label: 'Colapsar todo' },
            ]}
            placeholder="Arbol"
          />
        </div>

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
                  {['Categoria', 'Productos', 'Estado', 'Acciones'].map((header) => (
                    <th
                      key={header}
                      className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30"
                    >
                      {header}
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
      </div>

      {modalMode ? (
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
      ) : null}

      {deleteTarget ? (
        <DeleteConfirm
          cat={deleteTarget}
          onClose={() => setDeleteTarget(null)}
          onConfirm={() => handleDelete(deleteTarget)}
        />
      ) : null}
    </div>
  )
}
