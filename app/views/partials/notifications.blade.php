<?php

$notifications = App::flash()->getMessages(true);
if(is_array($notifications) and (count($notifications) > 0)):
    ?>
<div class="container">
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
</div>

<?php endif; ?>