<div>
    <div class="container-fluid py-4">
        <div class="card">
            <!--[if BLOCK]><![endif]--><?php if($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e($successMessage); ?>

                    <button type="button" class="btn-close" wire:click="$set('successMessage', null)"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e($errorMessage); ?>

                    <button type="button" class="btn-close" wire:click="$set('errorMessage', null)"></button>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <div class="card-header pb-0">
                <h5 class="mb-0">Lista de Produtos</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Nome/Código/Valor" 
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
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.create')): ?>
                            <a href="<?php echo e(route('admin.products.create')); ?>" class="btn bg-blue text-white">
                                + Novo Produto
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    Código
                                </th>
                                <th>
                                    Nome
                                </th>
                                <th>
                                    Valor
                                </th>
                                <th>
                                    Status
                                </th>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.edit')): ?>
                                    <th class="text-center">Editar</th>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.delete')): ?>
                                    <th class="text-center">Excluir</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php echo e($product->code); ?>

                                </td>
                                <td>
                                    <?php echo e($product->name); ?>

                                </td>
                                <td>
                                    R$<?php echo e(number_format($product->value, 2, ',', '.')); ?>

                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($product->status ? 'success' : 'danger'); ?>">
                                        <?php echo e($product->status ? 'Ativo' : 'Inativo'); ?>

                                    </span>
                                </td>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.edit')): ?>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>" class="btn btn-link text-dark fs-5 p-0 mb-0">
                                            <i class="fa fa-edit me-1"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.delete')): ?>
                                    <td class="text-center">
                                        <button wire:click="confirmDelete(<?php echo e($product->id); ?>)" class="btn btn-link text-danger text-gradient fs-5 p-0 mb-0">
                                            <i class="fa fa-trash me-1"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>

                <?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $products]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products)]); ?>
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
                        Tem certeza que deseja excluir este producte? Esta ação não pode ser desfeita.
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
<?php /**PATH /var/www/html/resources/views/livewire/products-list.blade.php ENDPATH**/ ?>