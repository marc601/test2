<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($data['title']); ?></h1>
        <form action="/task/update/<?php echo $data['task']->id; ?>" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Titulo</label>
                <input type="text" class="form-control" id="title" name="title"
                    value="<?php echo htmlspecialchars($data['task']->title); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description"
                    name="description"><?php echo htmlspecialchars($data['task']->description); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Estatus</label>
                <select class="form-control" id="status" name="status">
                    <?php foreach ($data['statuses'] as $key => $status): ?>
                        <option value="<?php echo $key; ?>"><?php echo $status; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
</body>

</html>