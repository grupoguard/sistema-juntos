<div>
    <form wire:submit.prevent="saveOrder">
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row align-items-center">
                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados do Cliente</h5>
                        </div>

                        <!-- Seleção de Cliente -->
                        <div class="row mb-3">
                            <div class="col-lg-5">
                                <label for="client_id" class="form-label">Cliente</label>
                                <select id="client_id" class="form-control" wire:model.change="client_id">
                                    <option value="new">Cadastrar cliente</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?> - <?php echo e($client->cpf); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                            </div>
                        </div>

                        <!-- Dados do Cliente -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label>Nome do cliente<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model.defer="client.name">
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
                                <select class="form-control" wire:model.change="client.gender">
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
                                <input type="text" class="form-control" wire:model.defer="client.mom_name">
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
                                <input type="text" class="form-control" id="cpf" wire:model.defer.live="client.cpf">
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
                                <input type="text" class="form-control" id="rg" wire:model.defer.live="client.rg">
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
                                <input type="date" class="form-control" wire:model.defer="client.date_birth">
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
                                <input type="tel" class="form-control" id="phone" wire:model.defer="client.phone">
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
                                <select class="form-control" wire:model.change="client.marital_status">
                                    <option value="">Selecione</option>
                                    <option value="solteiro">Solteiro(a)</option>
                                    <option value="casado">Casado(a)</option>
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
                                <input type="email" class="form-control" wire:model.defer="client.email">
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
                                <input type="number" class="form-control" data-cep id="zipcode" wire:model.defer="client.zipcode">
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
                                <input type="text" class="form-control" data-field="address" wire:model.defer="client.address">
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
                                <input type="number" class="form-control" wire:model.defer="client.number">
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
                                <input type="text" class="form-control" wire:model.defer="client.complement">
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
                                <input type="text" class="form-control" data-field="neighborhood" wire:model.defer="client.neighborhood">
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
                                <input type="text" class="form-control" data-field="city" wire:model.defer="client.city">
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
                                <input type="text" class="form-control" data-field="state" wire:model.defer="client.state">
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

                        <div class="row mb-3">
                            <div class="col-12">
                                <h5 class="mb-0">Dados do Pedido</h5>
                            </div>
                        </div>

                        <!-- Seleção de Consultor -->
                        <div class="row <?php echo e(empty($additionals) ? 'align-items-center' : 'align-items-ender'); ?>">
                            <div class="col-lg-3 mb-3">
                                <label for="seller_id" class="form-label">Consultor<span class="text-danger">*</span></label>
                                <select id="seller_id" class="form-control" wire:model.change="seller_id">
                                    <option value="">Selecione um consultor</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($seller->id); ?>"><?php echo e($seller->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['seller_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
        
                            <!-- Seleção de Produto -->
                            <div class="col-lg-6 mb-3">
                                <label for="product_id" class="form-label">Produto<span class="text-danger">*</span></label>
                                <select id="product_id" class="form-control" wire:model.change="product_id" wire:change="loadAdditionals">
                                    <option value="">Selecione um produto</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <!-- Adicionais -->
                            <div class="col-lg-3 mb-3">
                                <!--[if BLOCK]><![endif]--><?php if(!empty($additionals)): ?>
                                    <label class="form-label">Adicionais</label>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $additional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model.change="selectedAdditionals" value="<?php echo e($additional['id']); ?>">
                                            <label class="form-check-label"><?php echo e($additional['name']); ?> - R$ <?php echo e(number_format($additional['value'], 2, ',', '.')); ?></label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                <?php else: ?>
                                    <p>Nenhum adicional disponível.</p>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label>Valor adesão (R$)<span class="text-danger">*</span></label>
                                <input type="number" id="accession" step="0.1" class="form-control" placeholder="R$ Adesão" wire:model="accession">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['accession'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-lg-5 mb-3">
                                <label for="accession_payment" class="form-label">Pagamento adesão<span class="text-danger">*</span></label>
                                <select id="accession_payment" class="form-control" wire:model.change="accession_payment">
                                    <option value="">Selecione um pagamento</option>
                                    <option value="PIX">PIX</option>
                                    <option value="Boleto">Boleto</option>
                                    <option value="Cartão de crédito">Cartão de crédito</option>
                                    <option value="Cartão de débito">Cartão de débito</option>
                                    <option value="Não cobrada">Não cobrada</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['accession_payment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-lg-4 mt-4">
                                <h3>
                                    Total:
                                    <span class="total" id="total">
                                        R$ <?php echo e(number_format($total, 2, ',', '.')); ?>

                                    </span>
                                </h3>
                            </div>
                        </div>

                        <hr class="my-5">
                        
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mb-0">Dependentes</h5>
                            </div>
                            <div class="col-lg-6 text-end">
                                <button type="button" class="btn bg-blue text-white" wire:click="addDependent">Adicionar Dependente</button>
                                <!--[if BLOCK]><![endif]--><?php if(session()->has('error')): ?>
                                    <div class="alert alert-danger me-3 text-center text-white">
                                        <?php echo e(session('error')); ?>

                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $dependents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dependent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row align-items-end mt-4" wire:key="dependent-<?php echo e($index); ?>">
                                    <div class="col-md-12 mb-3">
                                        <label>Nome do dependente<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.<?php echo e($index); ?>.name">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Grau de Parentesco<span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.relationship">
                                            <option value="">Selecione</option>
                                            <option value="mae-pai">Mãe/Pai</option>
                                            <option value="irmao">Irmão(ã)</option>
                                            <option value="filho">Filho(a)</option>
                                            <option value="conjuge">Cônjuge</option>
                                            <option value="outro">Outro</option>
                                            <option value="nao_informado">Não Informado</option>
                                        </select>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.relationship'];
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
                                        <input type="text" class="form-control cpf-mask" wire:model="dependents.<?php echo e($index); ?>.cpf">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.cpf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>RG</label>
                                        <input type="text" class="form-control rg-mask" wire:model="dependents.<?php echo e($index); ?>.rg">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.rg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Data de nascimento<span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" wire:model="dependents.<?php echo e($index); ?>.date_birth">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.date_birth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label>Estado Civil<span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.change="dependents.<?php echo e($index); ?>.marital_status">
                                            <option value="">Selecione</option>
                                            <option value="solteiro">Solteiro(a)</option>
                                            <option value="casado">Casado(a)</option>
                                            <option value="divorciado">Divorciado(a)</option>
                                            <option value="viuvo">Viúvo(a)</option>
                                            <option value="uniao_estavel">União Estável</option>
                                            <option value="outro">Outro</option>
                                        </select>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.marital_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label>Nome da mãe<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.<?php echo e($index); ?>.mom_name">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.mom_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <!--[if BLOCK]><![endif]--><?php if(!empty($additionals)): ?>
                                            <label class="form-label">Adicionais</label>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $additional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" wire:model.change="dependents.<?php echo e($index); ?>.additionals" value="<?php echo e($additional['id']); ?>">
                                                    <label class="form-check-label"><?php echo e($additional['name']); ?> - R$ <?php echo e(number_format($additional['value'], 2, ',', '.')); ?></label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['dependents.'.$index.'.additionals'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php else: ?>
                                            <p>Nenhum adicional disponível.</p>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-danger" wire:click="removeDependent(<?php echo e($index); ?>)">X</button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <hr class="my-5">

                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Documentos</h5>
                        </div>

                        <div class="row mb-4">
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
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div class="col-md-4">
                                <label><?php echo e($document_file_type === 'CNH' ? 'CNH (imagem/foto)' : 'RG (imagem/foto)'); ?></label>
                                <input type="file" class="form-control" wire:model="document_file" accept="image/*" capture="environment">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['document_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                                <div wire:loading wire:target="document_file" class="text-muted mt-1">
                                    Enviando documento...
                                </div>
                            </div>

                            <div class="col-md-5">
                                <!--[if BLOCK]><![endif]--><?php if($document_file): ?>
                                    <label>Pré-visualização documento</label>
                                    <div class="border rounded p-2">
                                        <img src="<?php echo e($document_file->temporaryUrl()); ?>" alt="Documento" class="img-fluid rounded">
                                    </div>
                                <?php elseif(!empty($existing_document_file)): ?>
                                    <label>Documento atual</label>
                                    <div class="border rounded p-2">
                                        <a href="<?php echo e(Storage::url($existing_document_file)); ?>" target="_blank">Visualizar documento atual</a>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Comprovante de endereço (imagem/foto)</label>
                                <input type="file" class="form-control" wire:model="address_proof_file" accept="image/*" capture="environment">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address_proof_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->

                                <div wire:loading wire:target="address_proof_file" class="text-muted mt-1">
                                    Enviando comprovante...
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!--[if BLOCK]><![endif]--><?php if($address_proof_file): ?>
                                    <label>Pré-visualização comprovante</label>
                                    <div class="border rounded p-2">
                                        <img src="<?php echo e($address_proof_file->temporaryUrl()); ?>" alt="Comprovante" class="img-fluid rounded">
                                    </div>
                                <?php elseif(!empty($existing_address_proof_file)): ?>
                                    <label>Comprovante atual</label>
                                    <div class="border rounded p-2">
                                        <a href="<?php echo e(Storage::url($existing_address_proof_file)); ?>" target="_blank">Visualizar comprovante atual</a>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="col-12 mb-3">
                            <h5 class="mb-0">Dados de cobrança</h5>
                        </div>

                        <!-- Tipo de Cobrança -->
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label for="charge_type" class="form-label">Tipo de Cobrança<span class="text-danger">*</span></label>
                                <select id="charge_type" class="form-control" wire:model.change="charge_type">
                                    <option value="">Selecione</option>
                                    <option value="EDP">EDP</option>
                                    <option value="BOLETO">Boleto</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['charge_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <!-- Botão de salvar -->
                        <div class="row">
                            <div class="col-lg-7">

                            </div>
                            <div class="col-lg-5 text-end">
                                <button type="submit" class="btn btn-success btn-lg">Salvar Pedido</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="clientHasOrderModal" tabindex="-1" aria-labelledby="clientHasOrderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientHasOrderModalLabel">Cliente já possui pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Este cliente já possui um pedido e não pode cadastrar um novo.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </ul>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    
    <?php $__env->startPush('scripts'); ?>
        <script>
            window.addEventListener('clientHasOrder', event => {
                var myModal = new bootstrap.Modal(document.getElementById('clientHasOrderModal'));
                myModal.show();
            });
            
            // Função para aplicar máscaras em elementos específicos
            function applyMasksToNewElements(container) {
                $(container).find('.cpf-mask').each(function() {
                    if (!$(this).data('mask')) {
                        $(this).mask('000.000.000-00', {reverse: true});
                    }
                });
                
                $(container).find('.rg-mask').each(function() {
                    if (!$(this).data('mask')) {
                        $(this).mask('00.000.000-0');
                    }
                });
            }
            
            // Observer para detectar novos elementos
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                applyMasksToNewElements(node);
                            }
                        });
                    }
                });
            });
            
            // Iniciar observação
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            Livewire.hook('message.processed', (message, component) => {
                setTimeout(function() {
                    window.applyMasks();
                }, 50);
            });
        </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH /var/www/html/resources/views/livewire/order-form.blade.php ENDPATH**/ ?>