<div>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Lista de Cooperativas</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar cooperativa..." 
                            wire:model.live="search"
                        >
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" wire:model.lazy="statusFilter">
                            <option value="">
                                Todos
                            </option>
                            <option value="1">
                                Ativo
                            </option>
                            <option value="0">
                                Inativo
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <a 
                            href="<?php echo e(route('admin.groups.create')); ?>" 
                            class="btn bg-blue text-white">
                                + Nova Cooperativa
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    ID
                                </th>
                                <th>
                                    Nome
                                </th>
                                <th>
                                    Telefone
                                </th>
                                <th>
                                    Status
                                </th>
                                <th class="text-center">
                                    Editar
                                </th>
                                <th class="text-center">
                                    Excluir
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php echo e($group->id); ?>

                                </td>
                                <td>
                                    <?php echo e($group->name); ?>

                                </td>
                                <td>
                                    <?php echo e($group->phone); ?>

                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($group->status ? 'success' : 'danger'); ?>">
                                        <?php echo e($group->status ? 'Ativo' : 'Inativo'); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <a 
                                        href="<?php echo e(route('admin.groups.edit', $group->id)); ?>" 
                                        class="btn btn-link text-dark fs-5 p-0 mb-0">
                                        <i class="fa fa-edit me-1"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button 
                                        wire:click="confirmDelete(<?php echo e($group->id); ?>)" 
                                        class="btn btn-link text-danger text-gradient fs-5 p-0 mb-0"
                                    >
                                        <i class="fa fa-trash me-1"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>

                <?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $groups]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groups)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>

            </div>
        </div>
    </div>
    <!--[if BLOCK]><![endif]--><?php if($confirmingDelete): ?>
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja excluir esta cooperativa? Esta ação não pode ser desfeita.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmingDelete', false)">Cancelar</button>
                        <button type="button" class="btn btn-danger" wire:click="delete()">Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /var/www/html/resources/views/livewire/groups-list.blade.php ENDPATH**/ ?>