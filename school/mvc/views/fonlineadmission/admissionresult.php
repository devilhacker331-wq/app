<section class="apply-part smb-200">
	<div class="container">
		<div class="admission-card">
			<h2 class="section-title">Apply as a Student</h2>
			<div id="admissioninfo">
				<div class="result-group">
					<img src="<?= imagelink($admission['photo']) ?>" alt="avatar">
					<div class="admissioninfo" id="admissioninfo">
						<ul class="result-list">
							<li class="result-item">
								<span>Admission ID:</span>
								<span>
									<?php
									$admissionIDlen = strlen((string) $admission['onlineadmissionID']);
									$boxLimit = 8;

									if ($admissionIDlen >= $boxLimit) {
										$boxLimit += 2;
									}

									$zerolength = ($boxLimit - $admissionIDlen);
									if ($zerolength > 0) {
										for ($i = 1; $i <= $zerolength; $i++) {
											echo "<span class='idclass'>0";
										}
									}

									$admissionIDArray = str_split((string) $admission['onlineadmissionID']);
									if (customCompute($admissionIDArray)) {
										foreach ($admissionIDArray as $value) {
											echo $value . "</span>";
										}
									}

									?>
								</span>
							</li>
							<li class="result-item">
								<span>Full Name:</span>
								<span>
									<?= $admission['name'] ?>
								</span>
							</li>
							<li class="result-item">
								<span>Apply Class:</span>
								<span>
									<?= isset($classes[$admission['classesID']]) ? $classes[$admission['classesID']] : '' ?>
								</span>
							</li>
							<li class="result-item">
								<span>Email:</span>
								<span>
									<?= $admission['email'] ?>
								</span>
							</li>
						</ul>
					</div>
				</div>
				<ul class="result-list">
					<li class="result-item">
						<span>Phone No:</span>
						<span>
							<?= $admission['phone'] ?>
						</span>
					</li>
					<li class="result-item">
						<span>Date of Birth:</span>
						<span>
							<?= date('d M Y', strtotime((string) $admission['dob'])) ?>
						</span>
					</li>
					<li class="result-item">
						<span>Country:</span>
						<span>
							<?= isset($countrys[$admission['country']]) ? $countrys[$admission['country']] : '' ?>
						</span>
					</li>
					<li class="result-item">
						<span>Gender:</span>
						<span>
							<?= $admission['sex'] ?>
						</span>
					</li>
					<li class="result-item">
						<span>Apply Date:</span>
						<span>
							<?= date('d M Y', strtotime((string) $admission['create_date'])) ?>
						</span>
					</li>
					<li class="result-item">
						<span>Religion:</span>
						<span>
							<?= $admission['religion'] ?>
						</span>
					</li>
					<li class="result-item">
						<span>Address:</span>
						<span>
							<?= $admission['address'] ?>
						</span>
					</li>
					<li class="result-item">
						<span>Status:</span>

						<?php
						if ($admission['status'] == 1) {
							echo '<span class="result-status-approved">' . $this->lang->line('onlineadmission_approved') . '</span>';
						} elseif ($admission['status'] == 2) {
							echo '<span class="result-status-waiting">' . $this->lang->line('onlineadmission_waiting') . '</span>';
						} elseif ($admission['status'] == 3) {
							echo '<span class="result-status-decline">' . $this->lang->line('onlineadmission_decline') . '</span>';
						} else {
							echo '<span class="result-status-pending">' . $this->lang->line('onlineadmission_pending') . '</span>';
						}
						?>


					</li>
				</ul>
			</div>
			<button type="submit" class="section-btn full" onclick="javascript:printDiv('admissioninfo')">
				<span>print</span>
				<i class="lni lni-arrow-right"></i>
			</button>
		</div>
	</div>
</section>
<script type="text/javascript">
	function printDiv(divID) {
		var oldPage = document.body.innerHTML;
		var divElements = document.getElementById(divID).innerHTML;
		var footer = "<center><img src='<?= base_url('uploads/images/' . $siteinfos->photo) ?>' style='width:30px;' /></center>";
		var copyright = "<center><?= $siteinfos->footer ?> | hotline : <?= $siteinfos->phone ?></center>";
		document.body.innerHTML =
			"<html><head><title></title></head><body>" +
			"<center><img src='<?= base_url('uploads/images/' . $siteinfos->photo) ?>' style='width:50px;' /></center><p class=\"title\"><?= $siteinfos->sname ?></p><p style='margin-bottom:50px' class=\"title-desc\"><?= $siteinfos->address ?></p>" +
			divElements + footer + copyright + "</body>";

		window.print();
		document.body.innerHTML = oldPage;
		window.location.reload();
	}
</script>