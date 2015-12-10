<?php if(isset($_success)) {
    ?>

    <div class="alert alert-success">
           <button type="button" class="close" data-dismiss="alert" aria-label="close">
               <span aria-hidden ="true" >&times;</span>
           </button>
        <?= $_success; ?>
    </div>

<?php } ?>