@layout('views/layouts/master')
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/notice-details.css') ?>">
@endsection
@section('content')
<section class="notice-part">
    <div class="container">
        <div class="notice-group">
            <h2>{{ $notice->title }}</h2>
            <div class="notice-meta">
                <span>{{ date('d M Y', strtotime($notice->date)) }}</span>
            </div>
            <article class="notice-details">
                <p>
                    {{ htmlspecialchars_decode($notice->notice ?? "") }}
                </p>
            </article>
        </div>
    </div>
</section>
@endsection
