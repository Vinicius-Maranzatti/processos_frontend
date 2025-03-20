document.addEventListener("DOMContentLoaded", function () {
    const customSelects = document.querySelectorAll(".custom-select");
 
    customSelects.forEach(select => {
        const trigger = select.querySelector(".select-trigger");
        const options = select.querySelectorAll(".select-options li");
        const hiddenInput = select.querySelector("input");
 
        let selectedIndex = -1;
 
        // Permitir foco no select pelo Tab
        trigger.setAttribute("tabindex", "0");
 
        // Toggle para abrir e fechar o select
        trigger.addEventListener("click", function (e) {
            e.stopPropagation();
            toggleSelect(select);
        });
 
        // Abre ou fecha o menu
        function toggleSelect(select) {
            const isActive = select.classList.contains("active");
 
            // Fecha todos os selects
            customSelects.forEach(s => s.classList.remove("active"));
 
            if (!isActive) {
                select.classList.add("active");
            }
        }
 
        // Define o valor selecionado e fecha o menu
        options.forEach((option) => {
            option.setAttribute("tabindex", "-1");
            option.addEventListener("click", function () {
                selectOption(option);
            });
 
            option.addEventListener("keydown", function (e) {
                if (e.key === "Enter") {
                    selectOption(option);
                } else if (e.key === "ArrowDown") {
                    navigateOptions(option, 1);
                    e.preventDefault();
                } else if (e.key === "ArrowUp") {
                    navigateOptions(option, -1);
                    e.preventDefault();
                }
            });
        });
 
        // Função para selecionar a opção
        function selectOption(option) {
            // Atualiza o texto do trigger com o valor da opção selecionada
            trigger.querySelector("span").textContent = option.textContent;
            // Atualiza o valor do campo escondido com o valor da opção
            hiddenInput.value = option.dataset.value;
 
            // Marca a opção selecionada
            options.forEach(opt => opt.classList.remove("selected"));
            option.classList.add("selected");
 
            // Fecha o menu
            select.classList.remove("active");
 
            // Foca no trigger novamente
            trigger.focus();
        }
 
        // Navega entre as opções usando as setas
        function navigateOptions(option, direction) {
            const optionsArr = Array.from(options);
            const index = optionsArr.indexOf(option);
            const nextIndex = index + direction;
 
            if (nextIndex >= 0 && nextIndex < options.length) {
                options[nextIndex].focus();
            }
        }
 
        // Fecha o select ao clicar fora dele (ajustado)
        document.addEventListener("click", function (e) {
            customSelects.forEach(select => {
                if (!select.contains(e.target)) {
                    select.classList.remove("active");
                }
            });
        });
 
        // Fechar o select ao pressionar Esc
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                customSelects.forEach(select => select.classList.remove("active"));
                trigger.focus();
            }
        });
 
        // Fechar o select quando o usuário sai do campo com "Tab" (ajustado)
        trigger.addEventListener("focusout", function (e) {
            // Verifica se o select está ativo e fecha
            if (!select.contains(e.relatedTarget)) {
                select.classList.remove("active");
            }
        });
    });
 
    // Validação antes de enviar o formulário
    document.getElementById('formulario').addEventListener('submit', function (e) {
        let isValid = true;
        customSelects.forEach(select => {
            const hiddenInput = select.querySelector('input[type="hidden"]');
            if (!hiddenInput.value) {
                isValid = false;
                select.classList.add('error'); // Adiciona uma classe de erro
            } else {
                select.classList.remove('error'); // Remove a classe de erro
            }
        });
 
        if (!isValid) {
            e.preventDefault(); // Impede o envio do formulário
            alert('Por favor, selecione um valor para todos os campos.');
        }
    });
 });