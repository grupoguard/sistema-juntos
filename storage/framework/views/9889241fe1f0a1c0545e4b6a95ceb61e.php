<div class="container-fluid py-4 ">
    <div class="row">
        <!-- Formulário -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e($editing ? 'Editar Categoria' : 'Nova Categoria'); ?></h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label>Nome da Categoria:</label>
                            <input type="text" class="form-control" wire:model="partnerCategorie.name">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['partnerCategorie.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <button type="submit" class="btn btn-lg btn-success"><?php echo e($editing ? 'Atualizar' : 'Salvar'); ?></button>
                        <!--[if BLOCK]><![endif]--><?php if($editing): ?>
                            <button type="button" class="btn btn-warning ms-2" wire:click="resetForm">Cancelar</button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Adicionais -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h4>Categorias Cadastrados</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partnerCategorie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($partnerCategorie->name); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="edit(<?php echo e($partnerCategorie->id); ?>)">✏️</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" wire:click="delete(<?php echo e($partnerCategorie->id); ?>)" onclick="confirm('Tem certeza que deseja excluir?') || event.stopImmediatePropagation()">🗑</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                    <!--[if BLOCK]><![endif]--><?php if($allCategories->isEmpty()): ?>
                        <p class="text-center mt-3">Nenhuma categoria cadastrada.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/partners-categories-list.blade.php ENDPATH**/ ?>