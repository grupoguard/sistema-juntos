<?php $__env->startSection('guest'); ?>
    
        <?php echo $__env->yieldContent('content'); ?>        
        <?php echo $__env->make('layouts.footers.guest.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/layouts/user_type/guest.blade.php ENDPATH**/ ?>