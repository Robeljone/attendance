<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My attendance') }}</h2>
    </x-slot>

    <x-ui.page>
        <div
            id="attendance-portal"
            x-data="{
                scanVerified: {{ $verifiedToken ? 'true' : 'false' }},
                scannedToken: @js($verifiedToken ?? ''),
                statusMessage: @js($verifiedToken ? __('Station QR verified. You can clock in or out.') : ''),
                cameraReady: false,
                cameraError: false,
                manualToken: '',
                showManualEntry: false,
            }"
        >
        <div class="space-y-6">
            <x-ui.flash />

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,20rem)]">
            {{-- Today --}}
            <x-ui.card>
                <x-slot name="header">
                    <h3 class="ui-card-title">{{ __('Today') }}</h3>
                </x-slot>

                <div class="space-y-6">
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ __('Status') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $todayStatus ?? ($todayRecord?->isOpen() ? __('Clocked in') : ($todayRecord?->clock_out_at ? __('Completed') : __('Not started'))) }}
                            </dd>
                        </div>
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ __('Clock in') }}</dt>
                            <dd class="mt-1 text-sm font-semibold tabular-nums text-gray-900">{{ $todayRecord?->clock_in_at?->format('H:i') ?? '—' }}</dd>
                        </div>
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ __('Clock out') }}</dt>
                            <dd class="mt-1 text-sm font-semibold tabular-nums text-gray-900">{{ $todayRecord?->clock_out_at?->format('H:i') ?? '—' }}</dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap items-center gap-3">
                        @if ($canClockIn ?? ! ($todayRecord?->clock_in_at))
                            <form method="POST" action="{{ route('portal.attendance.clock-in') }}">
                                @csrf
                                <input type="hidden" name="token" x-model="scannedToken">
                                <button
                                    type="submit"
                                    x-bind:disabled="! scanVerified"
                                    class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500"
                                >
                                    {{ __('Clock in') }}
                                </button>
                            </form>
                        @endif
                        @if ($canClockOut ?? ($todayRecord?->isOpen() ?? false))
                            <form method="POST" action="{{ route('portal.attendance.clock-out') }}">
                                @csrf
                                <input type="hidden" name="token" x-model="scannedToken">
                                <button
                                    type="submit"
                                    x-bind:disabled="! scanVerified"
                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100 disabled:text-gray-400"
                                >
                                    {{ __('Clock out') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <p
                        x-show="! scanVerified"
                        x-cloak
                        class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
                    >
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ __('Scan the station QR code to enable clock in / out.') }}</span>
                    </p>
                </div>
            </x-ui.card>

            {{-- Scan station --}}
            <x-ui.card>
                <x-slot name="header">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="ui-card-title">{{ __('Scan station QR') }}</h3>
                        <p class="ui-card-subtitle">{{ __('Use your camera to verify the station code') }}</p>
                    </div>
                    <span
                        x-show="scanVerified"
                        x-cloak
                        class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                        {{ __('Verified') }}
                    </span>
                    <span
                        x-show="! scanVerified && cameraReady"
                        x-cloak
                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800"
                    >
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
                        {{ __('Scanning') }}
                    </span>
                </div>
                </x-slot>

                <div class="mx-auto w-full max-w-xs">
                        <div
                            class="relative aspect-square overflow-hidden rounded-2xl bg-gray-900 ring-1 ring-gray-900/10"
                            :class="scanVerified ? 'ring-2 ring-green-500 ring-offset-2' : ''"
                        >
                            {{-- Camera feed (html5-qrcode mounts video here) --}}
                            <div
                                id="qr-reader"
                                class="qr-reader absolute inset-0 h-full w-full"
                                x-show="! scanVerified"
                            ></div>

                            {{-- Tailwind scan frame --}}
                            <div
                                x-show="! scanVerified"
                                class="pointer-events-none absolute inset-0 z-10"
                                aria-hidden="true"
                            >
                                <div class="absolute inset-[18%] rounded-lg border border-white/30"></div>
                                <div class="absolute left-[18%] top-[18%] h-6 w-6 rounded-tl-md border-l-4 border-t-4 border-white"></div>
                                <div class="absolute right-[18%] top-[18%] h-6 w-6 rounded-tr-md border-r-4 border-t-4 border-white"></div>
                                <div class="absolute bottom-[18%] left-[18%] h-6 w-6 rounded-bl-md border-b-4 border-l-4 border-white"></div>
                                <div class="absolute bottom-[18%] right-[18%] h-6 w-6 rounded-br-md border-b-4 border-r-4 border-white"></div>
                                <div
                                    x-show="cameraReady"
                                    class="absolute left-[20%] right-[20%] top-1/2 h-0.5 -translate-y-1/2 bg-indigo-400/80 shadow-[0_0_12px_rgba(129,140,248,0.8)]"
                                ></div>
                            </div>

                            {{-- Loading / permission state --}}
                            <div
                                x-show="! scanVerified && ! cameraReady && ! cameraError"
                                class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 bg-gray-900/95 px-6 text-center"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/10">
                                    <svg class="h-6 w-6 animate-pulse text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.773-.527 1.687-.527 2.62v6.3a2.25 2.25 0 002.25 2.25h10.182a2.25 2.25 0 002.25-2.25v-6.3c0-.933-.147-1.847-.527-2.62a2.31 2.31 0 00-1.641-1.055l-.823-.165a2.25 2.25 0 01-1.632-1.216l-.548-1.096A2.25 2.25 0 0014.482 3H9.518a2.25 2.25 0 00-2.014 1.198l-.548 1.096a2.25 2.25 0 01-1.632 1.216l-.823.165z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-white">{{ __('Starting camera…') }}</p>
                                <p class="text-xs text-gray-400">{{ __('Allow camera access when prompted.') }}</p>
                            </div>

                            {{-- Camera error --}}
                            <div
                                x-show="cameraError && ! scanVerified"
                                x-cloak
                                class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 bg-gray-900 px-6 text-center"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-500/20 text-red-300">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-white" x-text="statusMessage"></p>
                            </div>

                            {{-- Verified state --}}
                            <div
                                x-show="scanVerified"
                                x-cloak
                                class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-3 bg-green-50 px-6 text-center"
                            >
                                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 ring-8 ring-green-50">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </span>
                                <p class="text-base font-semibold text-green-800">{{ __('Station QR verified') }}</p>
                                <p class="text-sm text-green-700">{{ __('You can clock in or out.') }}</p>
                            </div>
                        </div>

                        <p
                            class="mt-4 text-center text-sm"
                            :class="{
                                'font-medium text-green-700': scanVerified,
                                'text-red-600': cameraError && ! scanVerified,
                                'text-gray-500': ! scanVerified && ! cameraError,
                            }"
                            x-text="statusMessage || '{{ __('Align the station QR code inside the frame.') }}'"
                        ></p>

                        {{-- Manual entry when camera is unavailable (e.g. HTTP / LAN IP) --}}
                        <div
                            x-show="! scanVerified && showManualEntry"
                            x-cloak
                            class="mt-5 space-y-3 border-t border-gray-100 pt-5"
                        >
                            <p class="text-center text-xs text-gray-500">
                                {{ __('Camera blocked? Paste the station QR link or code below.') }}
                            </p>
                            <div class="flex gap-2">
                                <x-text-input
                                    id="manual-station-token"
                                    type="text"
                                    class="block w-full"
                                    x-model="manualToken"
                                    placeholder="{{ __('Station code or QR link') }}"
                                    autocomplete="off"
                                    @keydown.enter.prevent="$dispatch('verify-manual-token')"
                                />
                                <button
                                    type="button"
                                    id="manual-verify-btn"
                                    class="inline-flex shrink-0 items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="$dispatch('verify-manual-token')"
                                >
                                    {{ __('Verify') }}
                                </button>
                            </div>
                        </div>
                </div>
            </x-ui.card>
            </div>
        </div>
        </div>
    </x-ui.page>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('attendance-portal');
            const scanUrl = @json(route('portal.attendance.scan'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const alreadyVerified = @json((bool) $verifiedToken);
            let processing = false;
            let scanner = null;

            function setScanState(options) {
                if (! root || ! window.Alpine) {
                    return;
                }

                const data = Alpine.$data(root);

                if ('verified' in options) {
                    data.scanVerified = options.verified;
                }
                if ('token' in options) {
                    data.scannedToken = options.token;
                }
                if ('message' in options) {
                    data.statusMessage = options.message;
                }
                if ('cameraReady' in options) {
                    data.cameraReady = options.cameraReady;
                }
                if ('cameraError' in options) {
                    data.cameraError = options.cameraError;
                }
                if ('showManualEntry' in options) {
                    data.showManualEntry = options.showManualEntry;
                }
            }

            function cameraBlockedMessage() {
                if (! window.isSecureContext) {
                    return '{{ __('Browsers block the camera on non-HTTPS pages. Use code entry below, scan with your phone camera app, or open this site over HTTPS / localhost.') }}';
                }

                return '{{ __('Unable to access the camera. Check browser permissions.') }}';
            }

            function verifyToken(decodedText) {
                if (processing) {
                    return;
                }

                processing = true;
                setScanState({
                    verified: false,
                    token: '',
                    message: '{{ __('Processing scan…') }}',
                    cameraReady: true,
                    cameraError: false,
                });

                fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ token: decodedText }),
                })
                    .then(function (response) {
                        return response.json().then(function (body) {
                            return { ok: response.ok, body: body };
                        });
                    })
                    .then(function (result) {
                        if (result.ok) {
                            setScanState({
                                verified: true,
                                token: decodedText,
                                message: result.body.message || '{{ __('Station QR verified.') }}',
                                cameraReady: true,
                                cameraError: false,
                                showManualEntry: false,
                            });
                            if (scanner) {
                                scanner.stop().catch(function () {});
                            }
                        } else {
                            setScanState({
                                verified: false,
                                token: '',
                                message: result.body.message || '{{ __('Scan rejected.') }}',
                                cameraReady: true,
                                cameraError: false,
                                showManualEntry: true,
                            });
                            processing = false;
                        }
                    })
                    .catch(function () {
                        setScanState({
                            verified: false,
                            token: '',
                            message: '{{ __('Scan failed. Try again.') }}',
                            cameraReady: true,
                            cameraError: false,
                            showManualEntry: true,
                        });
                        processing = false;
                    });
            }

            root.addEventListener('verify-manual-token', function () {
                const data = Alpine.$data(root);
                const value = (data.manualToken || '').trim();

                if (! value) {
                    setScanState({
                        message: '{{ __('Enter the station code or QR link first.') }}',
                        cameraError: true,
                        cameraReady: false,
                        showManualEntry: true,
                    });
                    return;
                }

                verifyToken(value);
            });

            if (alreadyVerified) {
                return;
            }

            if (! window.isSecureContext) {
                setScanState({
                    verified: false,
                    token: '',
                    message: cameraBlockedMessage(),
                    cameraReady: false,
                    cameraError: true,
                    showManualEntry: true,
                });
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                setScanState({
                    verified: false,
                    token: '',
                    message: '{{ __('QR scanner failed to load.') }}',
                    cameraReady: false,
                    cameraError: true,
                    showManualEntry: true,
                });
                return;
            }

            scanner = new Html5Qrcode('qr-reader', { verbose: false });

            Html5Qrcode.getCameras()
                .then(function (cameras) {
                    if (! cameras.length) {
                        setScanState({
                            verified: false,
                            token: '',
                            message: '{{ __('No camera found. Allow camera access to scan.') }}',
                            cameraReady: false,
                            cameraError: true,
                            showManualEntry: true,
                        });
                        return;
                    }

                    return scanner.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            aspectRatio: 1,
                            disableFlip: false,
                        },
                        verifyToken,
                        function () {},
                    ).then(function () {
                        setScanState({
                            verified: false,
                            token: '',
                            message: '{{ __('Align the station QR code inside the frame.') }}',
                            cameraReady: true,
                            cameraError: false,
                        });
                    });
                })
                .catch(function () {
                    setScanState({
                        verified: false,
                        token: '',
                        message: cameraBlockedMessage(),
                        cameraReady: false,
                        cameraError: true,
                        showManualEntry: true,
                    });
                });
        });
    </script>
</x-app-layout>
