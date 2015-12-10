<?php $this->beginAssign(); ?>
<?php
$this->assign('_title', 'Books Management');
?>
<?php $this->endAssign(); ?>

<?php
/**
 * @var book abd\app\model\Book
 */
?>


<div class="row"><?php
    echo $this->insert('ui/error-block');
    echo $this->insert('ui/success-block');
    ?></div>

<div class="row">



    <form  action="books/update/<?php echo $book->getId(); ?>/" method="POST">


        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading" align='center'>
                    Edit Book
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12" align="center">
                            <div class="form-group">
                                <input name="title" type="text" class="form-control" placeholder="Title" value="<?php echo $book->getTitle(); ?>">
                            </div>
                            <div class="form-group">
                                <input name="author" type="text" class="form-control" placeholder="Author" value="<?php echo $book->getAuthor(); ?>">
                            </div>
                            <input type="hidden" name="csrf-token" value="<?= $_csrfToken; ?>">
                            <input type="hidden" name="http-method" value="PUT">
                            <input type="hidden" name="id" value="<?= $book->getId(); ?>">
                            <div class="col-lg-12" align="center">
                                <input type="submit" class="btn btn-success left-sbs sbmt" value="Add">
                            </div>
    </form>

</div>
</div>
</div>
</div>
</div>
</div>
