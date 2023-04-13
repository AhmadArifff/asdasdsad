<?= csrf_field(); ?>
<?php
if (session()->getFlashdata('message')) {
?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message') ?>
    </div>
<?php
}
?>
<form method="post" action="<?= base_url('admin/admincontrollers/importfileexcelperiodepembayaran') ?>" enctype="multipart/form-data">
    <?= csrf_field(); ?>
    <div class="form-group">
        <label>Import File Excel Data Periode Pembayaran</label>
        <input type="file" name="file" class="form-control" id="file" required accept=".xls, .xlsx" /></p>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit"><i class="fas fa-file"></i> Upload</button>
        <a href="<?= base_url('admin/admincontrollers/exporttemplatefileexcelperiodepembayaran') ?>" class="btn btn-primary"><i class="fas fa-download"></i> Template Data Export</a>
    </div>
</form>