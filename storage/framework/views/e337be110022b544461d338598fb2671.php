<?php $__env->startSection('content'); ?>
  <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header p-3 pb-0">
                        <div class="row">
                            <div class="col-12 d-flex align-items-center">
                                <h6 class="mb-0">Gerar arquivo de processamento</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <p class="text-sm">
                            Bsta subir uma planilha seguindo <a href="#">esse formato</a> e o sistema vai gerar o arquivo TXT para a EDP.
                        </p> 

                        <form action="<?php echo e(route('evidences.gerartxt')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label for="example-text-input" class="form-control-label">Selecione a planilha:</label>
                                <input class="form-control" type="file" name="planilha" required>
                            </div>
    
                            <input type="submit" class="btn btn-icon btn-3 btn-warning" value="Enviar">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.user_type.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/pages/processamento.blade.php ENDPATH**/ ?>