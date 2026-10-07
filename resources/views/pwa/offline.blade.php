<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#4f46e5">
        <title>{{ __('Offline') }} — {{ config('app.name', 'Attendance HR') }}</title>
        <style>
            :root { color-scheme: light; }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
                background: #f1f5f9;
                color: #0f172a;
                padding: 1.5rem;
            }
            .card {
                width: min(100%, 24rem);
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                padding: 1.75rem;
                box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
                text-align: center;
            }
            h1 { margin: 0 0 0.5rem; font-size: 1.25rem; }
            p { margin: 0 0 1.25rem; color: #64748b; font-size: 0.95rem; line-height: 1.5; }
            button {
                appearance: none;
                border: 0;
                border-radius: 0.5rem;
                background: #4f46e5;
                color: #fff;
                font: inherit;
                font-weight: 600;
                padding: 0.65rem 1rem;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>{{ __('You are offline') }}</h1>
            <p>{{ __('Reconnect to the network, then try again. Attendance and HR actions need an internet connection.') }}</p>
            <button type="button" onclick="window.location.reload()">{{ __('Retry') }}</button>
        </div>
    </body>
</html>
