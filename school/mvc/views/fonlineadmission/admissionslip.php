<section class="apply-part">
	<div class="container">
		<div class="apply-content">
			<h2 class="section-title">Apply as a Student</h2>
			<div class="result-group">
				<img src="images/avatar.jpg" alt="avatar">
				<ul class="result-list">
					<li class="result-item">
						<span>Admission ID:</span>
						<span>00000008</span>
					</li>
					<li class="result-item">
						<span>Full Name:</span>
						<span>Jonathon Doe</span>
					</li>
					<li class="result-item">
						<span>Apply Class:</span>
						<span>One</span>
					</li>
					<li class="result-item">
						<span>Email:</span>
						<span>jondoe@gmail.com</span>
					</li>
				</ul>
			</div>
			<ul class="result-list">
				<li class="result-item">
					<span>Phone No:</span>
					<span>01236547895</span>
				</li>
				<li class="result-item">
					<span>Date of Birth:</span>
					<span>13 Jul, 2017</span>
				</li>
				<li class="result-item">
					<span>Country:</span>
					<span>Bangladesh</span>
				</li>
				<li class="result-item">
					<span>Gender:</span>
					<span>Male</span>
				</li>
				<li class="result-item">
					<span>Apply Date:</span>
					<span>26 Jul, 2022</span>
				</li>
				<li class="result-item">
					<span>Religion:</span>
					<span>Muslim</span>
				</li>
				<li class="result-item">
					<span>Address:</span>
					<span>House:25, Road:05, Block:A Mirpur-2, Dhaka</span>
				</li>
			</ul>
			<button type="submit" class="btn btn-inline">print</button>
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