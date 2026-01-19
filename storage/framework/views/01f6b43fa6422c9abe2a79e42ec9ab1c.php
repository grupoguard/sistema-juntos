<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <!--[if BLOCK]><![endif]--><?php if(session('user_created')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">✅ Cooperativa e usuário criados com sucesso!</h5>
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
                <div class="<?php echo e($groupId ? 'col-lg-9' : 'col-lg-12'); ?>">
                    <h2 class="mb-0"><?php echo e($groupId ? 'Editar Cooperativa' : 'Nova Cooperativa'); ?></h2>
                </div>
                <!--[if BLOCK]><![endif]--><?php if($groupId): ?>
                    <div class="col-lg-3">
                        <label>Status<span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="group.status">
                            <option value="" disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.status'];
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
                    <div class="col-lg-6">
                        <label>Razão Social<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="group.group_name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.group_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-lg-6">
                        <label>Nome Fantasia<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="group.name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.name'];
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
                    <div class="col-md-4">
                        <label>CNPJ<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cnpj" wire:model.live="group.document">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label>Telefone<span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" wire:model="group.phone">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-4">
                        <label>Celular/Whatsapp</label>
                        <input type="tel" class="form-control" id="whatsapp" wire:model="group.whatsapp">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.whatsapp'];
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
                    <div class="col-md-6">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="group.email">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-6">
                        <label>Site</label>
                        <input type="text" class="form-control" id="site" wire:model.live="group.site">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.site'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if(!$groupId): ?>
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
                                <strong>Criar usuário para esta cooperativa</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            O usuário terá acesso ao sistema como COOP e poderá gerenciar seus vendedores e pedidos.
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

                <div class="row mb-3">
                    <div class="col-12">
                        <h5 class="mb-0">Endereço da Cooperativa</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>CEP<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" data-cep id="zipcode" wire:model="group.zipcode">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.zipcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="col-md-7">
                        <label>Endereço<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" data-field="address" wire:model="group.address">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.address'];
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
                        <input type="number" class="form-control" wire:model="group.number">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.number'];
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
                        <input type="text" class="form-control" wire:model="group.complement">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.complement'];
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
                        <input type="text" class="form-control" data-field="neighborhood" wire:model="group.neighborhood">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.neighborhood'];
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
                        <input type="text" class="form-control" data-field="city" wire:model="group.city">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.city'];
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
                        <input type="text" class="form-control" data-field="state" wire:model="group.state">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['group.state'];
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
                        <?php echo e($groupId ? 'Atualizar Cooperativa' : 'Cadastrar Cooperativa'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/group-form.blade.php ENDPATH**/ ?>