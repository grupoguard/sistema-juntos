<div>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" wire:ignore>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Dados do pedido</button>
        </li>
        <!--[if BLOCK]><![endif]--><?php if($charge_type == 'EDP'): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Evidências</button>
            </li>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Registro Financeiro</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
            <form wire:submit.prevent="saveOrder">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-10 mb-3">
                                    <h5 class="mb-0">Dados do Cliente</h5>
                                </div>
                                <div class="col-2 text-end">
                                    <!--[if BLOCK]><![endif]--><?php if($charge_type == 'EDP'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <?php echo e($charge_type); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">
                                            <?php echo e($charge_type); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>

                                <!-- Dados do Cliente -->
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
                                        <select id="seller_id" class="form-control" wire:model="seller_id">
                                            <option value="">Selecione um consultor</option>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e((string) $seller['id']); ?>"><?php echo e($seller['name']); ?></option>
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
                                        <select id="product_id" class="form-control" wire:model="product_id" wire:change="loadAdditionals">
                                            <option value="">Selecione um produto</option>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e((string) $product->id); ?>" <?php echo e($product->status !== 1 ? 'disabled' : ''); ?> ><?php echo e($product->name); ?></option>
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
                                        <input type="number" id="order.accession" step="0.1" class="form-control" placeholder="R$ Adesão" wire:ignore="order.accession" value="<?php echo e($this->order->accession); ?>" min="0">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['order.accession'];
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
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['order.accession_payment'];
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
                                        <div class="row align-items-end mt-4">
                                            <div class="col-md-12 mb-3">
                                                <label>Nome do dependente<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" placeholder="Nome do Dependente" wire:model="dependents.<?php echo e($index); ?>.name">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label>Grau de Parentesco<span class="text-danger">*</span></label>
                                                <select class="form-control" wire:model.defer="dependents.<?php echo e($index); ?>.relationship">
                                                    <option value="">Selecione</option>
                                                    <option value="mae-pai">Mãe/Pai</option>
                                                    <option value="irmao">Irmão(ã)</option>
                                                    <option value="conjuge">Cônjuge</option>
                                                    <option value="filho">Filho</option>
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
                                                    <option value="nao_informado">Não informado</option>
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
                                            <div class="col-md-5 mb-3">
                                                <label>Nome da mãe<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" placeholder="Nome da mãe" wire:model="dependents.<?php echo e($index); ?>.mom_name">
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

                                <!-- Botão de salvar -->
                                <div class="row">
                                    <div class="col-lg-5 text-end">
                                        <button type="submit" class="btn btn-success btn-lg">Alterar Pedido</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--[if BLOCK]><![endif]--><?php if($charge_type == 'EDP'): ?>
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row align-items-center">
                                <div class="col-lg-3 mb-3">
                                    <label for="installation_number" class="form-label">Número da Instalação<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="installation_number" min="1" max="9999999999" oninput="this.value = this.value.slice(0, 10)" wire:model="installation_number">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['installation_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="approval_name" class="form-label">Nome do Titular<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="approval_name" wire:model="approval_name">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['approval_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="approval_by" class="form-label">Autorizado por<span class="text-danger">*</span></label>
                                    <select id="approval_by" class="form-control"  wire:model.change="approval_by">
                                        <option value="">Selecione</option>
                                        <option value="Titular">Titular</option>
                                        <option value="Conjuge">Cônjuge</option>
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['approval_by'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-lg-3 mb-3">
                                    <label for="evidence_date" class="form-label">Data da Evidência<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="evidence_date" wire:model="evidence_date">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['evidence_date'];
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
                            
                            <div class="row mt-4 mb-5">
                                <div class="col-lg-6">
                                    <h5 class="mb-0">Enviar nova evidência</h5>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['evidences'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="text-danger"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-lg-6 text-end">
                                    <button type="button" class="btn bg-blue text-white" wire:click="addEvidence">Adicionar Documentos</button>
                                </div>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="row align-items-center mt-4">
                                        <div class="col-md-3 mb-3">
                                            <label>Tipo de evidência<span class="text-danger">*</span></label>
                                            <select class="form-control" wire:model="evidences.<?php echo e($index); ?>.evidence_type">
                                                <option value="selecione">Selecione</option>
                                                <option value="audio">Audio</option>
                                                <option value="contrato">Contrato</option>
                                                <option value="certidao de casamento">Certidão de Casamento</option>
                                                <option value="cpf">CPF</option>
                                                <option value="rg">RG</option>
                                                <option value="cnh">Carteira de Motorista</option>
                                                <option value="outro">Outro</option>
                                            </select>
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['evidences.<?php echo e($index); ?>.evidence_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <label>Arquivo cadastrado</label><br>
                                            <!--[if BLOCK]><![endif]--><?php if($evidence['evidence_type'] === 'audio'): ?>
                                                <audio controls>
                                                    <source src="<?php echo e(asset('storage/' . $evidence['document'])); ?>" type="audio/mpeg">
                                                    Seu navegador não suporta a tag de áudio.
                                                </audio>
                                            <?php else: ?> 
                                                <a href="<?php echo e(asset('storage/' . $evidence['document'])); ?>" target="_blank">
                                                    <i class="fa fa-file-pdf-o fa-2x"></i>
                                                </a>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label>Documento<span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" wire:model="evidences.<?php echo e($index); ?>.document">
                                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['evidences.<?php echo e($index); ?>.document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="button" class="btn btn-danger" wire:click="removeEvidence(<?php echo e($index); ?>)">X</button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row align-items-center">
                            <div class="col-lg-3 mb-3">
                                <label for="order.charge_type" class="form-label">Tipo de Cobrança<span class="text-danger">*</span></label>
                                <select id="charge_type" class="form-control" wire:model.change="charge_type" disabled>
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

                            <!--[if BLOCK]><![endif]--><?php if($order['charge_type'] == 'BOLETO'): ?>
                                <div class="col-lg-3 mb-3">
                                    <label for="charge_date" class="form-label">Data da Cobrança</label>
                                    <input type="number" class="form-control" id="charge_date" wire:model="charge_date">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['charge_date'];
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
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/order-edit.blade.php ENDPATH**/ ?>