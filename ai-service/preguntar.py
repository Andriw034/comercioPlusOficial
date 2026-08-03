"""
Prueba rapida del asistente de IA de una tienda.

Uso (con el servicio corriendo en otra terminal):

    python preguntar.py "todo motos pipe" "que le sirve a una Boxer 2018?"

Si no pasas nada, usa valores de ejemplo.
"""
import sys
import json
import urllib.request

URL = "http://localhost:5000/ask"


def preguntar(store, question):
    body = json.dumps({"store_id": store, "question": question}).encode("utf-8")
    req = urllib.request.Request(
        URL, data=body, headers={"Content-Type": "application/json"}
    )
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            data = json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        print("ERROR", e.code, e.read().decode("utf-8"))
        return
    except Exception as e:
        print("No se pudo conectar al servicio. Esta corriendo 'python app.py'?")
        print("Detalle:", e)
        return

    if "error" in data:
        print("ERROR:", data["error"])
        return

    print("=" * 60)
    print("TIENDA:", data.get("store", {}).get("name", store))
    print("PREGUNTA:", data.get("question"))
    print("-" * 60)
    print(data.get("answer"))
    print("-" * 60)
    print(f"Productos encontrados en el catalogo: {data.get('products_found', 0)}")
    for p in data.get("products", []):
        print(f"   - {p['name']} (${p['price']}, stock {p['stock']})")
    tok = data.get("tokens", {})
    print(f"Tokens usados: entrada {tok.get('input')} / salida {tok.get('output')}")
    print("=" * 60)


if __name__ == "__main__":
    store = sys.argv[1] if len(sys.argv) > 1 else "todo motos pipe"
    question = sys.argv[2] if len(sys.argv) > 2 else "que repuestos tienes para Boxer?"
    preguntar(store, question)
