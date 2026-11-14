@if ($errors->any())
    <ul role="alert" class="list-inside list-disc rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
        @foreach ($errors->all() as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
