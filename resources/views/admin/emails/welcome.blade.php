<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 20px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $employee->company->name }}</h1>
        </div>
        <div class="content">
            {{-- English Content --}}
            @if ($language === 'en')
                <h2>Hello, {{ $employee->first_name }}!</h2>
                <p>We are thrilled to have you join us at <strong>{{ $employee->company->name }}</strong>.</p>
                <p>Your position: <strong>{{ $employee->job_title }}</strong><br>
                    Start Date: <strong>{{ $employee->hire_date->format('d M Y') }}</strong></p>
                <p>Please report to the HR department on your first day at 09:00 AM.</p>
                <p>Best Regards,<br>The HR Team</p>

                {{-- Russian Content --}}
            @elseif($language === 'ru')
                <h2>Здравствуйте, {{ $employee->first_name }}!</h2>
                <p>Мы рады приветствовать вас в <strong>{{ $employee->company->name }}</strong>.</p>
                <p>Ваша должность: <strong>{{ $employee->job_title }}</strong><br>
                    Дата начала: <strong>{{ $employee->hire_date->format('d.m.Y') }}</strong></p>
                <p>Пожалуйста, подойдите в отдел кадров в ваш первый рабочий день к 09:00.</p>
                <p>С уважением,<br>HR Команда</p>

                {{-- Uzbek Content (Default) --}}
            @else
                <h2>Assalomu alaykum, {{ $employee->first_name }}!</h2>
                <p>Sizni <strong>{{ $employee->company->name }}</strong> jamoasida ko'rib turganimizdan xursandmiz.</p>
                <p>Lavozimingiz: <strong>{{ $employee->job_title }}</strong><br>
                    Ish boshlash sanasi: <strong>{{ $employee->hire_date->format('d.m.Y') }}</strong></p>
                <p>Iltimos, birinchi ish kuningizda soat 09:00 da Kadrlar bo'limiga uchrashing.</p>
                <p>Hurmat bilan,<br>HR Jamoasi</p>
            @endif
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $employee->company->name }}. All rights reserved.
        </div>
    </div>
</body>

</html>
