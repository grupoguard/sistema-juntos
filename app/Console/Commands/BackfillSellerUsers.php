<?php

namespace App\Console\Commands;

use App\Models\Seller;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Console\Command;

class BackfillSellerUsers extends Command
{
    protected $signature = 'sellers:backfill-users 
                            {--dry-run : Apenas simula sem salvar}
                            {--send-email : Envia email com acesso para os usuários criados}';

    protected $description = 'Cria usuários para sellers que ainda não possuem vínculo em user_sellers';

    public function handle(UserManagementService $userService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sendEmail = (bool) $this->option('send-email');

        $created = 0;
        $skipped = 0;
        $conflicts = 0;
        $invalid = 0;

        $sellers = Seller::query()->get();

        $this->info("Total de sellers encontrados: {$sellers->count()}");

        foreach ($sellers as $seller) {
            $alreadyLinked = $seller->users()->exists();

            if ($alreadyLinked) {
                $skipped++;
                $this->line("SKIP: Seller #{$seller->id} já possui usuário vinculado.");
                continue;
            }

            if (empty($seller->email) || !filter_var($seller->email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                $this->warn("INVALID: Seller #{$seller->id} sem email válido.");
                continue;
            }

            $existingUser = User::where('email', $seller->email)->first();

            if ($existingUser) {
                if ($dryRun) {
                    $this->warn("CONFLICT: Seller #{$seller->id} possui email já cadastrado em users ({$seller->email}).");
                } else {
                    $existingUser->sellers()->syncWithoutDetaching([$seller->id]);

                    if (!$existingUser->hasRole('SELLER')) {
                        $existingUser->assignRole('SELLER');
                    }

                    $existingUser->update([
                        'status' => (bool) ($seller->status ?? true),
                    ]);

                    $this->warn("LINKED: Seller #{$seller->id} vinculado ao usuário existente {$existingUser->email}.");
                }

                $conflicts++;
                continue;
            }

            if ($dryRun) {
                $this->info("DRY-RUN: Criaria usuário para Seller #{$seller->id} - {$seller->email}");
                $created++;
                continue;
            }

            $result = $userService->createUserForSeller($seller, $sendEmail);

            $this->info("CREATED: Seller #{$seller->id} => User #{$result['user']->id} ({$result['user']->email})");
            $created++;
        }

        $this->newLine();
        $this->table(
            ['Criados', 'Ignorados', 'Conflitos/Existente', 'Inválidos'],
            [[$created, $skipped, $conflicts, $invalid]]
        );

        return self::SUCCESS;
    }
}