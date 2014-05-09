<aside class="bg-gallery" role="complementary">
    <div class="bg-gallery-items cycle-slideshow"
         data-cycle-fx="fade"
         data-cycle-timeout="8000"
         data-cycle-pause-on-hover="false"
         data-cycle-prev=".bg-gallery-prev"
         data-cycle-next=".bg-gallery-next"
         data-pager=".bg-gallery-bullets"
         data-pager-template="<span></span>"
         data-cycle-slides="> .bg-gallery-item">

        <div class="bg-gallery-item bg-gallery-item-img" style="background-image:url(<?php echo asset('img/gallery/demo1.jpg'); ?>)"></div>
        <div class="bg-gallery-item bg-gallery-item-img" style="background-image:url(<?php echo asset('img/gallery/demo2.jpg'); ?>)"></div>

    </div>
    <div class="bg-gallery-overlay"></div>
</aside>
<a href="#" rel="nofollow" class="bg-gallery-btn bg-gallery-prev"><i class="fa fa-arrow-left"></i></a>
<a href="#" rel="nofollow" class="bg-gallery-btn bg-gallery-next"><i class="fa fa-arrow-right"></i></a>