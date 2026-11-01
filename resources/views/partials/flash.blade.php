@if(session('success'))
    <div class="mb-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
        <span>{{ session('success') }}</span><button type="button" class="dismiss-flash ml-3 text-emerald-600" aria-label="Dismiss">×</button>
    </div>
@endif
@if(session('error'))
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
        <p class="font-semibold">Please check the following:</p>
        <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
