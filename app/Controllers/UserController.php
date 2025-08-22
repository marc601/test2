<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\User;

class UserController extends AbstractController
{
    public function index()
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $users = $userModel->find();
        $data = [
            'title' => 'Usuarios',
            'users' => $users
        ];
        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function show($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);
        $data = [
            'title' => 'Detalles',
            'user' => $user[0] // find returns an array
        ];
        require_once __DIR__ . '/../Views/users/show.php';
    }

    public function create()
    {
        $this->authenticate();
        $data = [
            'title' => 'Crear Usuario'
        ];
        require_once __DIR__ . '/../Views/users/create.php';
    }

    public function store()
    {
        $this->authenticate();
        $user = new User(Database::getInstance());
        $user->name = $_POST['name'];
        $user->email = $_POST['email'];
        $user->password = $_POST['password']; // The model will hash it
        $user->saveRecord();
        header('Location: /user');
    }

    public function edit($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id);
        $data = [
            'title' => 'Edit User',
            'user' => $user[0] // find returns an array
        ];
        require_once __DIR__ . '/../Views/users/edit.php';
    }

    public function update($id)
    {
        $this->authenticate();

        // 1. Añadir Autorización: Un usuario solo puede editarse a sí mismo (o un admin)
        if ($_SESSION['user_id'] != $id) {
            // Idealmente, redirigir a una página de error 403 Forbidden
            header('Location: /user');
            exit;
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id); // Asumiendo que findById devuelve un objeto

        if (!$user) {
            header('Location: /user'); // O a una página 404
            exit;
        }

        // 2. Mapear y Validar los datos
        $user->name = trim($_POST['name']);
        $user->email = trim($_POST['email']);

        // 3. Hashear la contraseña si se ha proporcionado una nueva
        if (!empty($_POST['password'])) {
            $user->password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        // 4. Usar el método del modelo para guardar (elimina SQL del controlador)
        $user->saveRecord();

        header('Location: /user');
    }

    public function delete($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Location: /user');
    }
}
