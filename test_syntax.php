<?php
$business = ['image_url' => 'http://example.com/img.jpg'];
?>
<div class="profile-logo-img" style="background-image: url('<?= htmlspecialchars($business['image_url']) ?>')">
</div>