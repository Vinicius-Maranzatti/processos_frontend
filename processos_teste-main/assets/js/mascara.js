$(document).ready(function() {
   if ($('#cpf').length) {
      $('#cpf').inputmask("999.999.999-99", { clearIncomplete: true, showMaskOnHover: false });
   }

   if ($('#cep').length) {
      $('#cep').inputmask("99999-999", { clearIncomplete: true, showMaskOnHover: false });
   }

   // Obtém a data de hoje no formato YYYY-MM-DD
   let today = new Date().toISOString().split('T')[0];

   // Calcula a data máxima (1 ano a partir de hoje)
   let nextYear = new Date();
   nextYear.setFullYear(nextYear.getFullYear() + 1);
   let maxDate = nextYear.toISOString().split('T')[0];

   // Define os valores no input
   $('#data').attr({
      "value": today,
      "min": today,
      "max": maxDate
   });
   
    // Verifica se o input de Data de Nascimento existe antes de definir os atributos
    let inputDataNasc = $('#dataNasc');
    if (inputDataNasc.length) {
      let anoAtual = new Date().getFullYear();
      inputDataNasc.attr({
         "min": "1930-01-01",
         "max": `${anoAtual}-12-31`
      });
   }
   
    let inputDataEmissao = $('#dataEmissao');
    if (inputDataEmissao.length) {
      let minDate = "2000-01-01";
      let maxDateEmissao = "2035-12-31";
      inputDataEmissao.attr({
         "min": minDate,
         "max": maxDateEmissao
      });
   }
});