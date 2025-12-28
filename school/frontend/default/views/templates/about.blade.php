@layout('views/layouts/master')
@section('css')
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/about.css') ?>">
@endsection
@section('content')
    <section class="about-part">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <h1 class="section-page-title">About School</h1>
                    <div class="about-content mt-0">
                        <h2 class="section-title"> {{ frontendData::get_backend('sname') }}</h2>
                        <p>{{ htmlspecialchars_decode($page->content ?? '') }}</p>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="about-media">  
                        <img src="{{ imageLinkWithDefatulImage($featured_image->file_name, 'about.jpg', 'uploads/gallery') }}" alt="about">

                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(frontendData::get_frontend('school_origin') || frontendData::get_frontend('school_campus') || frontendData::get_frontend('school_success'))
        <section class="history-part">
            <div class="history-overlay">
                <div class="container">
                    <div class="history-content">
                        <h2 class="section-title">Our History</h2>
                        <div class="row row-cols-1 row-cols-md-3">
                            @if(frontendData::get_frontend('school_origin'))
                            <div class="col">
                                <h3>Origin</h3>
                                <p>{{ frontendData::get_frontend('school_origin') }}</p>
                            </div>
                            @endif
                            @if(frontendData::get_frontend('school_campus'))
                            <div class="col">
                                <h3>Campus</h3>
                                <p>{{ frontendData::get_frontend('school_campus') }}</p>
                            </div>
                            @endif
                            @if(frontendData::get_frontend('school_success'))
                            <div class="col">
                                <h3>Success</h3>
                                <p>{{ frontendData::get_frontend('school_success') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (frontendData::get_frontend('school_vision'))
    <section class="vision-part">
        <div class="container">
            <div class="vision-content">
                <h2 class="section-title">Our Vision</h2>
                <p>{{ frontendData::get_frontend('school_vision') }}</p>
            </div>
        </div>
    </section>
    @endif
@endsection
