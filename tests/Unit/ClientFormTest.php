<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Livewire\ClientForm;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class ClientFormTest extends TestCase
{
    use DatabaseTransactions; // Garante um banco limpo a cada teste

    /** @test */
    public function it_creates_a_client_with_dependents()
    {
        $user = User::factory()->create(); // Cria um usuário fictício para o teste

        $this->actingAs($user); // Autentica o usuário para evitar redirecionamento

        Livewire::test(ClientForm::class)
            ->set('client', [
                'group_id' => 1,
                'name' => 'João Silva',
                'mom_name' => 'Maria Silva',
                'date_birth' => '1990-01-01',
                'cpf' => '12345678909',
                'rg' => '123456789',
                'gender' => 'Masculino',
                'marital_status' => 'Solteiro',
                'phone' => '11999999999',
                'email' => 'joao@example.com',
                'zipcode' => '12345678',
                'address' => 'Rua das Flores',
                'number' => '123',
                'complement' => 'Apto 101',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'obs' => 'Nenhuma observação',
            ])
            ->call('addDependent') // Adiciona o primeiro dependente
            ->set('dependents.0', [
                'name' => 'Pedro Silva',
                'mom_name' => 'Maria Silva',
                'date_birth' => '2015-06-10',
                'cpf' => '98765432100',
                'rg' => '987654321',
                'marital_status' => 'Solteiro(a)',
                'relationship' => 'Filho(a)',
            ])
            ->call('addDependent') // Adiciona o segundo dependente
            ->set('dependents.1', [
                'name' => 'Ana Silva',
                'mom_name' => 'Maria Silva',
                'date_birth' => '2018-08-15',
                'cpf' => '11144477735',
                'rg' => '567891234',
                'marital_status' => 'Solteiro(a)',
                'relationship' => 'Filho(a)',
            ])
            ->call('save')
            ->assertRedirect(route('admin.clients.index'));

        // Verifica se o cliente foi salvo
        $this->assertDatabaseHas('clients', ['email' => 'joao@example.com']);

        // Verifica se os dependentes foram salvos corretamente
        $client = \App\Models\Client::where('email', 'joao@example.com')->first();
        $this->assertDatabaseHas('dependents', [
            'client_id' => $client->id,
            'name' => 'Pedro Silva',
        ]);
        $this->assertDatabaseHas('dependents', [
            'client_id' => $client->id,
            'name' => 'Ana Silva',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Livewire::test(ClientForm::class)
            ->set('client', [])
            ->call('save')
            ->assertHasErrors([
                'client.name',
                'client.mom_name',
                'client.date_birth',
                'client.cpf',
                'client.rg',
                'client.gender',
                'client.marital_status',
                'client.email',
                'client.address',
                'client.city',
                'client.state',
            ]);
    }
}
