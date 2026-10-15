@extends('layouts.public')

@section('content')
    @include('partials.public-header')

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('user-content')
    </main>
@endsection