<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('QR attendance station') }}</h2>
    </x-slot>

    <x-ui.page width="md">
        <x-ui.card>
            <div class="text-center">
                <p class="text-lg font-medium text-gray-900">{{ $stationName ?? config('app.name') }}</p>
                <p class="ui-card-subtitle mt-1">{{ __('Scan this code to clock in or out') }}</p>

                <div id="qr-code" class="mt-8 flex min-h-[280px] items-center justify-center"></div>

                <p class="mt-6 text-sm text-gray-600">
                    {{ __('Refreshes in') }} <span id="qr-countdown" class="font-semibold tabular-nums">—</span>s
                </p>
                <p id="qr-status" class="mt-2 text-xs text-gray-400"></p>
            </div>
        </x-ui.card>
    </x-ui.page>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        window.qrTokenUrl = @json(route('admin.qr.token'));
        window.qrTtl = @json($ttl ?? 30);

        (function () {
            const container = document.getElementById('qr-code');
            const countdownEl = document.getElementById('qr-countdown');
            const statusEl = document.getElementById('qr-status');
            let qrInstance = null;
            let secondsLeft = window.qrTtl;
            let countdownTimer = null;

            function renderQr(text) {
                container.innerHTML = '';
                qrInstance = new QRCode(container, {
                    text: text,
                    width: 256,
                    height: 256,
                    colorDark: '#111827',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }

            function resetCountdown(ttl) {
                secondsLeft = ttl ?? window.qrTtl;
                countdownEl.textContent = secondsLeft;
                if (countdownTimer) {
                    clearInterval(countdownTimer);
                }
                countdownTimer = setInterval(function () {
                    secondsLeft -= 1;
                    countdownEl.textContent = Math.max(0, secondsLeft);
                    if (secondsLeft <= 0) {
                        fetchToken();
                    }
                }, 1000);
            }

            function fetchToken() {
                statusEl.textContent = '{{ __('Refreshing…') }}';
                fetch(window.qrTokenUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        const url = data.payload_url || data.token;
                        renderQr(url);
                        window.qrTtl = data.ttl ?? window.qrTtl;
                        resetCountdown(data.ttl ?? window.qrTtl);
                        statusEl.textContent = data.expires_at
                            ? '{{ __('Valid until') }} ' + data.expires_at
                            : '';
                    })
                    .catch(function (err) {
                        statusEl.textContent = '{{ __('Failed to load token.') }} ' + err.message;
                        setTimeout(fetchToken, 5000);
                    });
            }

            document.addEventListener('DOMContentLoaded', fetchToken);
        })();
    </script>
</x-app-layout>
