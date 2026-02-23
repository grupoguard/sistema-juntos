<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="<?php echo e($clientId ? 'col-lg-6' : 'col-lg-8'); ?>">
                    <h2 class="mb-0"><?php echo e($clientId ? 'Editar Cliente' : 'Novo Cliente'); ?></h2>
                </div>
                <div class="<?php echo e($clientId ? 'col-lg-3' : 'col-lg-4'); ?>">
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.select-group', ['groupId' => $client['group_id'],'group_id' => $client['group_id']]);

$__html = app('livewire')->mount($__name, $__params, 'lw-1507168580-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.group_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <!--[if BLOCK]><![endif]--><?php if($clientId): ?>
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="storeOrUpdate">
                <div class="row mb-3">
                    <div class="col-12">
                        <label>Nome do cliente<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="client.name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-3">
                        <label>Gênero<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.gender">
                            <option value="">Selecione</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="outros">Outros</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-lg-9">
                        <label>Nome da mãe<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="client.mom_name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.mom_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CPF<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cpf" wire:model.live="client.cpf">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.cpf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-3">
                        <label>RG</label>
                        <input type="text" class="form-control" id="rg" wire:model.live="client.rg">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.rg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-3">
                        <label>Data de nascimento<span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model="client.date_birth">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.date_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-3">
                        <label>Celular/Whatsapp</label>
                        <input type="tel" class="form-control" id="phone" wire:model="client.phone">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Estado Civil<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="client.marital_status">
                            <option value="">Selecione</option>
                            <option value="solteiro">Solteiro(a)</option>
                            <option value="casado">Casado(a)</option>
                            <option value="separado">Separado(a)</option>
                            <option value="divorciado">Divorciado(a)</option>
                            <option value="viuvo">Viúvo(a)</option>
                            <option value="uniao_estavel">União Estável</option>
                            <option value="nao_informado">Não informado</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.marital_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-9">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="client.email">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço do Cliente</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="client.zipcode">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.zipcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-7 ">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="client.address">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-2">
                        <label>Número<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="client.number">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model="client.complement">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.complement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-4">
                        <label>Bairro<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="client.neighborhood">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.neighborhood'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-4">
                        <label>Cidade<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="city" wire:model="client.city">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-1">
                        <label>Estado<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="state" wire:model="client.state">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <hr class="my-5">

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <h5 class="mb-0">Dependentes</h5>
                    </div>
                    <div class="col-lg-6 text-end">
                        <button type="button" class="btn bg-blue text-white" wire:click="addDependent">+ Adicionar Dependente</button>
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dependents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dependent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="row align-items-end mt-4">
                        <div class="col-md-12 mb-3">
                            <label>Nome do dependente<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.<?php echo e($index); ?>.name">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Grau de Parentesco<span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="dependents.<?php echo e($index); ?>.relationship">
                                <option value="">Selecione</option>
                                <option value="solteiro">Mãe/Pai</option>
                                <option value="casado">Irmão(ã)</option>
                                <option value="separado">Filho(a)</option>
                                <option value="separado">Cônjuge</option>
                                <option value="outro">Outro</option>
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.<?php echo e($index); ?>.relationship'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>CPF<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="dependents.<?php echo e($index); ?>.cpf">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>RG</label>
                            <input type="text" class="form-control" wire:model="dependents.<?php echo e($index); ?>.rg">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Data de nascimento<span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="dependents.<?php echo e($index); ?>.date_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Estado Civil<span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="dependents.<?php echo e($index); ?>.marital_status">
                                <option value="">Selecione</option>
                                <option value="solteiro">Solteiro(a)</option>
                                <option value="casado">Casado(a)</option>
                                <option value="separado">Separado(a)</option>
                                <option value="divorciado">Divorciado(a)</option>
                                <option value="viuvo">Viúvo(a)</option>
                                <option value="uniao_estavel">União Estável</option>
                                <option value="outro">Outro</option>
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.<?php echo e($index); ?>.marital_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nome da mãe<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.<?php echo e($index); ?>.mom_name">
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-danger" wire:click="removeDependent(<?php echo e($index); ?>)">X</button>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        <?php echo e($clientId ? 'Atualizar Cliente' : 'Cadastrar Cliente'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/client-form.blade.php ENDPATH**/ ?>