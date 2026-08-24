<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .card { background-color: #1e293b; padding: 24px; border-radius: 8px; border: 1px solid #334155; }
        .footer { font-size: 12px; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Hola, {{ $user->name }}</h2>
        <div>{!! nl2br(e($messageBody)) !!}</div>
        <div class="footer">
            Enviado desde CorRol.
        </div>
    </div>
</body>
</html>