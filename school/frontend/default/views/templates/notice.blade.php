@layout('views/layouts/master')
@section('css')
    <link rel="stylesheet" href=" <?= base_url($frontendThemePath . 'assets/css/expanded/notice-list.css') ?>">
@endsection
@section('content')
    @if (customCompute($notices))
        <section class="notice-part">
            <div class="container">
                <h1 class="section-page-title">Notices</h1>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                    @foreach ($notices as $key => $notice)
                        @if ($key < 6)
                            <div class="col">
                                <div class="notice-card">
                                    <h4><a
                                            href="{{ base_url('frontend/notice/' . $notice->noticeID) }}">{{ namesorting($notice->title, 45) }}</a>
                                    </h4>
                                    <p>{{ namesorting($notice->notice, 200) }}</p>
                                    <span>{{ date('M d Y', strtotime($notice->date)) }}</span>
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
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3" id="notice"></div>
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
                url: "<?= base_url('frontend/load_more_notice') ?>",
                data: {
                    offset: offset,
                    limit: limit
                },
                dataType: "html",
                success: function(notice) {
                    if (JSON.parse(notice).length > 0) {
                        JSON.parse(notice).forEach(notice => {
                            html +=
                                ` <div class="col">
                                    <div class="notice-card">
                                        <h4><a href="{{ base_url('frontend/notice/`+notice.noticeID+`') }}">` + notice.title + `</a></h4>
                                        <p>` + (notice.notice.length > 200 ? notice.notice.slice(0, 200) + '...' :
                                    notice
                                    .notice) + `</p>
                                        <span>` + new Date(notice.date).toLocaleDateString('en-US', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                }) + `</span>
                                    </div>
                                  </div>
                                `
                        });
                    } else {
                        document.getElementById('loadMoreBtn').style.display = 'none';
                    }
                    checkEmptyNotice(offset, limit);
                    $('#notice').html(html);
                }
            });
        });
    });

    function checkEmptyNotice(offset, limit) {
        offset += limit;
        $.ajax({
            type: 'POST',
            url: "<?= base_url('frontend/load_more_notice') ?>",
            data: {
                offset: offset,
                limit: limit
            },
            dataType: "html",
            success: function(notice) {
                if (JSON.parse(notice).length == 0) {
                    document.getElementById('loadMoreBtn').style.display = 'none';
                }
            }
        });
    }
</script>
@endsection
