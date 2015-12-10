<?php $this->beginAssign(); ?>
<?php
$this->assign('_title', 'User Management');
?>
<?php $this->endAssign(); ?>

<form method="POST" action="users/create/" >


        Sample
        <?php
        echo $this->insert('ui/error-block');
        echo $this->insert('ui/success-block');
        ?>

<div class="input-group">
    <span class="input-group-addon" id="basic-addon1">Username</span>
    <input name="name" type="text" class="form-control" placeholder="Name">
</div>
<div class="input-group">
    <span class="input-group-addon" id="basic-addon1">Name</span>
    <input name="username" type="text" class="form-control" placeholder="Username" >
</div>

<div class="input-group">
    <span class="input-group-addon" id="basic-addon1">Password</span>
    <input name="password" type="text" class="form-control" placeholder="Password">
</div>

        <input type="hidden" name="csrf-token" value="<?= $_csrfToken; ?>">
    <input type="submit" class="btn btn-primary">

</form>