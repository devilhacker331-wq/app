@layout('views/layouts/master')
@section('css')
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/teachers.css') ?>">
@endsection
@section('content')
    <section class="teacher-part">
        <div class="container">
            <div class="teacher-content">
                <h1 class="section-page-title">Our Teachers</h1>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                    @if (customCompute($teachers))
                        @foreach ($teachers as $teacher)
                            <div class="col">
                                <div class="teacher-card">
                                    <div class="teacher-media">
                                        <img class="teacher-avater" src="{{ imagelink($teacher->photo) }}"
                                            alt="{{ $teacher->name }}">
                                        <div class="teacher-overlay">
                                      
                                            @if (!empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->facebook))
                                                <a target="_blank" href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->facebook }}"
                                                    class="lab-fill-facebook-round"></a>
                                            @endif

                                            @if (!empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->twitter))
                                                <a target="_blank" href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->twitter }}"
                                                    class="lab-fill-twitter-round"></a>
                                            @endif

                                            @if (!empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->linkedin))
                                                <a target="_blank" href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->linkedin }}"
                                                    class="lab-fill-linkedin-round"></a>
                                            @endif

                                            @if (!empty($sociallink[$teacher->usertypeID][$teacher->teacherID]->googleplus))
                                                <a target="_blank" href="{{ $sociallink[$teacher->usertypeID][$teacher->teacherID]->googleplus }}"
                                                    class="lab-fill-google-plus-round"></a>
                                            @endif 
                                        </div>
                                    </div>
                                    <div class="teacher-meta">
                                        <h3>{{ $teacher->name }}</h3>
                                        <p>{{ $teacher->designation }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
