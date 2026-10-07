@props([
    'paginator',
    'search' => true,
    'searchPlaceholder' => null,
    'perPageOptions' => \App\Support\ResolvesIndexPagination::ALLOWED_PER_PAGE,
])

@php
    $searchPlaceholder ??= __('Search…');
    $currentPerPage = (int) request('per_page', $paginator->perPage());
@endphp

<x-ui.card flush {{ $attributes }}>
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        @foreach (request()->except(['q', 'per_page', 'page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $nested)
                    @if (! is_array($nested))
                        <input type="hidden" name="{{ $key }}[]" value="{{ $nested }}">
                    @endif
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        @if ($search)
            <div class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="block w-full rounded-lg border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
        @else
            <div></div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <label for="datatable-per-page" class="text-sm text-gray-600">{{ __('Per page') }}</label>
            <select
                id="datatable-per-page"
                name="per_page"
                class="rounded-lg border-gray-300 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                onchange="this.form.submit()"
            >
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($currentPerPage === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
            @if ($search)
                <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    {{ __('Search') }}
                </button>
            @endif
        </div>
    </form>

    <div class="ui-table-wrap">
        {{ $slot }}
    </div>

    @if (method_exists($paginator, 'links'))
        <div class="ui-pagination flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500">
                {{ __('Showing') }}
                <span class="font-medium text-gray-700">{{ $paginator->firstItem() ?? 0 }}</span>
                –
                <span class="font-medium text-gray-700">{{ $paginator->lastItem() ?? 0 }}</span>
                {{ __('of') }}
                <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
            </p>
            <div>{{ $paginator->withQueryString()->links() }}</div>
        </div>
    @endif
</x-ui.card>
