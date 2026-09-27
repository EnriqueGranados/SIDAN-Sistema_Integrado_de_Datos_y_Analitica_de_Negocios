@extends('layouts.navbars')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Mi Panel</h1>
    <div class=" p-6 rounded-lg shadow">
        <p>Bienvenido, {{ auth()->user()->nombres }}</p>
    </div>
@endsection