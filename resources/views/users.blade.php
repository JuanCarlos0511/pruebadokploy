<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRUD Usuarios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        #editSection {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧑‍💼 Gestión de Usuarios</h1>
        
        <div id="message" class="message"></div>

        <!-- Formulario de Crear Usuario -->
        <div class="form-section">
            <h2>Crear Nuevo Usuario</h2>
            <form id="createForm">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" id="create_name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="create_email" required>
                </div>
                <div class="form-group">
                    <label>Año:</label>
                    <input type="number" id="create_year" min="1900" max="2100" placeholder="Ej: 2000">
                </div>
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" id="create_password" required>
                </div>
                <button type="submit" class="btn btn-success">Crear Usuario</button>
            </form>
        </div>

        <!-- Formulario de Editar Usuario (oculto por defecto) -->
        <div class="form-section" id="editSection">
            <h2>Editar Usuario</h2>
            <form id="editForm">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Año:</label>
                    <input type="number" id="edit_year" min="1900" max="2100" placeholder="Ej: 2000">
                </div>
                <div class="form-group">
                    <label>Contraseña (dejar vacío para no cambiar):</label>
                    <input type="password" id="edit_password">
                </div>
                <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                <button type="button" class="btn btn-warning" onclick="cancelEdit()">Cancelar</button>
            </form>
        </div>

        <!-- Tabla de Usuarios -->
        <div>
            <h2>Lista de Usuarios</h2>
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Año</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersBody">
                    <!-- Los usuarios se cargarán aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Cargar usuarios al iniciar
        document.addEventListener('DOMContentLoaded', loadUsers);

        // Crear usuario
        document.getElementById('createForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                name: document.getElementById('create_name').value,
                email: document.getElementById('create_email').value,
                password: document.getElementById('create_password').value,
                year: document.getElementById('create_year').value
            };

            try {
                const response = await fetch('/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    showMessage('Usuario creado exitosamente', 'success');
                    document.getElementById('createForm').reset();
                    loadUsers();
                } else {
                    const error = await response.json();
                    showMessage('Error al crear usuario: ' + JSON.stringify(error), 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        });

        // Editar usuario
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit_id').value;
            const data = {
                name: document.getElementById('edit_name').value,
                email: document.getElementById('edit_email').value,
                year: document.getElementById('edit_year').value
            };

            const password = document.getElementById('edit_password').value;
            if (password) {
                data.password = password;
            }

            try {
                const response = await fetch(`/users/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    showMessage('Usuario actualizado exitosamente', 'success');
                    cancelEdit();
                    loadUsers();
                } else {
                    showMessage('Error al actualizar usuario', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        });

        // Cargar usuarios
        async function loadUsers() {
            try {
                const response = await fetch('/users');
                const users = await response.json();
                
                const tbody = document.getElementById('usersBody');
                tbody.innerHTML = '';

                users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.year || '-'}</td>
                        <td>${new Date(user.created_at).toLocaleDateString()}</td>
                        <td class="actions">
                            <button class="btn btn-primary" onclick="editUser(${user.id}, '${user.name}', '${user.email}', ${user.year || 'null'})">Editar</button>
                            <button class="btn btn-danger" onclick="deleteUser(${user.id})">Eliminar</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                showMessage('Error al cargar usuarios', 'error');
            }
        }

        // Preparar edición
        function editUser(id, name, email, year) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_year').value = year || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('editSection').style.display = 'block';
            document.getElementById('editSection').scrollIntoView({ behavior: 'smooth' });
        }

        // Cancelar edición
        function cancelEdit() {
            document.getElementById('editForm').reset();
            document.getElementById('editSection').style.display = 'none';
        }

        // Eliminar usuario
        async function deleteUser(id) {
            if (!confirm('¿Estás seguro de eliminar este usuario?')) {
                return;
            }

            try {
                const response = await fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    showMessage('Usuario eliminado exitosamente', 'success');
                    loadUsers();
                } else {
                    showMessage('Error al eliminar usuario', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        }

        // Mostrar mensajes
        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = text;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
