document.addEventListener('DOMContentLoaded', function () {
    // Seleciona todos os inputs com a classe .input-custom
    const inputs = document.querySelectorAll('.input-custom');
    // Seleciona todos os selects customizados
    const customSelects = document.querySelectorAll('.custom-select');

    // Validação para inputs
    inputs.forEach(input => {
        // Valida o campo ao carregar a página (só aplica borda vermelha se o campo já foi interagido)
        if (input.value.trim() !== "" && input.hasAttribute('required')) {
            validarCampo(input);
        }

        // Adiciona um event listener para o evento 'input' em cada input
        input.addEventListener('input', function () {
            if (input.hasAttribute('required')) {
                validarCampo(input);
            }
        });

        // Adiciona um event listener para o evento 'blur' (quando o campo perde o foco)
        input.addEventListener('blur', function () {
            if (input.hasAttribute('required')) {
                validarCampo(input);
            }
        });
    });

    // Função para limpar todos os campos e redefinir a formatação
    function limparCampos() {
        // Limpa os inputs
        inputs.forEach(input => {
            input.value = ''; // Limpa o valor do campo
            input.style.outline = ''; // Remove a borda personalizada
            removerCheck(input); // Remove o ícone de check
        });

        // Limpa os selects customizados
        customSelects.forEach(select => {
            const hiddenInput = select.querySelector('input[type="hidden"]');
            const trigger = select.querySelector('.select-trigger');

            hiddenInput.value = ''; // Limpa o valor do campo hidden
            trigger.querySelector('span').textContent = 'Selecione'; // Reseta o texto do trigger
            trigger.style.outline = ''; // Remove a borda personalizada
        });

        console.log('Todos os campos foram limpos e a formatação foi redefinida.');
    }

    // Adiciona um event listener para o botão de limpar campos
    const botaoLimpar = document.getElementById('limparCampos');
    if (botaoLimpar) {
        botaoLimpar.addEventListener('click', function (e) {
            e.preventDefault(); // Evita o comportamento padrão do botão
            limparCampos();
        });
    }

    // Função para validar campos de input
    function validarCampo(campo) {
        if (campo.type === 'date') {
            // Apenas aplica a borda para campos do tipo date
            if (campo.value.trim() === "") {
                campo.style.outline = '2px solid #FF6C6C'; // Borda vermelha
            } else {
                campo.style.outline = '2px solid #00C663'; // Borda verde
            }
        } else {
            // Validação padrão para outros inputs
            if (campo.value.trim() === "") {
                campo.style.outline = '2px solid #FF6C6C'; // Borda vermelha se o campo estiver vazio
                removerCheck(campo);
            } else if (campo.checkValidity()) {
                campo.style.outline = '2px solid #00C663'; // Borda verde se o campo for válido
                adicionarCheck(campo);
            } else {
                campo.style.outline = '2px solid #FF6C6C'; // Borda vermelha se o campo for inválido
                removerCheck(campo);
            }
        }
    }

    // Função para adicionar o ícone de check
    function adicionarCheck(campo) {
        if (!campo.nextElementSibling || !campo.nextElementSibling.classList.contains('check-validacao')) {
            const check = document.createElement('i');
            check.classList.add('check-validacao', 'fa-regular', 'fa-circle-check');
            campo.parentNode.insertBefore(check, campo.nextSibling);
        }
    }

    // Função para remover o ícone de check
    function removerCheck(campo) {
        if (campo.nextElementSibling && campo.nextElementSibling.classList.contains('check-validacao')) {
            campo.nextElementSibling.remove();
        }
    }
});