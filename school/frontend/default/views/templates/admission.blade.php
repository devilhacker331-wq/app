@layout('views/layouts/master')
@section('css')
    <link rel="stylesheet" href="<?= base_url($frontendThemePath . 'assets/css/expanded/admission.css') ?>">
@endsection
@section('content')
    <section class="admission-part">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <h1 class="section-page-title">Admission</h1>
                </div>
                <div class="col-12 col-lg-6">
                    <p class="admission-describe">
                        If you have any questions, please don't hesitate to get in touch via
                        {{ frontendData::get_backend('phone') }} or
                        <span>{{ frontendData::get_backend('email') }}</span>
                    </p>
                </div>
                <div class="col-12 col-lg-10">
                    <div class="admission-content">
                        <h2 class="section-title">Get Result</h2>
                        <form class="row" id="result">
                            <div class="col-12 col-md-5">
                                <label for="id" class="form-label required">Admission ID</label>
                                <input type="text" name="onlineadmissionID" id="onlineadmissionID" class="form-control">
                            </div>
                            <div class="col-12 col-md-5">
                                <label for="phone" class="form-label required">Phone No</label>
                                <input type="number" name="phone" id="phone" class="form-control">
                            </div>
                            <div class="col-12 col-md-2">
                                <button type="button" class="btn btn-inline" id="result_btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="apply-part">
        <div class="container">
            <div class="apply-content" id="apply_student">
                <h2 class="section-title">Apply as a Student</h2>
                <form class="apply-form" id="applyForm">
                    <div class="form-group">
                        <label for="name" class="form-label required">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="classesID" class="form-label required">Apply Class</label>
                        <select class="form-control" name="classesID" id="classesID" required>
                            @if ($classes)
                                @foreach ($classes as $class)
                                    <option value="{{ $class->classesID }}">{{ $class->classes }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dob" class="form-label required">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" id="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="religion" class="form-label required">Religion</label>
                        <input type="text" class="form-control" name="religion" id="religion" required>
                    </div>
                    <div class="form-group">
                        <label for="sex" class="form-label required">Gender</label>
                        <select class="form-control" name="sex" id="sex" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label required">Phone</label>
                        <input type="number" name="phone" id="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="country" class="form-label required">Country</label>
                        <select class="form-control" name="country" id="country" required>
                            @foreach ($allcountry as $key => $country)
                                <option value="{{ $key }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label required">Address</label>
                        <input type="text" class="form-control" name="address" id="address" required>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="form-label required">Photo</label>
                        <div class="form-file-group">
                            <input class="form-control" type="file" name="photo" id="photo">
                            <label class="form-upload" for="photo">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 32V20H10L24 6L38 20H30V32H18ZM10 40V36H38V40H10Z" fill="#FA5E01" />
                                </svg>
                            </label>
                            <div class="form-file">
                                <span class="form-value"></span>
                                <i class="form-close fa-solid fa-xmark"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file" class="form-label required">Upload Document</label>
                        <div class="form-file-group">
                            <input class="form-control" type="file" name="file" id="file">
                            <label class="form-upload" for="photo">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 32V20H10L24 6L38 20H30V32H18ZM10 40V36H38V40H10Z" fill="#FA5E01" />
                                </svg>
                            </label>
                            <div class="form-file">
                                <span class="form-value"></span>
                                <i class="form-close fa-solid fa-xmark"></i>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-inline" id="submit">Apply</button>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        $('#submit').click(function() {
            var formData = new FormData($('#applyForm')[0]);
            $.ajax({
                type: 'POST',
                url: "<?= base_url('fonlineadmission/add') ?>",
                data: formData,
                async: true,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status == false) {
                        $.each(response, function(index, value) {
                            if (index != 'status') {
                                toastr["error"](value)
                                toastr.options = {
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": false,
                                    "positionClass": "toast-top-right",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "500",
                                    "hideDuration": "500",
                                    "timeOut": "5000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut"
                                }
                            }
                        });
                    } else {
                        if (response.render != '') {
                            window.location.href = '/frontend/admission/' + response.admissionID;
                        } else {
                            toastr["error"]('Admission Field')
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": false,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "500",
                                "hideDuration": "500",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            }
                        }
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('#result_btn').click(function(e) {
            e.preventDefault();
            var error = true;
            if ($('#onlineadmissionID').val() == '') {
                error = false;
                toastr["error"]('Admission ID field are required')
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }

            }
            if ($('#phone').val() == '') {
                error = false;
                toastr["error"]('Phone Number field are required')
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "500",
                    "hideDuration": "500",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                }
            }
            if (error) {
                $.ajax({
                    type: 'POST',
                    url: "<?= base_url('fonlineadmission/getAdmission') ?>",
                    data: {
                        'onlineadmissionID': $('#onlineadmissionID').val(),
                        'phone': $('#phone').val()
                    },
                    dataType: "html",
                    success: function(data) {
                        var response = JSON.parse(data);
                        if (response.status == false) {
                            $.each(response, function(index, value) {
                                if (index != 'status') {
                                    toastr["error"](value)
                                    toastr.options = {
                                        "closeButton": true,
                                        "debug": false,
                                        "newestOnTop": false,
                                        "progressBar": false,
                                        "positionClass": "toast-top-right",
                                        "preventDuplicates": false,
                                        "onclick": null,
                                        "showDuration": "500",
                                        "hideDuration": "500",
                                        "timeOut": "5000",
                                        "extendedTimeOut": "1000",
                                        "showEasing": "swing",
                                        "hideEasing": "linear",
                                        "showMethod": "fadeIn",
                                        "hideMethod": "fadeOut"
                                    }
                                }
                            });
                        } else {
                            window.location.href = '/frontend/admission/' + response.onlineadmissionID;
                        }
                    },
                });
            }

        });
    </script>
@endsection
