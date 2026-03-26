<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Seu acesso ao sistema</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Olá, {{ $name }}!</h2>

    <p>Seu acesso ao sistema foi criado com sucesso.</p>

    <p><strong>Login:</strong> {{ $email }}</p>
    <p><strong>Senha provisória:</strong> {{ $password }}</p>
    <p><strong>Acesso:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

    <p>Por segurança, recomendamos que você altere sua senha após o primeiro acesso.</p>

    <p>Atenciosamente,<br>Equipe</p>
</body>
</html>