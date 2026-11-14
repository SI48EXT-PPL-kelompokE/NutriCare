@if ($account->last_login)
    <time datetime="{{ $account->last_login->toIso8601String() }}">{{ $account->last_login->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>
@else
    Belum masuk
@endif
