@extends('layouts.app')

@section('title', $producto->nombre . ' - Rdecants')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<style>
    .swiper {
        width: 100%;
        height: 400px;
        margin-bottom: 30px;
    }

    .swiper-slide {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    .swiper-slide img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .swiper-thumbs {
        height: 80px;
        box-sizing: border-box;
        padding: 10px 0;
    }

    .swiper-thumbs .swiper-slide {
        width: 80px;
        height: 80px;
        opacity: 0.5;
        cursor: pointer;
    }

    .swiper-thumbs .swiper-slide-thumb-active {
        opacity: 1;
        border: 2px solid #007bff;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-6">
            <!-- Slider principal -->
            <div class="swiper product-main-slider">
                <div class="swiper-wrapper">
                    @foreach($producto->imagenes as $imagen)
                    <div class="swiper-slide">
                        <img src="{{ asset('storage/' . $imagen->ruta_imagen) }}" alt="{{ $producto->nombre }}" class="img-fluid">
                    </div>
                    @endforeach
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <!-- Slider de miniaturas -->
            <div class="swiper product-thumbs-slider">
                <div class="swiper-wrapper">
                    @foreach($producto->imagenes as $imagen)
                    <div class="swiper-slide">
                        <img src="{{ asset('storage/' . $imagen->ruta_imagen) }}" alt="{{ $producto->nombre }}" class="img-fluid">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="mb-3">{{ $producto->nombre }}</h1>
            <h5 class="text-muted mb-4">{{ $producto->marca->nombre }}</h5>

            <div class="mb-4">
                <span class="badge bg-primary">{{ $producto->tipoAroma->nombre }}</span>
                <span class="badge bg-secondary">{{ $producto->tipoProducto->nombre }}</span>
                @if($producto->porcentaje_contenido < 100)
                <span class="badge bg-warning">{{ $producto->porcentaje_contenido }}% de contenido</span>
                @endif
            </div>

            <p class="mb-4">{{ $producto->descripcion }}</p>

            <!-- Selector de presentaciones -->
            <form action="{{ route('carrito.agregar') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                <div class="mb-3">
                    <label for="presentacion" class="form-label">Presentación</label>
                    <select name="producto_presentacion_id" id="presentacion" class="form-select @error('producto_presentacion_id') is-invalid @enderror" required>
                        <option value="">Selecciona una presentación</option>
                        @foreach($producto->productoPresentaciones as $pp)
                            @if($pp->stock > 0)
                            <option value="{{ $pp->id }}" data-precio="{{ $pp->precio }}">
                                {{ $pp->presentacion->volumen }} {{ $pp->presentacion->unidad }} - ${{ number_format($pp->precio, 2) }}
                                @if($pp->es_ultimas_unidades)
                                (¡Últimas unidades!)
                                @endif
                            </option>
                            @else
                            <option value="" disabled>{{ $pp->presentacion->volumen }} {{ $pp->presentacion->unidad }} - Agotado</option>
                            @endif
                        @endforeach
                    </select>
                    @error('producto_presentacion_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="1" min="1" max="10" required>
                    @error('cantidad')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <h3 id="precio-total">$0.00</h3>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Agregar al carrito</button>
            </form>
        </div>
    </div>

    <!-- Sección de reseñas -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-4">Reseñas</h2>

            @if($producto->resenas->count() > 0)
                @foreach($producto->resenas as $resena)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">{{ $resena->usuario->nombre }}</h5>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $resena->calificacion)
                                    <i class="bi bi-star-fill text-warning"></i>
                                    @else
                                    <i class="bi bi-star text-warning"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <p class="card-text">{{ $resena->comentario }}</p>
                        <small class="text-muted">{{ $resena->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
                @endforeach
            @else
                <div class="alert alert-info">Este producto aún no tiene reseñas.</div>
            @endif

            @auth
                @if(auth()->user()->haCompradoProducto($producto->id))
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Deja tu reseña</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('resena.store', $producto->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="calificacion" class="form-label">Calificación</label>
                                <select name="calificacion" id="calificacion" class="form-select @error('calificacion') is-invalid @enderror" required>
                                    <option value="5">5 estrellas - Excelente</option>
                                    <option value="4">4 estrellas - Muy bueno</option>
                                    <option value="3">3 estrellas - Bueno</option>
                                    <option value="2">2 estrellas - Regular</option>
                                    <option value="1">1 estrella - Malo</option>
                                </select>
                                @error('calificacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="comentario" class="form-label">Comentario</label>
                                <textarea name="comentario" id="comentario" rows="3" class="form-control @error('comentario') is-invalid @enderror"></textarea>
                                @error('comentario')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Enviar reseña</button>
                        </form>
                    </div>
                </div>
                @endif
            @endauth
        </div>
    </div>

    <!-- Productos relacionados -->
    @if($productosRelacionados->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-4">También te puede interesar</h2>

            <div class="row">
                @foreach($productosRelacionados as $relacionado)
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="{{ asset('storage/' . $relacionado->imagenPrincipal->ruta_imagen) }}" class="card-img-top" alt="{{ $relacionado->nombre }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $relacionado->nombre }}</h5>
                            <p class="card-text">{{ $relacionado->marca->nombre }}</p>
                            <a href="{{ route('producto.detalle', $relacionado->slug) }}" class="btn btn-outline-primary">Ver detalles</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el slider de miniaturas
        var thumbsSwiper = new Swiper('.product-thumbs-slider', {
            spaceBetween: 10,
            slidesPerView: 'auto',
            freeMode: true,
            watchSlidesProgress: true,
        });

        // Inicializar el slider principal
        var mainSwiper = new Swiper('.product-main-slider', {
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            thumbs: {
                swiper: thumbsSwiper,
            },
        });

        // Actualizar precio total al cambiar presentación
        const presentacionSelect = document.getElementById('presentacion');
        const cantidadInput = document.getElementById('cantidad');
        const precioTotal = document.getElementById('precio-total');

        function actualizarPrecioTotal() {
            const presentacionOption = presentacionSelect.options[presentacionSelect.selectedIndex];
            if (presentacionOption && presentacionOption.value) {
                const precio = parseFloat(presentacionOption.getAttribute('data-precio'));
                const cantidad = parseInt(cantidadInput.value);

                if (!isNaN(precio) && !isNaN(cantidad)) {
                    const total = precio * cantidad;
                    precioTotal.textContent = '$' + total.toFixed(2);
                }
            } else {
                precioTotal.textContent = '$0.00';
            }
        }

        presentacionSelect.addEventListener('change', actualizarPrecioTotal);
        cantidadInput.addEventListener('change', actualizarPrecioTotal);
        cantidadInput.addEventListener('input', actualizarPrecioTotal);
    });
</script>
@endpush
