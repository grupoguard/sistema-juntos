document.addEventListener("DOMContentLoaded", function () {
    const cepInput = document.querySelector("[wire\\:model='client.zipcode']");
    const addressInput = document.querySelector("[wire\\:model='client.address']");
    const neighborhoodInput = document.querySelector("[wire\\:model='client.neighborhood']");
    const cityInput = document.querySelector("[wire\\:model='client.city']");
    const stateInput = document.querySelector("[wire\\:model='client.state']"); // Adicione se tiver o campo de estado

    if (cepInput) {
        cepInput.addEventListener("input", async function () {
            let cep = cepInput.value.replace(/\D/g, ""); // Remove não numéricos

            if (cep.length > 8) {
                alert("CEP inválido!");
                return;
            }
            
            if (cep.length === 8) {
                try {
                    // Faz a requisição para sua API Laravel
                    let response = await fetch(`/api/buscar-cep/${cep}`);
                    let data = await response.json();

                    if (data.error) {
                        alert("CEP não encontrado!");
                        return;
                    }

                    // Preenche os campos com os dados retornados
                    addressInput.value = data.logradouro || "";
                    neighborhoodInput.value = data.bairro || "";
                    cityInput.value = data.localidade || "";
                    stateInput.value = data.uf || "";

                    // Disparar evento 'input' para o Livewire reconhecer as mudanças
                    addressInput.dispatchEvent(new Event('input'));
                    neighborhoodInput.dispatchEvent(new Event('input'));
                    cityInput.dispatchEvent(new Event('input'));
                    stateInput.dispatchEvent(new Event('input'));

                    // Desabilita os campos preenchidos
                    addressInput.readOnly = true;
                    neighborhoodInput.readOnly = true;
                    cityInput.readOnly = true;
                    stateInput.readOnly = true;

                } catch (error) {
                    console.error("Erro ao buscar o CEP:", error);
                }
            }
        });
    }
});
