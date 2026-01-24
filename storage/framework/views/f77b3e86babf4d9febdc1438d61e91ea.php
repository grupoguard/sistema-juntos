<?php $__env->startSection('auth'); ?>
    <?php if(\Request::is('static-sign-up')): ?> 
        <?php echo $__env->make('layouts.navbars.guest.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldContent('content'); ?>
        <?php echo $__env->make('layouts.footers.guest.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <?php elseif(\Request::is('static-sign-in')): ?> 
        <?php echo $__env->make('layouts.navbars.guest.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
        <?php echo $__env->make('layouts.footers.guest.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <?php else: ?>
        <?php if(\Request::is('profile')): ?>  
            <?php echo $__env->make('layouts.navbars.auth.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
                <?php echo $__env->make('layouts.navbars.auth.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>

        <?php elseif(\Request::is('virtual-reality')): ?> 
            <?php echo $__env->make('layouts.navbars.auth.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="border-radius-xl mt-3 mx-3 position-relative" style="background-image: url('../assets/img/vr-bg.jpg') ; background-size: cover;">
                <?php echo $__env->make('layouts.navbars.auth.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <main class="main-content mt-1 border-radius-lg">
                    <?php echo $__env->yieldContent('content'); ?>
                </main>
            </div>
            <?php echo $__env->make('layouts.footers.auth.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php else: ?>
            <?php echo $__env->make('layouts.navbars.auth.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg <?php echo e((Request::is('rtl') ? 'overflow-hidden' : '')); ?>">
                <?php echo $__env->make('layouts.navbars.auth.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="container-fluid py-4">
                    <?php echo $__env->yieldContent('content'); ?>
                    <?php echo $__env->make('layouts.footers.auth.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </main>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/layouts/user_type/auth.blade.php ENDPATH**/ ?>