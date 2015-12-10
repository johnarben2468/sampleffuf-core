<?php if (isset($_exception)) { ?>

    <div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert" aria-label="close">
        <span aria-hidden="true">&times;</span>
    </button>
    <?php
    if ($_exception instanceof \xvsys\validator\ValidationException) {
        $violations = $_exception->getViolations();
        foreach ($violations as $violation) {
            echo $violation->getMessage() . '<br>';
        }
    } else {
        echo $_exception->getMessage();
    }
    ?>
    <?php
}
        ?>