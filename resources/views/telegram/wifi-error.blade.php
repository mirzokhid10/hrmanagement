<!DOCTYPE html>
<html>

<head>
    <title>WiFi Not Verified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h1 {
            font-size: 72px;
            margin: 0;
        }

        h2 {
            font-size: 28px;
            margin: 20px 0;
        }

        p {
            font-size: 18px;
            opacity: 0.9;
            margin: 10px 0;
        }

        code {
            background: rgba(0, 0, 0, 0.2);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <h1>❌</h1>
    <h2>WiFi Not Verified</h2>
    <p>{{ $message }}</p>
    @if (isset($ip))
        <p><code>Your IP: {{ $ip }}</code></p>
    @endif
    <p><small>Please connect to office WiFi and try again.</small></p>
</body>

</html>
