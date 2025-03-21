document.addEventListener("DOMContentLoaded", function () {
    const cepInput = document.querySelector("[data-cep]");

    if (!cepInput) return;

    cepInput.addEventListener("blur", function () {
        const cep = this.value.replace(/\D/g, ""); // Remove caracteres não numéricos

        if (cep.length !== 8) return;

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                const address = document.querySelector("[data-field='address']");
                const neighborhood = document.querySelector("[data-field='neighborhood']");
                const city = document.querySelector("[data-field='city']");
                const state = document.querySelector("[data-field='state']");

                if (!data.erro) {
                    // Preenche os campos e os torna readonly
                    address.value = data.logradouro || "";
                    neighborhood.value = data.bairro || "";
                    city.value = data.localidade || "";
                    state.value = data.uf || "";

                    [address, neighborhood, city, state].forEach(input => input.readOnly = true);

                    address.dispatchEvent(new Event('input'));
                    neighborhood.dispatchEvent(new Event('input'));
                    city.dispatchEvent(new Event('input'));
                    state.dispatchEvent(new Event('input'));
                } else {
                    // Caso o CEP não retorne endereço, desbloqueia os campos para edição
                    [address, neighborhood, city, state].forEach(input => {
                        input.value = "";
                        input.readOnly = false;
                    });
                }
            })
            .catch(error => console.error("Erro ao buscar CEP:", error));
    });
});