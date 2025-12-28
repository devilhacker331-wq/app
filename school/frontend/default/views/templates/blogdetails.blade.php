@layout('views/layouts/master')
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/blog-details.css') ?>">
@endsection
@section('content')
    @if (customCompute($post))
        <section class="blog-part">
            <div class="container">
                <div class="blog-group">
                    <h2>{{ $post->title }}</h2>
                    <div class="blog-meta">
                        <span>{{ date('M d, Y', strtotime($post->publish_date)) }}</span>
                    </div>
                    <article class="blog-details">
                        <img src="{{ imageLinkWithDefatulImage(customCompute($postImage) ? $postImage->file_name : null, 'blog.jpg', 'uploads/gallery/') }}"
                            alt="event">
                        <p>{{ strip_tags($post->content) }}</p>
                    </article>
                </div>
            </div>
        </section>
    @endif

    @if (customCompute($recentPosts))
        <section class="recent-part">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title">Recent blogs</h2>
                </div>
                <div class="recent-carousel owl-carousel carousel-arrow">
                    @foreach ($recentPosts as $recent_post)
                        <div class="blog-card">
                            <a href="{{ base_url('frontend/post/' . $recent_post->url) }}" class="blog-figure">
                                <img src="{{ imageLinkWithDefatulImage(isset($recentPostimage[$recent_post->featured_image]) ? $recentPostimage[$recent_post->featured_image]->file_name : null, 'blog.jpg', 'uploads/gallery/') }}"
                                    alt="blog">
                            </a>
                            <div class="blog-content">
                                <small>{{ date('M d, Y', strtotime($recent_post->publish_date)) }}</small>
                                <h4><a href="{{ base_url('frontend/post/' . $recent_post->url) }}">{{ $recent_post->title }}</a></h4>
                                <p>
                                    @if (strlen((string) $recent_post->content) > 150)
                                        {{ namesorting(strip_tags($recent_post->content), 150) }}
                                    @else
                                        {{ strip_tags($recent_post->content) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
