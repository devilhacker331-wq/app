@layout('views/layouts/master')
@section('css')
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/home.css') ?>">
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/gallery.css') ?>">
@endsection
@section('content')
    @if (customCompute($sliders))
        <section class="banner-carousel owl-carousel">
            @foreach ($sliders as $slider)
                <div class="banner-part"
                    style="background-image: url('{{ base_url('uploads/gallery/' . $slider->file_name) }}')">
                    <div class="banner-overlay">
                        <div class="container">
                            <div class="banner-group">
                                <div class="banner-content">
                                    <span>a tradition since {{ frontendData::get_frontend('hero_section_since') }}</span>
                                    <h1>{{ htmlspecialchars_decode($slider->file_title ?? '') }}</h1>
                                    <p>{{ htmlspecialchars_decode($slider->file_description ?? '') }}</p>
                                    @if (customCompute($pages_data->addmission_display))
                                        <a href="{{ $pages_data->addmission_display->addmission_url }}"
                                            class="btn btn-inline">apply now</a>
                                    @endif
                                </div>
                                @if (frontendData::get_frontend('hero_section_video'))
                                    <div class="banner-media">
                                        <a class="banner--media-video" target="_blank" data-autoplay="true"
                                            data-vbtype="video"
                                            href="{{ frontendData::get_frontend('hero_section_video') }}"><i
                                                class="fa-solid fa-play"></i></a>
                                        <div class="banner-media-content">
                                            <span>{{ frontendData::get_backend('sname') }}</span>
                                            <h3>campus tour</h3>
                                            <p>Watch Video</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    @if (frontendData::get_frontend('message_one') &&  frontendData::get_frontend('message_two') && frontendData::get_frontend('message_three') && frontendData::get_frontend('message_four'))
        <section class="feature-part">
            <div class="feature-card">
                <i class="lab-line-education"></i>
                <p>{{ frontendData::get_frontend('message_one') }}</p>
            </div>
            <div class="feature-card">
                <i class="lab-line-book"></i>
                <p>{{ frontendData::get_frontend('message_two') }}</p>
            </div>
            <div class="feature-card">
                <i class="lab-line-notebook"></i>
                <p>{{ frontendData::get_frontend('message_three') }}</p>
            </div>
            <div class="feature-card">
                <i class="lab-line-certificate"></i>
                <p>{{ frontendData::get_frontend('message_four') }}</p>
            </div>
        </section>
    @endif

    @if (customCompute($pages_data->about_display))
        <section class="about-part">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="about-content">
                            <h2 class="section-title">About School</h2>
                            <p>
                                {{ strip_tags(substr($pages_data->about_display->about_content, 0, 350)) }}
                                {{ strlen($pages_data->about_display->about_content) > 350 ? '...' : '' }}
                            </p>
                            <a href="{{ $pages_data->about_display->about_url }}" class="btn btn-outline">learn more</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-7">

                        <div class="about-media">
                            <img src="{{ imageLinkWithDefatulImage($pages_data->about_display->about_featured_image, 'about.jpg', 'uploads/gallery') }}"
                                alt="about">


                            <ul class="about-list">
                                <li class="about-item">
                                    <h4><span class="counter">{{ $pages_data->about_display->total_teacher }}</span>+</h4>
                                    <p>teachers</p>
                                </li>
                                <li class="about-item">
                                    <h4><span class="counter">{{ $pages_data->about_display->total_student }}</span>+</h4>
                                    <p>students</p>
                                </li>
                                <li class="about-item">
                                    <h4><span class="counter">{{ $pages_data->about_display->total_parent }}</span>+</h4>
                                    <p>graduates</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="apply-part">
        @if (customCompute($pages_data->addmission_display))
            <div class="container">
                <div class="apply-content">
                    <h2>{{ frontendData::get_frontend('admission_title') }}</h2>
                    <p>{{ frontendData::get_frontend('admission_description') }}</p>
                    <a href="{{ $pages_data->addmission_display->addmission_url }}" class="btn btn-outline">apply now</a>
                </div>
            </div>
        @endif
    </section>

    @if (frontendData::get_frontend('principle_message'))
        <section class="principal-part">
            <div class="container">
                <div class="principal-group">
                    <div class="principal-media">
                        <img src="{{ imageLinkWithDefatulImage(frontendData::get_frontend('photo'), 'default.png') }}"
                            alt="principal">
                    </div>
                    <div class="principal-content">
                        <h2 class="section-title">From the Principle</h2>
                        <p>“{{ frontendData::get_frontend('principle_message') }}”</p>
                        <dl>
                            <dt>{{ frontendData::get_frontend('principle_name') }}</dt>
                            <dd>Principle, {{ frontendData::get_backend('sname') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (customCompute($pages_data->teachers_display) && $pages_data->teachers_display->teachers)
        <section class="teacher-part">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title">Our Teachers</h2>
                </div>
                <div class="teacher-carousel owl-carousel carousel-arrow">
                    @foreach ($pages_data->teachers_display->teachers as $teacher)
                        <div class="teacher-card">
                            <div class="teacher-media">
                                <img class="teacher-avater"
                                    src="{{ imageLinkWithDefatulImage($teacher->photo, 'teacher.jpg') }}" alt="teacher">
                                <div class="teacher-overlay">

                                    @if (isset($sociallink[$teacher->usertypeID][$teacher->teacherID]->facebook) && !empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->facebook))
                                        <a href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->facebook }}"
                                            target="_blank" class="lab-fill-facebook-round"></a>
                                    @endif

                                    @if (isset($sociallink[$teacher->usertypeID][$teacher->teacherID]->twitter) && !empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->twitter))
                                        <a href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->twitter }}"
                                            target="_blank" class="lab-fill-twitter-round"></a>
                                    @endif

                                    @if (isset($sociallink[$teacher->usertypeID][$teacher->teacherID]->linkedin) && !empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->linkedin))
                                        <a href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->linkedin }}"
                                            target="_blank" class="lab-fill-linkedin-round"></a>
                                    @endif

                                    @if (isset($sociallink[$teacher->usertypeID][$teacher->teacherID]->googleplus) && !empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->googleplus))
                                        <a href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->googleplus }}"
                                            target="_blank" class="lab-fill-google-plus-round"></a>
                                    @endif

                                </div>
                            </div>
                            <div class="teacher-meta">
                                <h3>{{ $teacher->name }}</h3>
                                <p>{{ $teacher->designation }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="section-footer">
                    <a href="{{ $pages_data->teachers_display->teacher_url }}" class="btn btn-inline">See Our All Certified
                        Teacher's</a>
                </div>
            </div>
        </section>
    @endif

    @if (customCompute($pages_data->events_display) && $pages_data->events_display->events)
        <section class="event-part">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title">School Events</h2>
                    <a href="{{ $pages_data->events_display->event_url }}" class="section-btn">
                        <span>View All Events</span>
                        <i class="lni lni-arrow-right"></i>
                    </a>
                </div>

                <div class="event-carousel owl-carousel">
                    @foreach ($pages_data->events_display->events as $key => $event)
                        @if ($key < 3)
                            <div class="event-card">
                                <a href="{{ base_url('frontend/event/' . $event->eventID) }}" class="event-media">
                                    <img src="{{ imageLinkWithDefatulImage($event->photo, 'event.png') }}"
                                        alt="event">
                                </a>
                                <div class="event-content">
                                    <h4><a
                                            href="{{ base_url('frontend/event/' . $event->eventID) }}">{{ $event->title }}</a>
                                    </h4>
                                    <h5>{{ date('d M, Y', strtotime((string) $event->fdate)) . '-' . date('d M, Y', strtotime((string) $event->tdate)) }}
                                    </h5>
                                    <p>{{ substr($event->details, 0, 100) }}{{ strlen($event->details) > 100 ? '...' : '' }}
                                    </p>
                                    <a class="view" href="{{ base_url('frontend/event/' . $event->eventID) }}">
                                        <span>View Event</span>
                                        <i class="lni lni-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @else
                            <?php break; ?>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="social-part">
        <div class="social-overlay">
            <span></span>
            <h2>The School Community</h2>
            <p>Share your school pride with the world!</p>
            <nav>
                @if (!empty(frontendData::get_frontend('facebook')))
                    <a target="_blank" href="{{ frontendData::get_frontend('facebook') }}"
                        class="lab-fill-facebook-round"></a>
                @endif
                @if (!empty(frontendData::get_frontend('twitter')))
                    <a target="_blank" href="{{ frontendData::get_frontend('twitter') }}"
                        class="lab-fill-twitter-round"></a>
                @endif
                @if (!empty(frontendData::get_frontend('linkedin')))
                    <a target="_blank" href="{{ frontendData::get_frontend('linkedin') }}"
                        class="lab-fill-linkedin-round"></a>
                @endif
                @if (!empty(frontendData::get_frontend('youtube')))
                    <a target="_blank" href="{{ frontendData::get_frontend('youtube') }}"
                        class="lab-fill-youtube-round"></a>
                @endif
                @if (!empty(frontendData::get_frontend('google')))
                    <a target="_blank" href="{{ frontendData::get_frontend('google') }}"
                        class="lab-fill-google-plus-round"></a>
                @endif
            </nav>
        </div>
    </section>

    @if (customCompute($pages_data->gallery_display) && $pages_data->gallery_display->gallery_page)
        <section class="gallery-part">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title">School’s Gallery</h2>
                    <a href="{{ $pages_data->gallery_display->gallery_url }}" class="section-btn">
                        <span>see more</span>
                        <i class="lni lni-arrow-right"></i>
                    </a>
                </div>
                <div class="row">
                    @foreach ($pages_data->gallery_display->gallery_page as $image)
                        <div class="col-12 col-sm-6 col-md-4">
                            <a class="venobox-image" data-gall="gallery" href="{{ $image }}">
                                <img src="{{ $image }}" alt="gallery">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
