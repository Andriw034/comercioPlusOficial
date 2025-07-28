<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tienda - ComercioPlus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #fff7ed, #ffe8cc);
            position: relative;
            overflow: hidden;
        }

        .wave {
            position: absolute;
            width: 200%;
            height: 100%;
            left: -50%;
            background: radial-gradient(circle, rgba(255,115,0,0.1) 30%, transparent 70%);
            animation: moveWaves 8s ease-in-out infinite;
        }

        .wave:nth-child(1) {
            top: 0;
            animation-delay: 0s;
        }

        .wave:nth-child(2) {
            top: 50%;
            animation-delay: 4s;
        }

        @keyframes moveWaves {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            max-width: 450px;
            width: 100%;
            padding: 2rem;
        }

        h1 {
            color: #FF6000;
        }

        .input-field input, .input-field textarea, .input-field button {
            border-radius: 8px;
        }

        .input-field label {
            color: #333;
            font-size: 0.9rem;
        }

        .input-field input, .input-field textarea {
            border: 1px solid #ddd;
            padding: 0.75rem;
            width: 100%;
            margin-top: 0.5rem;
        }

        .input-field button {
            background-color: #FF6000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .input-field button:hover {
            background-color: #FF4500;
        }

    </style>
</head>

<body>
    <div class="wave"></div>
    <div class="wave"></div>

    <div class="min-h-screen flex items-center justify-center">
        <div class="form-container">
            <h1 class="text-2xl font-bold text-center mb-6">Crear Tienda</h1>

            <form method="POST" action="{{ route('store.create.post') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Nombre de la tienda -->
                <div class="input-field">
                    <label for="name">Nombre de la tienda</label>
                    <input type="text" name="name" id="name" required placeholder="Nombre de tu tienda" class="mt-1">
                </div>

                <!-- Descripción -->
                <div class="input-field">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3" placeholder="Descripción breve de la tienda" class="mt-1"></textarea>
                </div>

                <!-- Logo -->
                <div class="input-field">
                    <label for="logo">Logo</label>
                    <input type="file" name="logo" id="logo" accept="image/*" class="mt-1 text-sm text-gray-600 file:py-2 file:px-4 file:rounded-md file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
                </div>

                <!-- Portada -->
                <div class="input-field">
                    <label for="cover">Portada</label>
                    <input type="file" name="cover" id="cover" accept="image/*" class="mt-1 text-sm text-gray-600 file:py-2 file:px-4 file:rounded-md file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
                </div>

                <!-- Botón -->
                <div class="flex justify-end">
                    <button type="submit">Guardar y continuar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
