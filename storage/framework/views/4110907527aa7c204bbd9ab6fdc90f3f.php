<div>
    <label for="group">Selecione a cooperativa:<span class="text-danger">*</span></label>
    <select wire:model.live="group_id" id="group" class="form-control">
        <option value="">Selecione</option>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </select>
</div><?php /**PATH /var/www/html/resources/views/livewire/components/select-group.blade.php ENDPATH**/ ?>