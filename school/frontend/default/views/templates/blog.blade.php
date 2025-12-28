@layout('views/layouts/master')
 
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/blog-list.css') ?>">
@endsection

@section('content')
    @if (customCompute($posts))
        <section class="blog-part">
            <div class="container">
                <h1 class="section-page-title">Blogs</h1>
                <div class="row">
                    @foreach ($posts as $key => $post)
                        @if ($key < 6)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="blog-card">
                                    <a href="{{ base_url('frontend/post/' . $post->url) }}" class="blog-figure">
                                        <img src="{{ imageLinkWithDefatulImage(isset($featured_image[$post->featured_image]) ? $featured_image[$post->featured_image]->file_name : null, 'blog.jpg', 'uploads/gallery/') }}"
                                            alt="blog">
                                    </a>
                                    <div class="blog-content">
                                        <small>{{ date('M d, Y', strtotime($post->publish_date)) }}</small>
                                        <h4><a href="{{ base_url('frontend/post/' . $post->url) }}">{{ $post->title }}</a>
                                        </h4>
                                        <p>
                                            @if (strlen((string) $post->content) > 150)
                                                {{ namesorting(strip_tags($post->content), 150) }}
                                            @else
                                                {{ strip_tags($post->content) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <?php
                            $loadMoreBtn = true;
                            break;
                            ?>
                        @endif
                    @endforeach
                </div>
                @if ($loadMoreBtn)
                    <div class="row" id="blogs_one"></div>
                    <div class="mt-4 text-center">
                        <button type="button" class="btn btn-inline" id="loadMoreBtn">load more</button>
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var offset = 0;
            var limit = 6;
            var html = '';
            $('#loadMoreBtn').on('click', function() {
                offset += limit;
                $.ajax({
                    type: 'POST',
                    url: "<?= base_url('frontend/load_more_post') ?>",
                    data: {
                        offset: offset,
                        limit: limit
                    },
                    dataType: "html",
                    success: function(response) {
                        var response = JSON.parse(response);
                        if (response.posts.length > 0) {
                            response.posts.forEach(post => {
                                var content = stripTags(post.content).length > 200 ?
                                    stripTags(post.content).substring(0, 200) + '...' :
                                    post.content;
                                    console.log(response.images[post.featured_image].file_name);
                                html +=
                                    `    
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="blog-card">
                                <a href="{{ base_url('frontend/post/`+post.url+`') }}" class="blog-figure">
                          
                                    <img src="{{ base_url('/uploads/gallery/`+response.images[post.featured_image].file_name+`') }}" alt="` +
                                    post.title + `">
                                    
                                 </a>
                            <div class="blog-content">
                                <small>` + new Date(post.publish_date).toLocaleDateString('en-US', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    }) + `</small>
                                 <h4><a href="{{ base_url('frontend/post/`+post.url+`') }}">` + post.title + `</a></h4>
                                <p>` + content + `</p>
                            </div>
                        </div>
                        </div>
                                    `
                            });
                        } else {
                            document.getElementById('loadMoreBtn').style.display = 'none';
                        }
                        checkEmptyBlog(offset, limit);
                        $('#blogs_one').html(html);
                    }
                });
            });
        });

        function checkEmptyBlog(offset, limit) {
            offset += limit;
            $.ajax({
                type: 'POST',
                url: "<?= base_url('frontend/load_more_post') ?>",
                data: {
                    offset: offset,
                    limit: limit
                },
                dataType: "html",
                success: function(response) {
                    var response = JSON.parse(response);
                    if (response.posts.length == 0) {
                        document.getElementById('loadMoreBtn').style.display = 'none';
                    }
                }
            });
        }

        function stripTags(input) {
            return input.replace(/<[^>]*>/g, '');
        }
    </script>
@endsection
