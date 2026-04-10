"""
Cliente para Mercado Libre API Oficial (Colombia)
https://developers.mercadolibre.com

No requiere autenticacion para busquedas publicas.
Categoria MCO1747 = Accesorios para Vehiculos > Motos > Repuestos
"""

import requests
from typing import List, Dict


class MercadoLibreAPI:
    """Cliente oficial de Mercado Libre API"""

    BASE_URL = "https://api.mercadolibre.com"
    SITE_ID = "MCO"  # Colombia
    CATEGORY = "MCO1747"  # Repuestos motos
    TIMEOUT = 5

    def search(self, query: str, limit: int = 5) -> List[Dict]:
        """
        Buscar productos en Mercado Libre Colombia.

        Args:
            query: Termino de busqueda (ej: "banda yamaha ybr 125")
            limit: Maximo resultados (1-50)

        Returns:
            Lista de productos simplificados
        """
        try:
            url = f"{self.BASE_URL}/sites/{self.SITE_ID}/search"
            params = {
                "q": query,
                "category": self.CATEGORY,
                "limit": min(limit, 50),
            }

            resp = requests.get(url, params=params, timeout=self.TIMEOUT)
            resp.raise_for_status()
            data = resp.json()

            results = []
            for item in data.get("results", []):
                price = item.get("price", 0)
                results.append(
                    {
                        "title": item.get("title", ""),
                        "price": price,
                        "currency": item.get("currency_id", "COP"),
                        "permalink": item.get("permalink", ""),
                        "thumbnail": item.get("thumbnail", ""),
                        "condition": item.get("condition", "new"),
                        "seller": item.get("seller", {}).get("nickname", ""),
                    }
                )

            return results

        except requests.RequestException as exc:
            print(f"[MELI API] Error: {exc}")
            return []
