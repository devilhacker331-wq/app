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
            <div class="apply-content">
                <div id="apply_info">
                    <h2 class="section-title mb-4">Apply as a Student</h2>

                    @if (customCompute($admission))
                        <div class="result-group">
                            <img src="{{ imageLinkWithDefatulImage($admission->photo, 'default.png') }}" alt="avatar">

                            <ul class="result-list">
                                <li class="result-item">
                                    <span>Admission ID:</span>
                                    <span>
                                        <?php
                                        $admissionID = (string) $admission->onlineadmissionID;
                                        $admissionIDlen = strlen($admissionID);
                                        $boxLimit = max(8, $admissionIDlen + 2);
                                        $zerolength = max(0, $boxLimit - $admissionIDlen);
                                        echo str_repeat("<span class='idclass'>0", $zerolength);
                                        if (!empty($admissionID)) {
                                            foreach (str_split($admissionID) as $value) {
                                                echo "$value</span>";
                                            }
                                        }
                                        ?>
                                    </span>
                                </li>
                                <li class="result-item">
                                    <span>Full Name:</span>
                                    <span><?= $admission->name ?></span>
                                </li>
                                <li class="result-item">
                                    <span>Apply Class:</span>
                                    <span>
                                        <?= isset($classes[$admission->classesID]) ? $classes[$admission->classesID]->classes : '' ?>
                                    </span>
                                </li>
                                <li class="result-item">
                                    <span>Email:</span>
                                    <span><?= $admission->email ?></span>
                                </li>
                            </ul>
                        </div>
                        <ul class="result-list">
                            <li class="result-item">
                                <span>Phone No:</span>
                                <span><?= $admission->phone ?></span>
                            </li>
                            <li class="result-item">
                                <span>Date of Birth:</span>
                                <span>
                                    <?= date('d M Y', strtotime((string) $admission->dob)) ?>
                                </span>
                            </li>
                            <li class="result-item">
                                <span>Country:</span>
                                <span>
                                    <?= isset($country[$admission->country]) ? $country[$admission->country] : '' ?>
                                </span>
                            </li>
                            <li class="result-item">
                                <span>Gender:</span>
                                <span><?= $admission->sex ?></span>
                            </li>
                            <li class="result-item">
                                <span>Apply Date:</span>
                                <span>
                                    <?= date('d M Y', strtotime((string) $admission->create_date)) ?>
                                </span>
                            </li>
                            <li class="result-item">
                                <span>Religion:</span>
                                <span><?= $admission->religion ?></span>
                            </li>
                            <li class="result-item">
                                <span>Address:</span>
                                <span>
                                    <?= $admission->address ?>
                                </span>
                            </li>
                            <li class="result-item">
                                <span>Status:</span>
                                <span>
                                    <?php
                                    if ($admission->status == 1) {
                                        echo '<span class=" result-status result-status-approved">Approved</span>';
                                    } elseif ($admission->status == 2) {
                                        echo '<span class=" result-status result-status-waiting">Waiting</span>';
                                    } elseif ($admission->status == 3) {
                                        echo '<span class=" result-status result-status-decline">Decline</span>';
                                    } else {
                                        echo '<span class=" result-status result-status-pending">Pending</span>';
                                    }
                                    ?>
                                </span>
                            </li>
                        </ul>
                    @endif
                </div>
                <button type="submit" class="btn btn-inline" id="printButton"
                    onclick="javascript:printDiv('apply_info')">print</button>
            </div>
          
        </div>
    </section>
@endsection 

@section('js')
    <script type="text/javascript">
        function printDiv(divID) {
            var oldPage = document.body.innerHTML;
            var divElements = document.getElementById(divID).innerHTML;
            var footer =
                "<center><img src='<?= base_url('uploads/images/' . $siteinfos->photo) ?>' style='width:30px;' /></center>";
            var copyright = "<center><?= $siteinfos->footer ?> | hotline : <?= $siteinfos->phone ?></center>";
            document.body.innerHTML =
                "<html><head><title></title></head><body>" +
                "<center><img src='<?= base_url('uploads/images/' . $siteinfos->photo) ?>' style='width:50px;' /></center><p class=\"title\"><?= $siteinfos->sname ?></p><p style='margin-bottom:50px' class=\"title-desc\"><?= $siteinfos->address ?></p>" +
                divElements + footer + copyright + "</body>";

            window.print();
            document.body.innerHTML = oldPage;
            window.addEventListener("afterprint", function() {
                window.location.reload();
            });
        }
    </script>
@endsection
