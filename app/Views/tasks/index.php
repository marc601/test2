<?php

use App\Models\Task;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/task">Administrador de Tareas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/user/index">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/testOne">Test Uno</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <form action="logout" method="POST">
                            <button type="submit"
                                class="btn btn-link nav-link">Logout(<?php echo htmlspecialchars($_SESSION['userName']); ?>)</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
        <a href="/task/create" class="btn btn-primary mb-3">Nuevo</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>Estatus</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['tasks'] as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task->id); ?></td>
                        <td><?php echo htmlspecialchars($task->title); ?></td>
                        <td><?php echo htmlspecialchars($data['statuses'][$task->status]); ?></td>
                        <td><?php echo htmlspecialchars($task->due_date ?? 'N/A'); ?></td>
                        <td>
                            <a href="/task/show/<?php echo $task->id; ?>" class="btn btn-info btn-sm">Detalle</a>
                            <a href="/task/edit/<?php echo $task->id; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <form action="/task/delete/<?php echo $task->id; ?>" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                            </form>
                            <form action="/task/start/<?php echo $task->id; ?>" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-success btn-sm">Iniciar</button>
                            </form>
                            <form action="/task/finish/<?php echo $task->id; ?>" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-primary btn-sm">Finalizar</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>