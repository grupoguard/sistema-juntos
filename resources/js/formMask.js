$(document).ready(function(){
    $('#cnpj').mask('00.000.000/0000-00', {reverse: true});
    $('#cpf').mask('000.000.000-00', {reverse: true});
    $('#rg').mask('00.000.000-0');
    $('#whatsapp').mask('(00) 00000-0000');

    $('#phone').mask('(00) 00000-0000').focusout(function(event) {
        var phone, element;
        element = $(this);
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask('(00) 00000-0000');
        } else {
            element.mask('(00) 0000-0000');
        }
    });

    // Declarar no escopo global
    window.applyMasks = function() {
        $('.cpf-mask').mask('000.000.000-00', {reverse: true});
        $('.rg-mask').mask('00.000.000-0');
    }

    // Já aplica nas mascaras fixas
    window.applyMasks();
});