@layout('views/layouts/master')
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/gallery.css') ?>">
@endsection
@section('content')
    <section class="gallery-part page"> 
        <div class="container">
            <h1 class="section-page-title">School’s Gallery</h1>
            @if(customCompute($gallery))
            <div class="row">
                @foreach($gallery as $image)
                <div class="col-12 col-sm-6 col-md-4">
                    <a class="venobox-image" data-gall="gallery" href="{{ $image }}">
                        <img src="{{ $image }}" alt="gallery">
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
@endsection
