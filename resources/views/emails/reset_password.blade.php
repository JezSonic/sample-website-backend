<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weryfikacja adresu e-mail</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Resetowanie hasła w {{ config('app.name') }}</h1>

    <p>Przyjęliśmy zgłoszenie resetowania zapomnianego hasła. </p>

    <p style="text-align: center;">
        <a href="{{ $reset_password_url }}" class="button">Zmień swoje hasło</a>
    </p>
    <p><i>Podany link jest ważny do {{ $valid_until }}</i></p>
    <p>Jeśli przycisk nie działa, skopiuj i wklej poniższy link do paska adresu przeglądarki:</p>
    <p><a href="{{ $reset_password_url }}">{{ $reset_password_url }}</a></p>

    <p>Jeśli nie prosiłeś o zresetowanie hasła dla swojego konta w {{ config('app.name') }}, możesz bezpiecznie zignorować tę wiadomość.</p>

    <p>Dziękujemy,<br>Zespół {{ config('app.name') }}</p>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Wszelkie prawa zastrzeżone.</p>
    </div>
</div>
</body>
</html>
