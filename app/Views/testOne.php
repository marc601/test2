<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="jumbotron">
            <h1 class="display-4"><?php echo htmlspecialchars(string: $data['title']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($data['message']);
            ?></p>
            <h2 class="mt-5">Agregar Nueva Tarea</h2>
            <form action="/testOne/addTask" method="POST" class="mb-5">
                <div class="mb-3">
                    <label for="tittle" class="form-label">Título</label>
                    <input type="text" class="form-control" id="tittle" name="tittle" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="">Seleccione un estado</option>
                        <?php foreach ($data['statuses'] as $key => $value): ?>
                            <option value=<?php echo htmlspecialchars($key); ?>>
                                <?php echo htmlspecialchars($value); ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Agregar Tarea</button>
            </form>

            <h2 class="mt-5">Lista de Tareas</h2>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['taskManager'])): ?>

                        <?php foreach ($data['taskManager']->getAllTasks() as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task->getId() ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($task->getTittle() ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($task->getDescription() ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($task->getStatusString()) ?? ''; ?></td>
                                <td>
                                    <form action="/testOne/updateTaskStatus" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task->getId()); ?>">
                                        <input type="hidden" name="action"
                                            value="<?php echo htmlspecialchars($task::STATUS_DONE); ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Terminar tarea</button>
                                    </form>
                                    <form action="/testOne/updateTaskStatus" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task->getId()); ?>">
                                        <input type="hidden" name="action"
                                            value="<?php echo htmlspecialchars($task::STATUS_IN_PROGRESS); ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Iniciar Tarea</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay tareas para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <hr class="my-4">
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>