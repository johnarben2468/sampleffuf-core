<?php
/**
 * @var users abd\app\model\Sampletable
 */
?>


<div class="panel panel-default">
    <div class="panel-body">
        <table>
            <tr><th>Users</th><th>Options</th></tr>
            <?php foreach($users as $user) { ?>
                <tr>
                    <td><?php echo $user->getName(); ?></td>
                    <td><a href="users/delete/<?php echo $user->getId() ?>">Delete</a></td>
                </tr>

            <?php } ?>
        </table>
    </div>
</div>
