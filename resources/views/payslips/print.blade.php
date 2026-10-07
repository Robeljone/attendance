@php
    $currency = $company->currency ?? 'USD';
    $earnings = $payslip->lines->where('type', App\Enums\PayslipLineType::Earning);
    $deductions = $payslip->lines->where('type', App\Enums\PayslipLineType::Deduction);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Payslip') }} — {{ $payslip->employee?->user?->name }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background: #eef2f7;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(8px);
        }
        .toolbar a, .toolbar button {
            appearance: none;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            border-radius: 0.5rem;
            padding: 0.5rem 0.9rem;
            font: 600 0.875rem/1.2 system-ui, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar button.primary {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }
        .sheet {
            width: min(820px, calc(100% - 2rem));
            margin: 1.5rem auto 2.5rem;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 2.25rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            border-bottom: 2px solid var(--ink);
            padding-bottom: 1.25rem;
        }
        .company h1 {
            margin: 0;
            font-size: 1.75rem;
            letter-spacing: -0.02em;
        }
        .company p, .meta p {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-family: system-ui, sans-serif;
            font-size: 0.875rem;
        }
        .meta { text-align: right; }
        .meta strong {
            display: block;
            font-size: 1.1rem;
            font-family: system-ui, sans-serif;
        }
        .employee {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1rem;
            background: var(--soft);
            border: 1px solid var(--line);
            font-family: system-ui, sans-serif;
            font-size: 0.9rem;
        }
        .employee span { color: var(--muted); display: block; font-size: 0.75rem; margin-bottom: 0.2rem; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-family: system-ui, sans-serif;
            font-size: 0.9rem;
        }
        th, td {
            padding: 0.7rem 0.4rem;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }
        th { color: var(--muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
        td.amount, th.amount { text-align: right; }
        .section-title {
            margin: 1.75rem 0 0;
            font-family: system-ui, sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .totals {
            margin-top: 1.5rem;
            display: grid;
            gap: 0.5rem;
            max-width: 280px;
            margin-left: auto;
            font-family: system-ui, sans-serif;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.35rem 0;
        }
        .totals .net {
            margin-top: 0.35rem;
            border-top: 2px solid var(--ink);
            padding-top: 0.75rem;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .footer {
            margin-top: 2.5rem;
            color: var(--muted);
            font-family: system-ui, sans-serif;
            font-size: 0.75rem;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet {
                width: 100%;
                margin: 0;
                border: 0;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ $backUrl }}">{{ __('Back') }}</a>
        <button class="primary" type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
    </div>

    <main class="sheet">
        <header class="header">
            <div class="company">
                <h1>{{ $company->company_name ?? config('app.name') }}</h1>
                @if (filled($company->tagline ?? null))
                    <p>{{ $company->tagline }}</p>
                @endif
                <p>{{ __('Payslip') }}</p>
            </div>
            <div class="meta">
                <strong>{{ $payslip->payrollPeriod?->name }}</strong>
                <p>
                    {{ $payslip->payrollPeriod?->start_date?->format('M j, Y') }}
                    –
                    {{ $payslip->payrollPeriod?->end_date?->format('M j, Y') }}
                </p>
                <p>{{ __('Status') }}: {{ $payslip->payrollPeriod?->status?->label() ?? '—' }}</p>
            </div>
        </header>

        <section class="employee">
            <div>
                <span>{{ __('Employee') }}</span>
                <strong>{{ $payslip->employee?->user?->name ?? '—' }}</strong>
            </div>
            <div>
                <span>{{ __('Employee number') }}</span>
                <strong>{{ $payslip->employee?->employee_number ?? '—' }}</strong>
            </div>
            <div>
                <span>{{ __('Department') }}</span>
                <strong>{{ $payslip->employee?->department?->name ?? '—' }}</strong>
            </div>
            <div>
                <span>{{ __('Position') }}</span>
                <strong>{{ $payslip->employee?->position ?? '—' }}</strong>
            </div>
        </section>

        <p class="section-title">{{ __('Earnings') }}</p>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="amount">{{ __('Amount') }} ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($earnings as $line)
                    <tr>
                        <td>{{ $line->label }}</td>
                        <td class="amount">{{ number_format((float) $line->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="section-title">{{ __('Deductions') }}</p>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="amount">{{ __('Amount') }} ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($deductions as $line)
                    <tr>
                        <td>{{ $line->label }}</td>
                        <td class="amount">{{ number_format((float) $line->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">{{ __('No deductions') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <div>
                <span>{{ __('Gross') }}</span>
                <strong>{{ number_format((float) ($payslip->breakdown['gross'] ?? $payslip->grossPay()), 2) }}</strong>
            </div>
            <div>
                <span>{{ __('Deductions') }}</span>
                <strong>{{ number_format((float) $payslip->deductions, 2) }}</strong>
            </div>
            <div class="net">
                <span>{{ __('Net pay') }}</span>
                <span>{{ number_format((float) $payslip->net_pay, 2) }} {{ $currency }}</span>
            </div>
        </div>

        <p class="footer">
            {{ __('Attendance') }}:
            {{ __('Present') }} {{ $payslip->present_days }},
            {{ __('Absent') }} {{ $payslip->absent_days }},
            {{ __('Leave') }} {{ $payslip->leave_days }}.
            {{ __('Generated for') }} {{ $company->company_name ?? config('app.name') }}.
        </p>
    </main>
</body>
</html>
