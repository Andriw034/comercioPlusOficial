from flask import Flask, request, jsonify
from flask_cors import CORS
from anthropic import Anthropic
import mysql.connector
import os
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

app = Flask(__name__)
CORS(app)  # Permitir requests desde React frontend

# Cliente Anthropic
anthropic_client = Anthropic(api_key=os.getenv('ANTHROPIC_API_KEY'))

def get_db_connection():
    """Crea conexión a MySQL"""
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', '127.0.0.1'),
        port=int(os.getenv('DB_PORT', '3306')),
        database=os.getenv('DB_NAME', 'comercioPlus'),
        user=os.getenv('DB_USER', 'root'),
        password=os.getenv('DB_PASSWORD', '')
    )

def search_parts(query):
    """
    Búsqueda simple de repuestos en MySQL
    """
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    search_term = f'%{query.lower()}%'

    cursor.execute("""
        SELECT
            part_reference,
            part_type,
            part_brand,
            part_description,
            motorcycle_brand,
            motorcycle_model,
            year_from,
            year_to,
            notes
        FROM parts_compatibility
        WHERE
            LOWER(part_reference) LIKE %s OR
            LOWER(motorcycle_model) LIKE %s OR
            LOWER(part_type) LIKE %s OR
            LOWER(motorcycle_brand) LIKE %s
        ORDER BY
            motorcycle_brand,
            motorcycle_model
        LIMIT 15
    """, (search_term, search_term, search_term, search_term))

    results = cursor.fetchall()
    cursor.close()
    conn.close()

    return results

def build_context(parts):
    """Construye contexto para Claude"""
    if not parts:
        return "No se encontraron repuestos en la base de datos."

    context_lines = []
    for part in parts:
        line = (
            f"- {part['part_reference']} ({part['part_brand']}): "
            f"Compatible con {part['motorcycle_brand']} {part['motorcycle_model']} "
            f"{part['year_from']}-{part['year_to']}"
        )
        if part['notes']:
            line += f" - {part['notes']}"
        context_lines.append(line)

    return "\n".join(context_lines)

def ask_claude(question, parts_context):
    """Llama a Claude API"""

    prompt = f"""Eres un asistente experto en repuestos de motos en Colombia.

Tu trabajo es ayudar a vendedores de repuestos a responder preguntas de clientes.

IMPORTANTE:
- Usa lenguaje colombiano natural
- Se especifico con referencias
- Si hay varias opciones, menciona TODAS
- Explica POR QUE ese repuesto es compatible
- Si NO encuentras info, dilo y sugiere verificar con distribuidor

PREGUNTA DEL VENDEDOR:
{question}

REPUESTOS DISPONIBLES:
{parts_context}

RESPONDE de forma clara y util:"""

    try:
        message = anthropic_client.messages.create(
            model="claude-sonnet-4-20250514",
            max_tokens=800,
            messages=[{
                "role": "user",
                "content": prompt
            }]
        )

        return {
            'answer': message.content[0].text,
            'tokens_input': message.usage.input_tokens,
            'tokens_output': message.usage.output_tokens
        }

    except Exception as e:
        return {
            'answer': f"Error al consultar IA: {str(e)}",
            'tokens_input': 0,
            'tokens_output': 0
        }

@app.route('/health', methods=['GET'])
def health():
    """Health check"""
    return jsonify({
        'status': 'ok',
        'service': 'ComercioPlus AI Service',
        'version': '1.0.0'
    })

