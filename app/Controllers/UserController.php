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
        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id)[0];
        $user->name = $_POST['name'];
        $user->email = $_POST['email'];

        $password = $user->password;
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
        $stmt->execute([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'id' => $id
        ]);

        header('Location: /user');
    }

    public function delete($id)
    {
        $this->authenticate();
        $userModel = new User(Database::getInstance());
        $user = $userModel->find($id)[0];

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Location: /user');
    }
}
