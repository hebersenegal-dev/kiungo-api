<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue</title>
</head>
<body>
    <h1>Bonjour {{ $user->first_name ?? $user->email }},</h1>
    <p>Bienvenue sur notre plateforme ! Nous sommes ravis de vous compter parmi nous.</p>
    <p>Merci pour votre inscription.</p>
</body>
</html>
