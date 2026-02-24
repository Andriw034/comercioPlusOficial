import { useCallback, useState } from 'react'
import {
  createFromScan,
  getInventoryMovements,
  getStoreCategories,
  scanInInventory,
  type CreateFromScanPayload,
  type InventoryMovementItem,
  type ScanInPayload,
} from '@/services/inventoryReceive'

export default function useInventoryReceive() {
  const [movements, setMovements] = useState<InventoryMovementItem[]>([])
  const [categories, setCategories] = useState<any[]>([])
  const [loadingMovements, setLoadingMovements] = useState(false)
  const [loadingCategories, setLoadingCategories] = useState(false)

  const loadMovements = useCallback(async (limit = 10, type: string | null = 'purchase') => {
    setLoadingMovements(true)
    try {
      const response = await getInventoryMovements(limit, type)
      setMovements(response.data || [])
      return response
    } finally {
      setLoadingMovements(false)
    }
  }, [])

  const loadCategories = useCallback(async () => {
    setLoadingCategories(true)
    try {
      const response = await getStoreCategories()
      setCategories(response)
      return response
    } finally {
      setLoadingCategories(false)
    }
  }, [])

  const scanIn = useCallback(async (payload: ScanInPayload) => {
    const response = await scanInInventory(payload)

    if (response?.data?.movement) {
      setMovements((previous) => [response.data.movement, ...previous].slice(0, 10))
    }

    return response
  }, [])

  const createByScan = useCallback(async (payload: CreateFromScanPayload) => {
    const response = await createFromScan(payload)

    if (response?.data?.movement) {
      setMovements((previous) => [response.data.movement, ...previous].slice(0, 10))
    }

    return response
  }, [])

  return {
    movements,
    categories,
    loadingMovements,
    loadingCategories,
    loadMovements,
    loadCategories,
    scanIn,
    createByScan,
  }
}
