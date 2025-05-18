@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Sección de banner (si existe) -->
    @if(isset($producto) && $producto)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if($producto->imagenPrincipal)
                                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta_imagen) }}" class="img-fluid" alt="{{ $producto->nombre }}">
                                @else
                                    <img src="{{ asset('img/default-product.png') }}" class="img-fluid" alt="{{ $producto->nombre }}">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h3>{{ $producto->nombre }}</h3>
                                <p>{{ $producto->descripcion }}</p>
                                <a href="{{ route('producto.detalle', $producto->slug) }}" class="btn btn-primary">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sección de productos destacados -->
    <h2>Productos destacados</h2>
    <div class="row">
        @forelse($productosDestacados as $productoItem)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    @if($productoItem->imagenPrincipal)
                        <img src="{{ asset('storage/' . $productoItem->imagenPrincipal->ruta_imagen) }}" class="card-img-top" alt="{{ $productoItem->nombre }}">
                    @else
                        <img src="{{ asset('img/default-product.png') }}" class="card-img-top" alt="{{ $productoItem->nombre }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $productoItem->nombre }}</h5>
                        <p class="card-text">{{ $productoItem->marca->nombre }}</p>

                        <!-- Mostrar precio más bajo disponible -->
                        @if($productoItem->productoPresentaciones->isNotEmpty())
                            <p class="card-text">
                                Desde ${{ number_format($productoItem->productoPresentaciones->min('precio'), 2) }}
                            </p>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="{{ route('producto.detalle', $productoItem->slug) }}" class="btn btn-outline-primary btn-sm w-100">Ver detalles</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No hay productos destacados disponibles.</div>
            </div>
        @endforelse
    </div>

    <!-- Sección de productos nuevos -->
    <h2 class="mt-5">Productos nuevos</h2>
    <div class="row">
        @forelse($productosNuevos as $productoItem)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    @if($productoItem->imagenPrincipal)
                        <img src="{{ asset('storage/' . $productoItem->imagenPrincipal->ruta_imagen) }}" class="card-img-top" alt="{{ $productoItem->nombre }}">
                    @else
                        <img src="{{ asset('img/default-product.png') }}" class="card-img-top" alt="{{ $productoItem->nombre }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $productoItem->nombre }}</h5>
                        <p class="card-text">{{ $productoItem->marca->nombre }}</p>

                        <!-- Mostrar precio más bajo disponible -->
                        @if($productoItem->productoPresentaciones->isNotEmpty())
                            <p class="card-text">
                                Desde ${{ number_format($productoItem->productoPresentaciones->min('precio'), 2) }}
                            </p>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="{{ route('producto.detalle', $productoItem->slug) }}" class="btn btn-outline-primary btn-sm w-100">Ver detalles</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No hay productos nuevos disponibles.</div>
            </div>
        @endforelse
    </div>

    <!-- Sección de marcas -->
    @if($marcas->isNotEmpty())
    <h2 class="mt-5">Nuestras marcas</h2>
    <div class="row">
        @foreach($marcas as $marca)
            <div class="col-md-2 col-4 mb-4 text-center">
                <a href="{{ route('catalogo.marca', $marca->slug) }}" class="text-decoration-none">
                    @if($marca->logo)
                        <img src="{{ asset('storage/' . $marca->logo) }}" alt="{{ $marca->nombre }}" class="img-fluid mb-2" style="max-height: 60px;">
                    @else
                        <div class="border p-2 rounded">
                            <span class="d-block p-2">{{ $marca->nombre }}</span>
                        </div>
                    @endif
                </a>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
