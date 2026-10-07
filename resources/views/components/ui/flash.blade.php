@if (session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif

@if (session('status'))
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800" role="status">
        {{ session('status') }}
    </div>
@endif
