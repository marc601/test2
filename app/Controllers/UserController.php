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
            'users' => $users,

        ];
        $this->render('users/index', $data);
    }

    public function show($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id);
        $data = [
            'title' => 'Detalles',
            'user' => $user
        ];
        $this->render('users/show', $data);
    }

    public function create()
    {
        $this->authenticate();
        $data = [
            'title' => 'Crear Usuario',
            'user' => new User(null),
            'errors' => []
        ];
        $this->render('users/create', $data);
    }

    public function store()
    {
        $this->authenticate();
        $user = new User(Database::getInstance());
        $user->name = trim($_POST['name'] ?? '');
        $user->email = trim($_POST['email'] ?? '');
        $user->password = $_POST['password'] ?? '';

        $errors = $user->validate();

        if (empty($errors)) {
            // Hash password before saving
            $user->password = password_hash($user->password, PASSWORD_BCRYPT);
            $user->saveRecord();
            $this->redirect('/user');
        }

        // If validation fails, re-render form with errors
        $data = [
            'title' => 'Crear Usuario',
            'user' => $user,
            'errors' => $errors
        ];
        $this->render('users/create', $data);
    }

    public function edit($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id);
        $data = [
            'title' => 'Edit User',
            'user' => $user
        ];
        $this->render('users/edit', $data);
    }

    public function update($id)
    {
        $this->authenticate();

        // 1. Añadir Autorización: Un usuario solo puede editarse a sí mismo (o un admin)
        if ($_SESSION['user_id'] != $id) {
            // Idealmente, redirigir a una página de error 403 Forbidden
            $this->redirect('/user');
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id); // Asumiendo que findById devuelve un objeto

        if (!$user) {
            $this->redirect('/user'); // O a una página 404
        }

        // 2. Mapear y Validar los datos
        $user->name = trim($_POST['name'] ?? '');
        $user->email = trim($_POST['email'] ?? '');

        // 3. Hashear la contraseña si se ha proporcionado una nueva
        if (!empty($_POST['password'])) {
            $user->password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $errors = $user->validate(true);

        if (empty($errors)) {
            $user->saveRecord();
            $this->redirect('/user');
        }

        // If validation fails, re-render the edit form
        $data = [
            'title' => 'Editar Usuario',
            'user' => $user,
            'errors' => $errors
        ];
        $this->render('users/edit', $data);
    }

    public function delete($id)
    {
        $this->authenticate();

        if ($_SESSION['user_id'] != $id) {
            $this->redirect('/user');
        }

        $userModel = new User(Database::getInstance());
        $user = $userModel->findById($id);

        if ($user) {
            $userModel->deleteRecord($id);
        }

        // If user deletes themselves, log them out
        session_destroy();
        $this->redirect('/user');
    }
}
