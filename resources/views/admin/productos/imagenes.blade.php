<!-- resources/views/admin/productos/imagenes.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Imágenes del producto: {{ $producto->nombre }}</h3>
                </div>
                <div class="card-body">
                    <!-- Formulario para subir nueva imagen -->
                    <form action="{{ route('admin.productos.subir-imagen', $producto->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="imagen" class="form-label">Seleccionar imagen</label>
                                <input type="file" name="imagen" id="imagen" class="form-control @error('imagen') is-invalid @enderror" required>
                                @error('imagen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 mt-4">
                                <button type="submit" class="btn btn-primary">Subir imagen</button>
                            </div>
                        </div>
                    </form>

                    <!-- Lista de imágenes actuales -->
                    <div class="row">
                        @forelse($producto->imagenes as $imagen)
                            <div class="col-md-3 mb-4">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $imagen->ruta_imagen) }}" class="card-img-top" alt="{{ $producto->nombre }}">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            @if($imagen->es_principal)
                                                <span class="badge bg-success">Principal</span>
                                            @else
                                                <span class="badge bg-secondary">Secundaria</span>
                                            @endif
                                        </h5>
                                        <div class="btn-group" role="group">
                                            @if(!$imagen->es_principal)
                                                <form action="{{ route('admin.productos.imagen-principal', $imagen->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">Hacer principal</button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.productos.eliminar-imagen', $imagen->id) }}" method="POST" class="ms-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta imagen?')">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No hay imágenes para este producto. Sube una imagen.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.productos.edit', $producto->id) }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
