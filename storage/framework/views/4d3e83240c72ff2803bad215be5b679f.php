<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .container {
            background: #f5f5f5;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .errors {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .errors h3 {
            margin-top: 0;
            color: #856404;
        }
        .errors pre {
            background: white;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Importar CSV de Clientes e Dependentes</h1>
        
        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.csv.import')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="csv_file">Selecione o arquivo CSV:</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            </div>
            <button type="submit">Importar</button>
        </form>

        <?php if(session('errors_log')): ?>
            <div class="errors">
                <h3>Erros encontrados durante a importação:</h3>
                <pre><?php echo e(session('errors_log')); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html><?php /**PATH /var/www/html/resources/views/pages/import.blade.php ENDPATH**/ ?>