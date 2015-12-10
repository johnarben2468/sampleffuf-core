<?php $this->beginAssign(); ?>
<?php
$this->assign('_title', 'Books Management');
?>
<?php $this->endAssign(); ?>

<?php
/**
 * @var books abd\app\model\Book
 */
?>


<div class="row"><?php
    echo $this->insert('ui/error-block');
    echo $this->insert('ui/success-block');
    ?></div>

<div class="row">

    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading" align='center'>
                Books
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12" align="center">


                        <div class="table-responsive">
                            <table  id="tablesorter-table"  align="center" style="color:black" class="table table-striped display tablesorter" id="main-table" border=0>
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Action </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($books as $book){
                                ?>
                                <tr >
                                    <td><?php echo $book->getTitle()?></td>
                                    <td><?php echo $book->getAuthor()?></td>
                                    <td>
                                        <a href="books/edit/<?php echo $book->getId()?>">
                                            <button class="btn btn-primary" ><i class="fa fa-pencil-square-o"></i></button>
                                        </a>
                                        <a href="books/delete/<?php echo $book->getId()?>">
                                            <button class="btn btn-warning" ><i class="fa fa-trash-o"></i></button>
                                        </a>
                                    </td>

                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <form method="POST" action="books/create/">


    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading" align='center'>
                Add Book
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12" align="center">

                        <div class="form-group">
                            <input name="title" type="text" class="form-control" placeholder="Title">
                        </div>
                        <div class="form-group">
                            <input name="author" type="text" class="form-control" placeholder="Author">
                        </div>
                        <input type="hidden" name="csrf-token" value="<?= $_csrfToken; ?>">
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
