@layout('views/layouts/master')
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/event-list.css') ?>">
@endsection
@section('content')
    <section class="event-part">
        <div class="container">
            <h1 class="section-page-title">Events</h1>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
                @if (customCompute($events))
                    @foreach ($events as $key => $event)
                        @if ($key < 6)
                            <div class="col">
                                <div class="event-card">
                                    <a href="{{ base_url('frontend/event/' . $event->eventID) }}" class="event-media">
                                        <img src="{{ base_url('/uploads/images/' . $event->photo) }}" alt="event">
                                    </a>
                                    <div class="event-content">
                                        <h4><a
                                                href="{{ base_url('frontend/event/' . $event->eventID) }}">{{ $event->title }}</a>
                                        </h4>
                                        <h5>{{ date('d M, Y', strtotime($event->fdate)) }} -
                                            {{ date('d M, Y', strtotime($event->tdate)) }}</h5>
                                        <p>
                                            @if (strlen((string) $event->details) > 100)
                                                {{ strip_tags(substr((string) $event->details, 0, 100) . '...') }}
                                            @else
                                                {{ strip_tags(substr((string) $event->details, 0, 100)) }}
                                            @endif
                                        </p>
                                        <a class="view" href="{{ base_url('frontend/event/' . $event->eventID) }}">
                                            <span>View Event</span>
                                            <i class="lni lni-arrow-right"></i>
                                        </a>
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
                @endif
            </div>

            @if ($loadMoreBtn)
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3" id="events"></div>
                <div class="mt-4 text-center">
                    <button type="button" class="btn btn-inline" id="loadMoreBtn">load more</button>
                </div>
            @endif
        </div>
    </section>
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
                    url: "<?= base_url('frontend/load_more_event') ?>",
                    data: {
                        offset: offset,
                        limit: limit
                    },
                    dataType: "html",
                    success: function(events) {
                        if (JSON.parse(events).length > 0) {
                            JSON.parse(events).forEach(event => {
                                html +=
                                    ` 
                            <div class="col">
                                <div class="event-card">
                                <a href="{{ base_url('frontend/event/`+event.eventID+`') }}" class="event-media">
                                     <img src="{{ base_url('/uploads/images/`+event.photo+`') }}" alt="` +
                                    event.photo + `">
                                </a>
                            <div class="event-content">
                                <h4><a href="{{ base_url('frontend/event/`+event.eventID+`') }}">` + event.title + `</a></h4>
                                <h5>` + new Date(event.fdate).toLocaleDateString('en-US', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    }) + ` - ` + new Date(event.tdate)
                                    .toLocaleDateString(
                                        'en-US', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        }) + `</h5>
                                <p>` + (event.details.length > 100 ? event.details.slice(0, 100) + '...' : event
                                        .details) + `</p>
                                    <a class="view" href="{{ base_url('frontend/event/`+event.eventID+`') }}">
                                     <span>View Event</span>
                                    <i class="lni lni-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                     </div>`
                            });
                        } else {
                            document.getElementById('loadMoreBtn').style.display = 'none';
                        }
                        checkEmptyEvent(offset, limit);
                        $('#events').html(html);
                    }
                });
            });
        });

        function checkEmptyEvent(offset, limit) {
            offset += limit;
            $.ajax({
                type: 'POST',
                url: "<?= base_url('frontend/load_more_event') ?>",
                data: {
                    offset: offset,
                    limit: limit
                },
                dataType: "html",
                success: function(events) {
                    if (JSON.parse(events).length == 0) {
                        document.getElementById('loadMoreBtn').style.display = 'none';
                    }
                }
            });
        }
    </script>
@endsection
