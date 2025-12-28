@layout('views/layouts/master')

@section('css')
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/blog-details.css') ?>">
@endsection

@section('content')
    <article class="article-part smt-115 smb-200">
        <div class="container">
            <div class="article-content">
                <h1 class="article-title">{{ $post->title }}</h1>
                <h2 class="article-date">Posted on {{ date('dS F, Y', strtotime($post->publish_date)) }} by
                    {{ frontendData::get_user($post->create_usertypeID, $post->create_userID) }}</h2>
                <img class="article-image"
                    src="{{ imageLinkWithDefatulImage($featured_image->file_name, 'holiday.png', 'uploads/gallery/') }}"
                    alt="blog">
                <p class="article-text">{{ $post->content }}</p>
            </div>
        </div>
    </article>
    <section class="recent-part smb-200">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Recent Blogs</h2>
            </div>
            <div class="recent-carousel owl-carousel carousel-arrow">
                @foreach ($posts as $post)
                    <div class="blog-card">
                        <figure class="blog-figure">
                            <img src="{{ imageLinkWithDefatulImage($recent_featured_image[$post->featured_image]->file_name, 'holiday.png', 'uploads/gallery/') }}"
                                alt="blog">
                        </figure>
                        <div class="blog-content">
                            <span>Posted on {{ date('dS F, Y', strtotime($post->publish_date)) }} by
                                {{ frontendData::get_user($post->create_usertypeID, $post->create_userID) }}</span>
                            <h5><a
                                    href="{{ base_url('frontend/post/' . $post->url) }}">{{ namesorting(strip_tags($post->title), 40) }}</a>
                            </h5>
                            <p style="text-align: justify">
                                @if (strlen(strip_tags($post->content)) > 150)
                                    {{ namesorting(strip_tags($post->content), 150) }}
                                    <a href="{{ base_url('frontend/post/' . $post->url) }}"> Read More » </a>
                                @else
                                    {{ strip_tags($post->content) }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
