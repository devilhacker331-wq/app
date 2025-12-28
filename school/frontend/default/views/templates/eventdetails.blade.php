@layout('views/layouts/master')
@section('css')
<link rel="stylesheet" href="<?= base_url($frontendThemePath .'assets/css/expanded/event-details.css') ?>">
@endsection
@section('content')
    <section class="event-part">
        <div class="container">
            <div class="event-group">
                <h2>{{ $event->title }}</h2>
                <div class="event-meta">
                    <button type="button">going</button>
                    <span>{{ date('d M, Y', strtotime($event->fdate)) }} -
                        {{ date('d M, Y', strtotime($event->tdate)) }}</span>
                    <span>{{ date('h:i A', strtotime((string) $event->ftime)) }} -
                        {{ date('h:i A', strtotime((string) $event->ttime)) }}</span>
                </div>
                <article class="event-details">
                    <img src="{{ base_url('/uploads/images/' . $event->photo) }}" alt="{{ $event->title }}">
                    <p>{{ $event->details }}</p>
                </article>
            </div>
        </div>
    </section>

    @if (customCompute($latestEvents))
        <section class="recent-part">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title">Recent Events</h2>
                </div>
                <div class="recent-carousel owl-carousel carousel-arrow">
                    @foreach ($latestEvents as $event)
                        <div class="event-card">
                            <a href="{{ base_url('frontend/event/' . $event->eventID) }}" class="event-media">
                                <img src="{{ base_url('/uploads/images/' . $event->photo) }}" alt="{{ $event->title }}">
                            </a>
                            <div class="event-content">
                                <h4><a href="{{ base_url('frontend/event/' . $event->eventID) }}">{{ $event->title }}</a></h4>
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
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
