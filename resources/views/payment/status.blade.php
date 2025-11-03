@extends('layout.master')
@section('title', 'Payment Verify')
@section('content')
<div class="container mt-5">
    <div class="alert alert-{{ $alert }} text-center p-4 fs-5">
        {{ $message }}
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('home.index') }}" class="btn btn-primary">بازگشت به صفحه اصلی</a>
    </div>
</div>
@endsection

