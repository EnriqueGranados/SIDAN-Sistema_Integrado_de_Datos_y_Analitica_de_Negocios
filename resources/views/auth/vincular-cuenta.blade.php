@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-center">
                    <h5 class="mb-0">Vincular cuenta de Google</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Hemos detectado que ya existe una cuenta con el correo <strong>{{ session('temp_email') }}</strong>.
                    </div>
                    <p class="text-muted">
                        Para proteger tu cuenta y vincular tu perfil de Google, ingresa tu contraseña actual. Solo te la pediremos esta vez.
                    </p>

                    <form method="POST" action="{{ route('vincular.cuenta.procesar') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña actual</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autofocus>
                            
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                Confirmar y Vincular
                            </button>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection