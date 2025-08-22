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
        <table class="table">
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($data['user']->id); ?></td>
            </tr>
            <tr>
                <th>Nombre</th>
                <td><?php echo htmlspecialchars($data['user']->name); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($data['user']->email); ?></td>
            </tr>
        </table>
        <a href="/user" class="btn btn-primary">Regresar</a>
    </div>
</body>

</html>