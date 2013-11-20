<?php

$notifications = pure::flash()->getMessages(true);
if(count($notifications) > 0):
    ?>

<ul class="notifications-panel">
    <?php foreach($notifications as $i => $n): ?>
    <li>
        <div class="alert alert-dismissable alert-<?php echo $n['level'] ?>">
            <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>
            <?php echo $n['message'] ?>
        </div>
    </li>
    <?php endforeach; ?>
</ul>

<?php endif; ?>