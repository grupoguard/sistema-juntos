<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Contrato - Pedido #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        h1,h2,h3 { margin: 0 0 10px 0; }
        h4 { margin: 0 0 8px 0; }
        p { margin: 0 0 8px 0; line-height: 1.45; text-align: justify; }

        .row { width: 100%; display: flex; gap: 10px; }
        .col { flex: 1; }
        .col-6 { width: 50%; }
        .text-end { text-align: right; }
        .align-items-center { align-items: center; }
        .page-break { page-break-after: always; }

        /* Cards cinza */
        .field {
            background: #EFEFEF;
            border: 1px solid #E2E2E2;
            border-radius: 2px;
            padding: 8px 10px;
            margin-bottom: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center; /* centraliza verticalmente dentro do card */
        }
        .field .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #444;
            margin-bottom: 4px;
        }
        .field .value {
            font-size: 12px;
            font-weight: 600;
            color: #111;
        }

        .section-title { margin: 14px 0 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; }
        .muted { color:#666; font-size: 11px; }

        /* Grids */
        .grid-2 { display: flex; gap: 10px; flex-wrap: wrap; }
        .grid-2 .field { flex: 1; min-width: 240px; }

        .grid-3 { display: flex; gap: 10px; flex-wrap: wrap; }
        .grid-3 .field { flex: 1; min-width: 200px; }

        .grid-4 { display: flex; gap: 10px; flex-wrap: wrap; }
        .grid-4 .field { flex: 1; min-width: 160px; }

        /* Dependentes */
        .dep-card {
            background: #F5F5F5;
            border: 1px solid #E2E2E2;
            border-radius: 2px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .dep-title { font-weight: 700; margin-bottom: 8px; }

        /* EDP block */
        .edp-header {
            background: #1B59A3;
            color: #fff;
            padding: 10px;
            border-radius: 2px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 10px;
        }
        .digit-boxes { display: flex; gap: 6px; margin: 6px 0 10px; }
        .digit {
            width: 22px; height: 26px;
            border: 1px solid #111;
            background: #fff;
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .sign { margin-top: 25px; }
        .line { border-top: 1px solid #000; margin-top: 25px; }
        .center { text-align: center; }

        /* Rodapé de assinatura */
        .sign-row { display:flex; gap:16px; margin-top: 18px; }
        .sign-col { flex:1; }
        .sign-col .line { margin-top: 28px; }

        .mt-5 { margin-top: 55px; }
    </style>
</head>
<body>

    {{-- Cabeçalho --}}
    <div class="row align-items-center" style="margin-bottom: 10px;">
        <div class="col">
            <h3>CONTRATO DE ADESÃO</h3>
            <div class="muted">Pedido #{{ $order->id }}</div>
        </div>
        <div class="col text-end">
            {{-- Dompdf costuma renderizar melhor com public_path quando for imagem local --}}
            <img src="{{ asset('assets/img/logo-azul.png') }}" style="max-width: 120px;">
        </div>
    </div>

    {{-- Linha 1: Responsável/Empresa + Endereço --}}
    <div class="row">
        <div class="col">
            <div class="field">
                <div class="label">Nome do responsável/Empresa</div>
                <div class="value">{{ $order->client->name ?? '-' }}</div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="label">CPF/CNPJ</div>
                    <div class="value">{{ $order->client->cpf ?? '-' }}</div>
                </div>
                <div class="field">
                    <div class="label">RG/Inscrição Estadual</div>
                    <div class="value">{{ $order->client->rg ?? '-' }}</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="label">Data de nascimento</div>
                    <div class="value">
                        @if(!empty($order->client->date_birth))
                            {{ \Carbon\Carbon::parse($order->client->date_birth)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="field">
                    <div class="label">Estado civil</div>
                    <div class="value">{{ $order->client->marital_status ?? '-' }}</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="label">Celular/WhatsApp</div>
                    <div class="value">{{ $order->client->phone ?? '-' }}</div>
                </div>
                <div class="field">
                    <div class="label">Email</div>
                    <div class="value">{{ $order->client->email ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="field">
                <div class="label">Nome da mãe</div>
                <div class="value">{{ $order->client->mom_name ?? '-' }}</div>
            </div>
            <div class="field">
                <div class="label">Endereço</div>
                <div class="value">
                    {{ $order->client->address ?? '-' }}, {{ $order->client->number ?? '-' }}
                    @if(!empty($order->client->complement))
                        — {{ $order->client->complement }}
                    @endif
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="label">Bairro</div>
                    <div class="value">{{ $order->client->neighborhood ?? '-' }}</div>
                </div>
                <div class="field">
                    <div class="label">CEP</div>
                    <div class="value">{{ $order->client->zipcode ?? '-' }}</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="label">Cidade</div>
                    <div class="value">{{ $order->client->city ?? '-' }}</div>
                </div>
                <div class="field">
                    <div class="label">UF</div>
                    <div class="value">{{ $order->client->state ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dados do plano --}}
    <div class="section-title">Dados do plano</div>

    @php
        // adicionais do titular (ajuste se seu relacionamento for diferente)
        $mainAdd = $order->orderAditionals ?? collect(); // se existir relationship
        if (is_array($mainAdd)) $mainAdd = collect($mainAdd);

        $hasAdd = $mainAdd->count() > 0;

        $accessionPayment = $order->accession_payment ?? null;
        $showAccessionPayment = in_array(mb_strtolower((string)$accessionPayment), ['não cobrada','nao cobrada','nao_cobrada','não_cobrada'], true);

        // valor do plano: pode ser orderPrice->product_value (com desconto) ou product->value
        $planValue = $order->orderPrice->product_value ?? ($order->product->value ?? 0);
    @endphp

    {{-- layout adaptativo: 2 / 3 / 4 cards --}}
    <div class="{{ ($hasAdd && $showAccessionPayment) ? 'grid-4' : (($hasAdd || $showAccessionPayment) ? 'grid-3' : 'grid-2') }}">
        <div class="field">
            <div class="label">Consultor</div>
            <div class="value">{{ $order->seller->name ?? '-' }}</div>
        </div>

        <div class="field">
            <div class="label">Plano contratado + valor</div>
            <div class="value">
                {{ $order->product->name ?? '-' }}
                — R$ {{ number_format((float)$planValue, 2, ',', '.') }}
            </div>
        </div>

        @if($hasAdd)
            <div class="field">
                <div class="label">Adicionais</div>
                <div class="value" style="font-weight:600;">
                    @foreach($mainAdd as $i => $a)
                        {{ $a->aditional->name ?? $a->name ?? 'Adicional' }}
                        (R$ {{ number_format((float)($a->value ?? 0), 2, ',', '.') }})@if(!$loop->last), @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($showAccessionPayment)
            <div class="field">
                <div class="label">Pagamento da adesão</div>
                <div class="value">{{ $order->accession_payment ?? '-' }}</div>
            </div>
        @endif
    </div>

    {{-- Forma de pagamento mensal --}}
    <div class="section-title">Forma de pagamento mensal</div>
    <div class="grid-2">
        <div class="field">
            <div class="label">Opção escolhida</div>
            <div class="value">{{ $order->charge_type ?? '-' }}</div>
        </div>
        <div class="field">
            <div class="label">Dia de pagamento</div>
            <div class="value">{{ $order->charge_date ?? '-' }}</div>
        </div>
    </div>

    {{-- Dependentes --}}
    <div class="section-title">Dependentes</div>

    @if(empty($dependents))
        <div class="field">
            <div class="label">Nenhum dependente</div>
            <div class="value">—</div>
        </div>
    @else
        @foreach($dependents as $idx => $dep)
            <div class="dep-card">
                <div class="grid-2">
                    <div class="field">
                        <div class="label">Nome</div><div class="value">{{ $dep['name'] ?? '-' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Grau de parentesco</div><div class="value">{{ $dep['relationship'] ?? '-' }}</div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <div class="label">CPF</div><div class="value">{{ $dep['cpf'] ?? '-' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">RG</div><div class="value">{{ $dep['rg'] ?? '-' }}</div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <div class="label">Data de nascimento</div>
                        <div class="value">
                            @if(!empty($dep['date_birth']))
                                {{ \Carbon\Carbon::parse($dep['date_birth'])->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="field">
                        <div class="label">Nome da mãe</div>
                        <div class="value">{{ $dep['mom_name'] ?? '-' }}</div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <div class="label">Estado civil</div>
                        <div class="value">{{ $dep['marital_status'] ?? '-' }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Plano</div>
                        <div class="value">{{ $order->product->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="field">
                    <div class="label">Adicionais vinculados + valor</div>
                    <div class="value">
                        @php $adds = $dep['additionals'] ?? []; @endphp

                        @if(empty($adds))
                            —
                        @else
                            @foreach($adds as $a)
                                {{ $a['name'] ?? 'Adicional' }} (R$ {{ number_format((float)($a['value'] ?? 0), 2, ',', '.') }})@if(!$loop->last), @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Bloco EDP --}}
    @if(($order->charge_type ?? '') === 'EDP')
        <div class="edp-header">Autorização de Débito de Energia</div>

        @php
            $inst = preg_replace('/\D/', '', (string)($order->installation_number ?? ''));
            $inst = substr($inst, 0, 9);
            $digits = str_split(str_pad($inst, 9, ' ', STR_PAD_RIGHT));
        @endphp

        <div class="field">
            <div class="label">Número de instalação (até 9 dígitos)</div>
            <div class="digit-boxes">
                @foreach($digits as $ch)
                    <div class="digit">{{ trim($ch) === '' ? '' : $ch }}</div>
                @endforeach
            </div>
        </div>

        @if(!empty($order->approval_by) && mb_strtolower($order->approval_by) !== 'titular')
            <div class="grid-2">
                <div class="field">
                    <div class="label">Autorizado por</div>
                    <div class="value">{{ $order->approval_by ?? '-' }}</div>
                </div>
                <div class="field">
                    <div class="label">Nome de quem autorizou + parentesco</div>
                    <div class="value">
                        {{ $order->approval_name ?? '-' }}
                        @if(!empty($order->approval_relationship))
                            — {{ $order->approval_relationship }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @php
            // 1) Valor base do plano (com desconto, se você grava em order_prices.product_value)
            $base = (float)($order->orderPrice->product_value ?? ($order->product->value ?? 0));

            // 2) Adicionais do TITULAR (order_aditionals)
            // Se você já tem relation $order->orderAditionals, ótimo. Senão, faz query.
            $titularAdds = (float) \App\Models\OrderAditional::query()
                ->where('order_id', $order->id)
                ->sum('value');

            // 3) Adicionais dos DEPENDENTES (order_aditionals_dependents)
            $dependAdds = (float) \App\Models\OrderAditionalDependent::query()
                ->where('order_id', $order->id)
                ->sum('value');

            // Total mensal final
            $mensal = $base + $titularAdds + $dependAdds;

            // Por extenso
            $brl = \App\Helpers\MoneyExtenso::brl($mensal);
        @endphp

        <p>
            Autorizo o débito mensal de <strong>R$ {{ $brl['number'] }}</strong>
    (<strong>{{ mb_strtoupper($brl['extenso']) }}</strong>), a ser incluído nas faturas mensais de energia emitidas para a unidade consumidora da EDP São Paulo Distribuição de Energia S.A., em favor da Juntos Clube de Benefícios, CNPJ 53.802.618/0001-31, pelo período de 12 meses, conforme contrato estabelecido entre as partes. Esta autorização será renovada automaticamente por prazo indeterminado, salvo manifestação contrária por escrito, com 30 (trinta) dias de antecedência por parte do CONTRATANTE à CONTRATADA. Estou ciente de que o valor acima será somado ao consumo de energia elétrica e poderá sofrer alterações conforme previsto em contrato. Em caso de mudança de endereço, comprometo-me a comunicar a Juntos Clube de Benefícios pelo telefone (12) 3042-9838, pois o valor da mensalidade está vinculado ao endereço da instalação. Caso não seja o titular da conta de energia, responsabilizo-me por informar ao titular sobre a autorização de débito, isentando a Juntos Clube de Benefícios de qualquer responsabilidade por eventual omissão desta comunicação. Em caso de não inclusão da cobrança, poderão ser tomadas outras medidas de cobrança conforme acordado entre as partes, com base na Resolução Normativa nº 1.000/2021 da ANEEL, de 07/12/2021.
        </p>

        <p>
            Autorizo também a EDP São Paulo Distribuição de Energia S.A. a enviar a nota fiscal/fatura de energia elétrica da unidade consumidora ao e-mail informado neste documento.
        </p>

        <p>
            Informamos que, em conformidade com a Lei Geral de Proteção de Dados (LGPD), nº 13.709/2018, as informações coletadas através desta autorização serão utilizadas exclusivamente para adesão ao produto/serviço da MAP Assessoria de Cobranças Ltda., por meio da conta de energia sob minha titularidade.
        </p>

        <div class="center" style="margin-top: 12px;">
            <strong>São José dos Campos</strong><br>
            <span class="muted">
                {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}
            </span>
        </div>

        <div class="sign-row">
            <div class="sign-col mt-5">
                <div class="line"></div>
                <strong>ASSINATURA DO ASSOCIADO</strong><br>
                {{ $order->client->name ?? '-' }}
            </div>
            <div class="sign-col">
                <img src="{{ asset('assets/img/assinatura.png') }}" style="max-width: 220px; margin-top: 8px;">
                <div class="line"></div>
                <strong>JUNTOS BENEFÍCIOS</strong><br>
            </div>
        </div>
    @else
        {{-- Se não for EDP, mantém apenas assinatura padrão --}}
        <div class="sign-row">
            <div class="sign-col">
                <div class="line"></div>
                <strong>ASSOCIADO</strong><br>
                {{ $order->client->name ?? '-' }}
            </div>
            <div class="sign-col">
                <img src="{{ asset('assets/img/assinatura.png') }}" style="max-width: 220px; margin-top: 8px;">
                <div class="line"></div>
                <strong>JUNTOS BENEFÍCIOS</strong><br>
            </div>
        </div>
    @endif

    <div class="page-break"></div>

    {{-- =======================
         PÁGINA 2
    ======================= --}}
    <h3 class="center" style="margin-bottom: 10px;">Termo associativo e de utilização</h3>

    <p>
        Pelo presente instrumento particular, e na melhor forma de direito, de um lado, o Juntos Clube de Benefícios, localizado na Av. Dep. Benedito Matarazzo, 7151, sala 01 — Jardim Aquarius, São José dos Campos - SP, CEP 12.242-010, inscrito no CNPJ sob o número 53.802.616/0001-31, e, de outro lado, o ASSOCIADO, devidamente qualificado na Ficha Cadastral, que é parte integrante deste instrumento, formalizam o presente contrato.
    </p>

    <p>
        O ASSOCIADO, por este instrumento, formaliza sua adesão às condições aqui dispostas para usufruir dos serviços, benefícios e vantagens do cartão de benefícios. Neste contrato, o ASSOCIADO é considerado titular, e as pessoas por ele indicadas na Ficha Cadastral são denominadas beneficiários, dependentes e/ou agregados, sendo permitido ao titular inscrever dependentes familiares.
    </p>

    <h4>CLÁUSULA PRIMEIRA: DAS DEFINIÇÕES</h4>
    <p>1.1. O CLUBE DE BENEFÍCIOS JUNTOS é um clube de descontos e benefícios, não um plano de saúde.</p>
    <p>1.2. A participação no CLUBE DE BENEFÍCIOS JUNTOS é voluntária e pressupõe a leitura e a concordância integral com os termos.</p>
    <p>1.3. O CLUBE DE BENEFÍCIOS JUNTOS não garante e não se responsabiliza pelos serviços oferecidos nem pelo pagamento das despesas. Tampouco assegura desconto obrigatório em todos os serviços prestados por nossos parceiros ou planos de saúde. Todos os serviços utilizados serão pagos pelo ASSOCIADO diretamente ao prestador. O CLUBE DE BENEFÍCIOS JUNTOS oferece apenas os preços e descontos constantes na relação de empresas e serviços conveniados, divulgada no site https://juntosbeneficios.com.br/.</p>

    <h4>CLÁUSULA SEGUNDA: DO OBJETO DA ASSOCIAÇÃO</h4>
    <p>2.1. Pelo presente contrato, a JUNTOS BENEFÍCIOS oferece ao ASSOCIADO e dependentes uma rede de prestadores de serviços nas áreas de saúde (compreendendo médicos em diversas especialidades, psicólogos, psicoterapeutas, exames) e demais áreas selecionadas e credenciadas, indicados no endereço eletrônico https://juntosbeneficios.com.br, com o objetivo de oferecer descontos nos preços praticados pelos credenciados.</p>
    <p>2.2. Telemedicina: Recurso tecnológico utilizado por meio de aparelho celular, tablet e/ou computador para assistência médica com médicos de família, clínico geral e especialistas, telepsicopatologia e visando a promoção da saúde e a prevenção de riscos e doenças no âmbito da Atenção Primária à Saúde (APS).</p>
    <p>2.3. Seguro de Vida por Morte Acidental: Garante ao beneficiário e/ou herdeiros legais o pagamento do valor contratado em caso de morte exclusivamente decorrente de acidente pessoal coberto, com indenização de R$ 5.000,00 (cinco mil reais) em caso de morte ou invalidez.</p>
    <p>2.4. Auxílio Funeral: Objetiva garantir ao titular do plano assistência ou reembolso das despesas com funeral até o valor de R$ 5.000,00 (cinco mil reais), na ocorrência do falecimento do titular ou de dependentes legais. Mais informações sober a apólice no site https://juntosbeneficios.com.br.</p>
    <p>2.5. Clube de Desconto: Os associados terão acesso ao Clube de Desconto, que oferece vantagens e descontos exclusivos em estabelecimentos e serviços parceiros em todo o território nacional. O uso dos descontos está sujeito às regras e condições de cada parceiro, podendo variar conforme a localidade e o tipo de serviço ou produto. O serviço de concessão dos descontos é prestado diretamente pelo Clube Certo, responsável pela gestão e manutenção das ofertas no site https://juntosbeneficios.com.br/.</p>
    <p>2.6. Odontologia: O plano oferece cobertura nacional, sem coparticipação e sem período de carência, abrangendo consultas e procedimentos de urgência, dentística, periodontia, odontopediatria, endodontia, cirurgia, radiologia, próteses, diagnóstico bucal (patologia), prevenção e orientação de higiene, além de testes e exames laboratoriais. Ressaltamos que não há cobertura para procedimentos de natureza estética ou ortodôntica.</p>
    <p>2.7. A JUNTOS BENEFÍCIOS reserva-se o direito de, a qualquer tempo, excluir da rede credenciada algum prestador de serviço ou especialidade médica, ou incluir novos, mediante simples atualização do portal eletrônico https://juntosbeneficios.com.br.</p>
    <p>2.8. No caso de atendimento fora da rede credenciada, o ASSOCIADO não poderá, em hipótese alguma, requerer o benefício do CLUBE DE BENEFÍCIOS JUNTOS.</p>
    <p>2.9. O ASSOCIADO declara ter recebido, no momento da celebração da presente Associação, instruções de acesso ao site https://juntosbeneficios.com.br, onde estão informações sobre a rede credenciada. Havendo mudanças nos prestadores conveniados, a JUNTOS BENEFÍCIOS informará que a lista atualizada poderá ser acessada no endereço eletrônico https://juntosbeneficios.com.br.</p>

    <h4>CLÁUSULA TERCEIRA: DO ASSOCIADO E DOS DEPENDENTES</h4>
    <p>3.1. O ASSOCIADO do CLUBE DE BENEFÍCIOS JUNTOS, que figura como parte na presente Associação, será responsável pelo pagamento das mensalidades.</p>
    <p>3.2. Toda e qualquer questão entre as partes com base na presente Associação, ainda que relacionada a dependentes, será tratada exclusivamente pelo ASSOCIADO junto a JUNTOS BENEFÍCIOS.</p>
    <p>3.3. Na Ficha Cadastral, assinada pelo ASSOCIADO, este poderá indicar dependentes para recebimento de cartão adicional.</p>
    <p>3.4. O ASSOCIADO poderá incluir novos dependentes mediante o pagamento mensal do adicional das taxas devidas.</p>
    <p>3.5. O cartão de identificação fornecido ao ASSOCIADO e dependentes (virtual ou físico) é de uso pessoal, intransferível e com número de identificação, devendo ser apresentado aos prestadores da rede credenciada, juntamente com documento de identidade do ASSOCIADO, para usufruir dos benefícios do desconto. O ASSOCIADO terá acesso ao clube de descontos, disponível no site www.juntosbeneficios.com.br.</p>
    <p>3.6. Em caso de extravio ou necessidade de emissão da segunda via do cartão, será cobrada uma nova taxa de emissão.</p>
    <p>3.7. É responsabilidade do ASSOCIADO manter a JUNTOS BENEFÍCIOS informada sobre quaisquer alterações cadastrais.</p>

    <h4>CLÁUSULA QUARTA: DO DESCONTO</h4>
    <p>4.1. O desconto ao qual o ASSOCIADO tem direito é variável conforme o prestador de serviço credenciado, podendo ser alterado a qualquer momento em função de negociações com os prestadores da rede credenciada.</p>

    <h4>CLÁUSULA QUINTA: DA CONTRATAÇÃO E DO PAGAMENTO DOS SERVIÇOS</h4>
    <p>5.1. Os ASSOCIADOS escolherão livremente os prestadores da rede credenciada e com eles contratarão honorários e preços dos serviços utilizados, efetuando o pagamento diretamente ao prestador, com o benefício do desconto oferecido pelo CLUBE DE BENEFÍCIOS JUNTOS.</p>
    <p>5.2. A JUNTOS BENEFÍCIOS não assume responsabilidade pelos serviços realizados pelos prestadores nem pelo pagamento dos honorários e preços devidos a eles.</p>
    <p>5.3. O atendimento dos ASSOCIADOS será condicionado à rotina interna e à agenda de atendimento de cada prestador de serviço disponibilizado pela rede credenciada.</p>
    <p>5.4. Os ASSOCIADOS poderão, no prazo de 30 (trinta) dias da data da realização do atendimento, registrar suas críticas e reclamações sobre a qualidade do atendimento junto a JUNTOS BENEFÍCIOS.</p>
    <p>5.5. A JUNTOS BENEFÍCIOS não se responsabiliza por atraso ou falta de pagamento ao prestador de serviços, podendo este deixar de realizar o atendimento quando o pagamento não for feito pelo ASSOCIADO no ato da consulta.</p>
    <p>5.6. O desconto será concedido apenas para serviços efetuados e pagos no momento do atendimento.</p>

    <h4>CLÁUSULA SEXTA: DA ADESÃO E DA MANUTENÇÃO</h4>
    <p>6.1. O ASSOCIADO pagará a JUNTOS BENEFÍCIOS a taxa de adesão e mensalidades conforme estipulado na Ficha de Inscrição.</p>
    <p>6.2. A taxa de adesão será cobrada no ato da inscrição e não é reembolsável.</p>
    <p>6.3. A manutenção da adesão será efetiva enquanto o ASSOCIADO estiver em dia com o pagamento das mensalidades.</p>
    <p>6.4. O não pagamento das mensalidades implicará na suspensão temporária dos benefícios até que a situação seja regularizada.</p>

    <h4>CLÁUSULA SÉTIMA: DAS OBRIGAÇÕES DO ASSOCIADO</h4>
    <p>7.1. O ASSOCIADO compromete-se a utilizar o CLUBE DE BENEFÍCIOS JUNTOS de acordo com as normas estabelecidas e a respeitar os prestadores de serviços.</p>
    <p>7.2. É de responsabilidade do ASSOCIADO informar imediatamente a JUNTOS BENEFÍCIOS sobre qualquer irregularidade ou necessidade de atualização de dados cadastrais.</p>
    <p>7.3. O ASSOCIADO deve manter em dia todos os pagamentos referentes à taxa de adesão e mensalidades, conforme estipulado na Ficha Cadastral.</p>
    <p>7.4. É de responsabilidade do ASSOCIADO verificar a validade do cartão e a inclusão de novos dependentes.</p>

    <h4>CLÁUSULA OITAVA: DA VIGÊNCIA E RESCISÃO</h4>
    <p>8.1. Este contrato terá vigência de 12 meses, contados do início do pagamento da taxa de administração mensal. Sua renovação se dará automaticamente pelo pagamento das mensalidades após o período.</p>
    <p>8.2. O ASSOCIADO poderá rescindir o presente contrato no prazo de 07 (sete) dias contados da data de sua assinatura enviando um e-mail com suas informações pessoais e o motivo do cancelamento para o e-mail atendimento@juntosbeneficios.com.br.</p>
    <p>8.3. A desistência do contrato após 07 (sete) dias da data da adesão ou da renovação não dá direito à devolução de qualquer valor pago a JUNTOS BENEFÍCIOS.</p>
    <p>8.4. A falta de pagamento das mensalidades cessará os benefícios de desconto imediatamente, que serão reativados 48 horas após a regularização dos valores pendentes.</p>
    <p>8.5. O cancelamento do plano antes do cumprimento de 12 meses de vigência do contrato acarretará em multa de 30% do valor restante do contrato em aberto.</p>

    <h4>CLÁUSULA NONA: DOS TELEFONES DE ATENDIMENTO</h4>
    <p>9.1. Para optantes da Assistência Funeral, em caso de sinistro ligar para: 0800 770 4369.</p>
    <p>9.2. Para optantes do plano Odontológico, em caso de acionamento ligar para: (12) 3202-6000.</p>
    <p>9.3. Para atendimento ao associado e dúvidas gerais entrar em contato de segunda a sexta em horário comercial via telefone ou whatsapp pelo número: (12) 3042-9838.</p>

    <h4>CLÁUSULA DÉCIMA: DAS DISPOSIÇÕES GERAIS</h4>
    <p>10.1. Este documento, juntamente com o Ficha Cadastral, constitui o entendimento integral entre as partes e substitui todos os acordos anteriores, verbais ou escritos, relacionados ao CLUBE DE BENEFÍCIOS JUNTOS.</p>
    <p>10.2. Qualquer alteração deste documento somente será válida se realizada por escrito e assinada por ambas as partes.</p>
    <p>10.3. As partes elegem o foro da Comarca de São José dos Campos, São Paulo, para dirimir quaisquer dúvidas ou questões oriundas desta Associação. Por estarem assim justos e contratados, firmam o presente documento em duas vias de igual teor e forma.</p>

    <div class="center" style="margin-top: 16px;">
        <strong>São José dos Campos</strong><br>
        <span class="muted">
            {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y') }}
        </span>
    </div>

    <div class="sign-row">
        <div class="sign-col mt-5">
            <div class="line"></div>
            <strong>ASSINATURA DO ASSOCIADO</strong><br>
            {{ $order->client->name ?? '-' }}
        </div>
        <div class="sign-col">
            <img src="{{ asset('assets/img/assinatura.png') }}" style="max-width: 220px; margin-top: 8px;">
            <div class="line"></div>
            <strong>JUNTOS BENEFÍCIOS</strong><br>
        </div>
    </div>

</body>
</html>