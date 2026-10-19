{{-- Layout público compartido con la navbar institucional de SIDAN. --}}
@extends('layouts.public')

@section('content')
    {{-- Ocultar navegación al imprimir comprobantes de inscripción. --}}
    <div class="sidan-no-print print:hidden">
        @include('partials.public-header')
    </div>

    @yield('portal-content')
@endsection
