<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <!--[if BLOCK]><![endif]--><?php if(session('user_created')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">✅ Vendedor e usuário criados com sucesso!</h5>
                        <hr>
                        <p><strong>Credenciais de Acesso:</strong></p>
                        <p class="mb-1">
                            <strong>Email:</strong> <?php echo e(session('user_email')); ?><br>
                            <strong>Senha:</strong> <code class="fs-5"><?php echo e(session('user_password')); ?></code>
                        </p>
                        <hr>
                        <p class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>IMPORTANTE:</strong> Anote esta senha! Ela não será exibida novamente.
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <div class="<?php echo e($sellerId ? 'col-lg-6' : 'col-lg-8'); ?>">
                    <h2 class="mb-0"><?php echo e($sellerId ? 'Editar Consultor' : 'Novo Consultor'); ?></h2>
                </div>
                <div class="<?php echo e($sellerId ? 'col-lg-3' : 'col-lg-4'); ?>">
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.select-group', ['groupId' => $seller['group_id'],'group_id' => $seller['group_id']]);

$__html = app('livewire')->mount($__name, $__params, 'lw-3391063797-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.group_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <!--[if BLOCK]><![endif]--><?php if($sellerId): ?>
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="seller.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.status'];
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
                    <div class="col-md-9">
                        <label>Nome do consultor<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seller.name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.name'];
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
                        <input type="date" class="form-control" wire:model="seller.date_birth">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.date_birth'];
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
                        <input type="text" class="form-control" id="cpf" wire:model.live="seller.cpf">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.cpf'];
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
                        <input type="text" class="form-control" id="rg" wire:model.live="seller.rg">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.rg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-3">
                        <label>Telefone</label>
                        <input type="tel" class="form-control" id="phone" wire:model="seller.phone">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-3">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="seller.email">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.email'];
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
                    <div class="col-lg-4">
                        <label>Tipo de comissão<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="seller.comission_type">
                            <option value="">Selecione</option>
                            <option value="0">Padrão</option>
                            <option value="1">Fixo (R$)</option>
                            <option value="2">Porcentagem (%)</option>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label>Valor da comissão<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="seller.comission_value">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.comission_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-lg-4">
                        <label>Recorrência (meses)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" wire:model="seller.comission_recurrence">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.comission_recurrence'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if(!$sellerId): ?>
                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Dados de acesso</h5>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                       <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="createUser" id="createUser">
                            <label class="form-check-label" for="createUser">
                                <strong>Criar usuário para este consultor</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            O usuário terá acesso ao sistema como vendedor e poderá gerenciar seus pedidos.
                        </small>
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if($createUser): ?>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Email do usuário:</strong> <?php echo e($group['email'] ?? 'Preencha o email acima'); ?>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha Customizada (Opcional)</label>
                            <input type="password" wire:model="userPassword" class="form-control <?php $__errorArgs = ['userPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                placeholder="Deixe vazio para gerar automaticamente">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['userPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <small class="text-muted">
                                Se deixar vazio, uma senha aleatória será gerada e exibida após o cadastro.
                            </small>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]--> 
                </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <hr class="my-5">

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço do Consultor</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="seller.zipcode">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.zipcode'];
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
                        <input type="text" class="form-control" data-field="address" wire:model="seller.address">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.address'];
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
                        <input type="number" class="form-control" wire:model="seller.number">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.number'];
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
                        <input type="text" class="form-control" wire:model="seller.complement">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.complement'];
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
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="seller.neighborhood">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.neighborhood'];
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
                        <input type="text" class="form-control" data-field="city" wire:model="seller.city">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.city'];
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
                        <input type="text" class="form-control" data-field="state" wire:model="seller.state">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller.state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-lg btn-success" wire:click="storeOrUpdate">
                        <?php echo e($sellerId ? 'Atualizar Consultor' : 'Cadastrar Consultor'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/seller-form.blade.php ENDPATH**/ ?>