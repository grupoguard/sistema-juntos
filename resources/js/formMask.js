$(document).ready(function(){
    $('#cpf').mask('000.000.000-00', {reverse: true});
    $('#rg').mask('00.000.000-0');

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
});