@app.route('/ask', methods=['POST'])
def ask():
    """
    Endpoint principal para preguntas

    Body: {"question": "Que banda le sirve a Viva R 2018?"}
    """
    try:
        data = request.get_json()

        if not data or 'question' not in data:
            return jsonify({'error': 'Falta campo "question"'}), 400

        question = data['question']

        # 1. Buscar repuestos
        parts = search_parts(question)

        # 2. Construir contexto
        context = build_context(parts)

        # 3. Preguntar a Claude
        claude_response = ask_claude(question, context)

        # 4. Retornar respuesta
        return jsonify({
            'question': question,
            'answer': claude_response['answer'],
            'parts_found': len(parts),
            'parts': parts[:5],
            'tokens': {
                'input': claude_response['tokens_input'],
                'output': claude_response['tokens_output']
            }
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/search', methods=['POST'])
def search():
    """Busqueda directa sin IA (gratis)"""
    try:
        data = request.get_json()

        if not data or 'query' not in data:
            return jsonify({'error': 'Falta campo "query"'}), 400

        query = data['query']
        parts = search_parts(query)

        return jsonify({
            'query': query,
            'results': parts,
            'count': len(parts)
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/import-csv', methods=['POST'])
def import_csv():
    """
    Importar repuestos desde archivo CSV.

    Espera multipart/form-data con campo 'file' (CSV).
    Columnas requeridas: part_reference, part_type, part_brand,
      motorcycle_brand, motorcycle_model, year_from, year_to
    Columnas opcionales: part_description, notes
    """
    import csv, io

    if 'file' not in request.files:
        return jsonify({'error': 'Falta archivo CSV (campo "file")'}), 400

    file = request.files['file']
    if not file.filename or not file.filename.endswith('.csv'):
        return jsonify({'error': 'El archivo debe ser .csv'}), 400

    stream = io.StringIO(file.stream.read().decode('utf-8-sig'))
    reader = csv.DictReader(stream)

    required = {'part_reference', 'part_type', 'part_brand',
                'motorcycle_brand', 'motorcycle_model', 'year_from', 'year_to'}

    if not required.issubset(set(reader.fieldnames or [])):
        return jsonify({
            'error': f'Columnas requeridas: {", ".join(sorted(required))}',
            'received': reader.fieldnames,
        }), 400

    conn = get_db_connection()
    cursor = conn.cursor()
    inserted = 0
    skipped = 0
    errors_list = []

    for i, row in enumerate(reader, start=2):
        try:
            cursor.execute(
                "SELECT 1 FROM parts_compatibility "
                "WHERE part_reference=%s AND motorcycle_brand=%s AND motorcycle_model=%s AND year_from=%s",
                (row['part_reference'], row['motorcycle_brand'],
                 row['motorcycle_model'], int(row['year_from'])),
            )
            if cursor.fetchone():
                skipped += 1
                continue

            cursor.execute(
                "INSERT INTO parts_compatibility "
                "(part_reference, part_type, part_brand, part_description, "
                " motorcycle_brand, motorcycle_model, year_from, year_to, notes, "
                " created_at, updated_at) "
                "VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW(),NOW())",
                (
                    row['part_reference'], row['part_type'], row['part_brand'],
                    row.get('part_description', ''),
                    row['motorcycle_brand'], row['motorcycle_model'],
                    int(row['year_from']), int(row['year_to']),
                    row.get('notes', ''),
                ),
            )
            inserted += 1
        except Exception as exc:
            errors_list.append(f"Fila {i}: {exc}")

    conn.commit()
    cursor.close()
    conn.close()

    return jsonify({
        'inserted': inserted,
        'skipped': skipped,
        'errors': errors_list[:20],
        'total_rows': inserted + skipped + len(errors_list),
    })


@app.route('/search-marketplace', methods=['POST'])
def search_marketplace():
    """Buscar en Mercado Libre API oficial (complementario a BD propia)"""
    try:
        data = request.get_json()
        query = (data or {}).get('query', '')

        if not query:
            return jsonify({'error': 'Falta campo "query"'}), 400

        from services.meli_api import MercadoLibreAPI
        meli = MercadoLibreAPI()
        results = meli.search(query, limit=5)

        return jsonify({
            'query': query,
            'count': len(results),
            'products': results,
            'source': 'Mercado Libre API Oficial',
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    # Verificar API key
    if not os.getenv('ANTHROPIC_API_KEY'):
        print("WARNING: ANTHROPIC_API_KEY no configurada")
        print("   Crea archivo .env con tu API key")

    print("ComercioPlus AI Service iniciando...")
    print("http://localhost:5000")
    print("Health: http://localhost:5000/health")
    print("Endpoints: /search, /ask, /search-marketplace")

    app.run(host='0.0.0.0', port=5000, debug=True)
