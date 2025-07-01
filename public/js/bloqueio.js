$(document).ready(function () {
    $('#precoCompra, #precoVenda, #valor').on('input', function () {
        let valor = $(this).val();

        // Usa vírgula OU ponto como separador
        const regex = /^(\d+)([.,](\d{0,2})?)?$/;

        if (!regex.test(valor)) {
            // Se digitação for inválida, remove o último caractere
            $(this).val(valor.slice(0, -1));
        }
    });
});
