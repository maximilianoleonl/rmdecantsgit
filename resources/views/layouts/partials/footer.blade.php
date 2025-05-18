<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Rdecants</h5>
                <p class="small">Tu tienda de perfumes originales y decants.</p>
                <p class="small">
                    <i class="bi bi-geo-alt"></i> Ubicación, Ciudad, Estado CP 12345<br>
                    <i class="bi bi-envelope"></i> info@rdecants.com<br>
                    <i class="bi bi-telephone"></i> (123) 456-7890
                </p>
            </div>
            <div class="col-md-3">
                <h5>Enlaces rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('home') }}" class="text-white-50">Inicio</a></li>
                    <li><a href="{{ route('catalogo.index') }}" class="text-white-50">Catálogo</a></li>
                    <li><a href="#" class="text-white-50">Sobre nosotros</a></li>
                    <li><a href="#" class="text-white-50">Contacto</a></li>
                    <li><a href="#" class="text-white-50">Términos y condiciones</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Categorías</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50">Perfumes Originales</a></li>
                    <li><a href="#" class="text-white-50">Decants</a></li>
                    <li><a href="#" class="text-white-50">Testers</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h5>Síguenos</h5>
                <div class="d-flex gap-2">
                    <a href="#" class="text-white-50"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-twitter fs-5"></i></a>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 text-center">
                <p class="small mb-0">&copy; {{ date('Y') }} Rdecants. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>
