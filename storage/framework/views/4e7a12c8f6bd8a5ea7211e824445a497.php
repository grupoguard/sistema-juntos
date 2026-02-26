<div class="container-fluid py-4">
    
    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('message')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-0">Cadastro Facilitado de Pedido</h4>
                    <small class="text-muted">
                        Preenchimento passo a passo (ideal para celular)
                    </small>
                </div>

                <div class="mt-2 mt-md-0">
                    <!--[if BLOCK]><![endif]--><?php if($draftId): ?>
                        <span class="badge badge-info">Rascunho #<?php echo e($draftId); ?></span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Novo rascunho</span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <div class="card-body">
            
            <div class="mb-3">
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo e(($step / 8) * 100); ?>%;"
                         aria-valuenow="<?php echo e($step); ?>" aria-valuemin="1" aria-valuemax="8"></div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Etapa <?php echo e($step); ?> de 8</small>
                </div>
            </div>

            
            <div class="mb-0">
                <div class="d-flex flex-wrap">
                    <?php
                        $stepNames = [
                            1 => 'Cliente',
                            2 => 'Endereço',
                            3 => 'Pedido',
                            4 => 'Dependentes',
                            5 => 'Cobrança',
                            6 => 'Documento',
                            7 => 'Comprovante',
                            8 => 'Resumo',
                        ];
                    ?>

                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $stepNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                class="btn btn-sm mr-2 mb-2 <?php echo e($step == $num ? 'btn-primary' : 'btn-outline-secondary'); ?>"
                                wire:click="goToStep(<?php echo e($num); ?>)"
                                <?php echo e($num > $step ? 'disabled' : ''); ?>>
                            <?php echo e($num); ?>. <?php echo e($label); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Existem campos inválidos:</strong>
            <ul class="mb-0 mt-2">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </ul>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="card">
        <div class="card-body">

            
            <!--[if BLOCK]><![endif]--><?php if($step === 1): ?>
                <h5 class="mb-4">1. Dados do Cliente</h5>

                <div class="row">
                    <div class="col-md-4">
                        <label>CPF <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model.defer="client.cpf" placeholder="Digite o CPF">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" wire:click="lookupClientByCpf">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.cpf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-8 d-flex align-items-end">
                        <!--[if BLOCK]><![endif]--><?php if($clientLookupMessage): ?>
                            <div class="alert <?php echo e($clientFound ? 'alert-info' : 'alert-secondary'); ?> w-100 mb-0 mt-3 mt-md-0">
                                <?php echo e($clientLookupMessage); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label>Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="client.name">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-3">
                        <label>Gênero <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="client.gender">
                            <option value="">Selecione</option>
                            <option value="MASCULINO">Masculino</option>
                            <option value="FEMININO">Feminino</option>
                            <option value="OUTRO">Outro</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-3">
                        <label>RG</label>
                        <input type="text" class="form-control" wire:model.defer="client.rg">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.rg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>Data de nascimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model.defer="client.date_birth">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.date_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-3">
                        <label>WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="client.phone">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" class="form-control" wire:model.defer="client.email">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['client.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 2): ?>
                <h5 class="mb-4">2. Endereço do Cliente</h5>

                <div class="alert alert-light border">
                    <strong>Observação:</strong> se o cliente já tiver endereço cadastrado, os campos aparecem preenchidos e continuam editáveis.
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>CEP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model.defer="address.zipcode" placeholder="Digite o CEP">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" wire:click="fetchAddressByCep">
                                    Buscar CEP
                                </button>
                            </div>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.zipcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-7">
                        <label>Endereço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.address">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-2">
                        <label>Número <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.number">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>Complemento</label>
                        <input type="text" class="form-control" wire:model.defer="address.complement">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.complement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-4">
                        <label>Bairro <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.neighborhood">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.neighborhood'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-4">
                        <label>Cidade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.city">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-1">
                        <label>UF <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model.defer="address.state" maxlength="2">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address.state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 3): ?>
                <h5 class="mb-4">3. Dados do Pedido</h5>

                <div class="row">
                    <div class="col-md-6">
                        <label>Consultor <span class="text-danger">*</span></label>

                        <!--[if BLOCK]><![endif]--><?php if($this->isSellerUser()): ?>
                            <?php
                                $selectedSeller = collect($sellers)->firstWhere('id', (int)($orderData['seller_id'] ?? 0));
                            ?>
                            <input type="text" class="form-control" value="<?php echo e($selectedSeller['name'] ?? 'Consultor vinculado ao usuário'); ?>" disabled>
                            <small class="text-muted">Como você está logado como consultor, este campo é definido automaticamente.</small>
                        <?php else: ?>
                            <select class="form-control" wire:model.defer="orderData.seller_id">
                                <option value="">Selecione</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($seller['id']); ?>"><?php echo e($seller['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['orderData.seller_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-6">
                        <label>Produto <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.change="orderData.product_id">
                            <option value="">Selecione</option>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product['id']); ?>"><?php echo e($product['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['orderData.product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Adicionais</label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aditional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           value="<?php echo e($aditional['id']); ?>"
                                           id="aditional_<?php echo e($aditional['id']); ?>"
                                           wire:model.defer="orderData.selectedAdditionals">
                                    <label class="form-check-label" for="aditional_<?php echo e($aditional['id']); ?>">
                                        <?php echo e($aditional['name']); ?>

                                        <!--[if BLOCK]><![endif]--><?php if(isset($aditional['value'])): ?>
                                            - R$ <?php echo e(number_format((float)$aditional['value'], 2, ',', '.')); ?>

                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <small class="text-muted">Nenhum adicional disponível.</small>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label>Valor da adesão</label>
                        <input type="text" class="form-control" wire:model.defer="orderData.accession">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['orderData.accession'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-3">
                        <label>Pagamento da adesão</label>
                        <select class="form-control" wire:model.defer="orderData.accession_payment">
                            <option value="">Selecione</option>
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="PIX">Pix</option>
                            <option value="CARTAO">Cartão</option>
                            <option value="BOLETO">Boleto</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['orderData.accession_payment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 4): ?>
                <h5 class="mb-4">4. Dependentes (Opcional)</h5>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" wire:click="addDependent">
                        + Adicionar dependente
                    </button>
                </div>

                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $dependents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dependent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="card mb-3 border">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Dependente <?php echo e($index + 1); ?></strong>
                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeDependent(<?php echo e($index); ?>)">
                                Remover
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.name">
                                </div>
                                <div class="col-md-3">
                                    <label>CPF</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.cpf">
                                </div>
                                <div class="col-md-3">
                                    <label>RG</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.rg">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label>Data de nascimento</label>
                                    <input type="date" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.date_birth">
                                </div>
                                <div class="col-md-3">
                                    <label>Parentesco</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.relationship">
                                </div>
                                <div class="col-md-6">
                                    <label>Nome da mãe</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.mom_name">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>Estado civil</label>
                                    <input type="text" class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.marital_status">
                                </div>
                                <!--[if BLOCK]><![endif]--><?php if(!empty($additionals)): ?>
                                    <div class="col-md-6">
                                        <label class="mb-1"><strong>Adicionais do dependente</strong></label>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aditional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                    type="checkbox"
                                                    value="<?php echo e($aditional['id']); ?>"
                                                    id="dep_<?php echo e($index); ?>_add_<?php echo e($aditional['id']); ?>"
                                                    wire:model.defer="dependents.<?php echo e($index); ?>.additionals">
                                                <label class="form-check-label" for="dep_<?php echo e($index); ?>_add_<?php echo e($aditional['id']); ?>">
                                                    <?php echo e($aditional['name']); ?> - R$ <?php echo e(number_format((float)($aditional['value'] ?? 0), 2, ',', '.')); ?>

                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="alert alert-light border">
                        Nenhum dependente adicionado. Esta etapa é opcional.
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 5): ?>
                <h5 class="mb-4">5. Dados de Cobrança</h5>

                <div class="row">
                    <div class="col-md-4">
                        <label>Tipo de cobrança <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="billing.charge_type">
                            <option value="Boleto">Boleto</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['billing.charge_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-4">
                        <label>Dia de pagamento <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="billing.charge_date">
                            <option value="">Selecione</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['billing.charge_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 6): ?>
                <h5 class="mb-4">6. Documento (RG ou CNH)</h5>

                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo do documento</label>
                        <select class="form-control" wire:model="document_file_type">
                            <option value="RG">RG</option>
                            <option value="CNH">CNH</option>
                        </select>
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['document_file_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="col-md-9">
                        <label>Enviar imagem/foto do <?php echo e($document_file_type); ?></label>
                        <input type="file"
                               class="form-control"
                               wire:model="document_file"
                               accept="image/*"
                               capture="environment">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['document_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                        <div wire:loading wire:target="document_file" class="text-muted mt-2">
                            Enviando documento...
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <!--[if BLOCK]><![endif]--><?php if($document_file): ?>
                            <div class="border rounded p-2">
                                <label class="d-block">Pré-visualização</label>
                                <img src="<?php echo e($document_file->temporaryUrl()); ?>" class="img-fluid rounded" style="max-height: 420px;">
                            </div>
                        <?php elseif($existing_document_file): ?>
                            <div class="border rounded p-2">
                                <label class="d-block">Documento já salvo no rascunho</label>
                                <a href="<?php echo e(Storage::url($existing_document_file)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Visualizar documento
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border">
                                Nenhum documento enviado ainda.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 7): ?>
                <h5 class="mb-4">7. Comprovante de Endereço</h5>

                <div class="row">
                    <div class="col-md-12">
                        <label>Enviar imagem/foto do comprovante</label>
                        <input type="file"
                               class="form-control"
                               wire:model="address_proof_file"
                               accept="image/*"
                               capture="environment">
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address_proof_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-danger"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                        <div wire:loading wire:target="address_proof_file" class="text-muted mt-2">
                            Enviando comprovante...
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <!--[if BLOCK]><![endif]--><?php if($address_proof_file): ?>
                            <div class="border rounded p-2">
                                <label class="d-block">Pré-visualização</label>
                                <img src="<?php echo e($address_proof_file->temporaryUrl()); ?>" class="img-fluid rounded" style="max-height: 420px;">
                            </div>
                        <?php elseif($existing_address_proof_file): ?>
                            <div class="border rounded p-2">
                                <label class="d-block">Comprovante já salvo no rascunho</label>
                                <a href="<?php echo e(Storage::url($existing_address_proof_file)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Visualizar comprovante
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border">
                                Nenhum comprovante enviado ainda.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            
            <!--[if BLOCK]><![endif]--><?php if($step === 8): ?>
                <h5 class="mb-4">8. Resumo do Cadastro</h5>

                <div class="alert alert-info">
                    Revise as informações abaixo e clique em <strong>Enviar pedido</strong>.
                    <br>
                    <small>
                        (Neste momento, o componente está pronto com rascunho + etapas. O envio final pode ser ligado ao seu fluxo atual via service.)
                    </small>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card border mb-3">
                            <div class="card-header"><strong>Cliente</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>CPF:</strong> <?php echo e($client['cpf'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Nome:</strong> <?php echo e($client['name'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Gênero:</strong> <?php echo e($client['gender'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>RG:</strong> <?php echo e($client['rg'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Nascimento:</strong> <?php echo e($client['date_birth'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>WhatsApp:</strong> <?php echo e($client['phone'] ?? '-'); ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?php echo e($client['email'] ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Endereço</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>CEP:</strong> <?php echo e($address['zipcode'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Endereço:</strong> <?php echo e($address['address'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Número:</strong> <?php echo e($address['number'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Complemento:</strong> <?php echo e($address['complement'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Bairro:</strong> <?php echo e($address['neighborhood'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Cidade:</strong> <?php echo e($address['city'] ?? '-'); ?></p>
                                <p class="mb-0"><strong>UF:</strong> <?php echo e($address['state'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border mb-3">
                            <div class="card-header"><strong>Pedido</strong></div>
                            <div class="card-body">
                                <?php
                                    $selectedSeller = collect($sellers)->firstWhere('id', (int)($orderData['seller_id'] ?? 0));
                                    $selectedProduct = collect($products)->firstWhere('id', (int)($orderData['product_id'] ?? 0));
                                    $selectedAdditionalsLabels = collect($additionals)
                                        ->whereIn('id', collect($orderData['selectedAdditionals'] ?? [])->map(fn($v) => (int)$v)->toArray())
                                        ->pluck('name')
                                        ->toArray();
                                ?>

                                <p class="mb-1"><strong>Consultor:</strong> <?php echo e($selectedSeller['name'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Produto:</strong> <?php echo e($selectedProduct['name'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Adicionais:</strong>
                                    <!--[if BLOCK]><![endif]--><?php if(!empty($selectedAdditionalsLabels)): ?>
                                        <?php echo e(implode(', ', $selectedAdditionalsLabels)); ?>

                                    <?php else: ?>
                                        Nenhum
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                                <p class="mb-1"><strong>Valor adesão:</strong> <?php echo e($orderData['accession'] ?? '-'); ?></p>
                                <p class="mb-0"><strong>Pagamento adesão:</strong> <?php echo e($orderData['accession_payment'] ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Cobrança</strong></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Tipo:</strong> <?php echo e($billing['charge_type'] ?? '-'); ?></p>
                                <p class="mb-0"><strong>Dia de pagamento:</strong> <?php echo e($billing['charge_date'] ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="card border mb-3">
                            <div class="card-header"><strong>Documentos</strong></div>
                            <div class="card-body">
                                <p class="mb-1">
                                    <strong>Documento (<?php echo e($document_file_type ?? 'RG'); ?>):</strong>
                                    <!--[if BLOCK]><![endif]--><?php if($existing_document_file): ?>
                                        <a href="<?php echo e(Storage::url($existing_document_file)); ?>" target="_blank">Visualizar</a>
                                    <?php else: ?>
                                        Não enviado
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                                <p class="mb-0">
                                    <strong>Comprovante:</strong>
                                    <!--[if BLOCK]><![endif]--><?php if($existing_address_proof_file): ?>
                                        <a href="<?php echo e(Storage::url($existing_address_proof_file)); ?>" target="_blank">Visualizar</a>
                                    <?php else: ?>
                                        Não enviado
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header"><strong>Dependentes</strong></div>
                            <div class="card-body">
                                <!--[if BLOCK]><![endif]--><?php if(!empty($dependents)): ?>
                                    <ul class="mb-0 pl-3">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dependents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>
                                                <?php echo e($dep['name'] ?? 'Sem nome'); ?>

                                                <!--[if BLOCK]><![endif]--><?php if(!empty($dep['relationship'])): ?> - <?php echo e($dep['relationship']); ?> <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">Nenhum dependente informado.</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="card-footer">
            <div class="d-flex flex-wrap justify-content-between">
                <div class="mb-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="saveAndExit">
                        Salvar rascunho e sair
                    </button>
                </div>

                <div class="mb-2">
                    <!--[if BLOCK]><![endif]--><?php if($step > 1): ?>
                        <button type="button" class="btn btn-outline-primary" wire:click="previousStep">
                            Voltar
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!--[if BLOCK]><![endif]--><?php if($step < 8): ?>
                        <button type="button" class="btn btn-primary" wire:click="nextStep">
                            Avançar
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-success" wire:click="submitOrder">
                            Enviar pedido
                        </button>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/order-easy-form.blade.php ENDPATH**/ ?>