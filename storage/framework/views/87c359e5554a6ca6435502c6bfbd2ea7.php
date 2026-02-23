<div class="container-fluid py-4 ">
    <div class="row">
        <!-- Formulário -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e($editing ? 'Editar Adicional' : 'Novo Adicional'); ?></h4>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label>Nome do Adicional:</label>
                            <input type="text" class="form-control" wire:model="aditional.name">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['aditional.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($editing): ?>
                            <div class="mb-3">
                                <label>Status:</label>
                                <select class="form-control" wire:model="aditional.status">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

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
                    <h4>Adicionais Cadastrados</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $allAdditionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aditional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($aditional->name); ?></td>
                                    <td>
                                        <!--[if BLOCK]><![endif]--><?php if($aditional->status): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="edit(<?php echo e($aditional->id); ?>)">✏️</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" wire:click="delete(<?php echo e($aditional->id); ?>)" onclick="confirm('Tem certeza que deseja excluir?') || event.stopImmediatePropagation()">🗑</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                    <!--[if BLOCK]><![endif]--><?php if($allAdditionals->isEmpty()): ?>
                        <p class="text-center mt-3">Nenhum adicional cadastrado.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/additionals-list.blade.php ENDPATH**/ ?>