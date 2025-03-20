$(document).ready(function () {
    // Adiciona a classe 'selected' ao item clicado
    $(document).on('click', '.select-options li', function () {
        var value = $(this).data('value');
        var text = $(this).text();
        var select = $(this).closest('.custom-select');

        // Atualiza o texto do trigger
        select.find('.select-trigger span').text(text);

        // Remove a classe 'selected' de todos os itens
        select.find('.select-options li').removeClass('selected');

        // Adiciona a classe 'selected' ao item clicado
        $(this).addClass('selected');

        // Atualiza o valor do campo hidden
        select.find('input[type="hidden"]').val(value);
    });

    // Validação antes de enviar o formulário
    $('#formulario').on('submit', function (event) {
        event.preventDefault(); // Impede o envio tradicional do formulário

        // Verifica se todos os selects têm um valor selecionado
        let isValid = true;
        $('.custom-select').each(function () {
            const hiddenInput = $(this).find('input[type="hidden"]');
            if (!hiddenInput.val()) {
                isValid = false;
                $(this).addClass('error'); // Adiciona uma classe de erro
                $(this).find('.select-trigger').css('border', '1px solid red'); // Adiciona borda vermelha
            } else {
                $(this).removeClass('error'); // Remove a classe de erro
                $(this).find('.select-trigger').css('border', ''); // Remove borda vermelha
            }
        });

        if (!isValid) {
            alert('Por favor, selecione um valor para todos os campos.');
            return; // Impede o envio do formulário
        }

        // Abre o modal
        const modal_preview = document.getElementById('modal_preview');
        modal_preview.showModal();

        // Exibe os steps e esconde outros elementos
        $('#steps').show();
        $('#loading').hide();
        $('#arquivosProcessados').hide();
        $('#conclusao').hide();

        // Coleta os dados do formulário
        var formData = $(this).serialize();

        // Envia os dados via AJAX
        $.ajax({
            url: '/processar.php', // Verifique se o caminho está correto
            type: 'POST',
            data: formData,
            success: function (response) {
                // Verifica se a resposta é uma string e converte para objeto JSON
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }

                // Verifica se a resposta é válida
                if (response.status === 'success' && Array.isArray(response.arquivos)) {
                    // Simular animação dos steps
                    const steps = document.querySelectorAll('.step');
                    const stepsContainer = document.getElementById('steps');
                    let currentStep = 0;

                    const animateSteps = () => {
                        if (currentStep < steps.length) {
                            steps[currentStep].classList.add('step-primary');
                            currentStep++;
                            setTimeout(animateSteps, 500); // 500ms de delay entre os steps
                        } else {
                            // Animação de scale para esconder os steps
                            stepsContainer.classList.add('fade-out');

                            // Exibe a lista de arquivos processados após a animação de scale
                            setTimeout(() => {
                                $('#arquivosProcessados').show();
                                $('#steps').hide(); // Esconde os steps após a conclusão
                                $('#conclusao').show(); // Exibe a mensagem de conclusão

                                // Adiciona o nome do cliente e o total de arquivos
                                const conteudoHTML = `
                                    <div id="collapse-content" class="collapse collapse-arrow bg-base-100 border border-base-300">
                                        <input type="radio" name="my-accordion-2" />
                                        <div class="collapse-title">
                                            <h2>${response.nome}</h2>
                                            <p id="descricao-collapse">Total de arquivos: ${response.arquivos.length}</p>
                                        </div>
                                        <div class="collapse-content text-sm">
                                            <div id="itens-processados">
                                                <ul class="list bg-base-100 rounded-box shadow-md">
                                                    <li class="p-4 pb-2 text-xs opacity-60 tracking-wide">Lista dos arquivos processados:</li>
                                                    ${response.arquivos.map(arquivo => `
                                                        <li class="list-row item-process">
                                                            <div id="icon-processo">
                                                                <i class="fa-solid ${arquivo.tipo === 'word' ? 'fa-file-word' : 'fa-file-pdf'}"></i>
                                                            </div>
                                                            <div>
                                                                <div>${arquivo.nome}</div>
                                                                <div class="text-xs font-semibold opacity-60">Tamanho: ${(arquivo.conteudo.length / 1024).toFixed(2)} KB</div>
                                                            </div>
                                                            <button class="btn btn-square btn-ghost" onclick="downloadFile('${arquivo.conteudo}', '${arquivo.nome}', '${arquivo.tipo}')">
                                                                <i class="fa-solid fa-file-arrow-down"></i>
                                                            </button>
                                                        </li>
                                                    `).join('')}
                                                </ul>
                                                <button class="btn btn-primary mt-4" id="exportarTudo">Exportar Tudo</button>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                $('#arquivosProcessados').html(conteudoHTML);

                                // Adiciona funcionalidade ao botão "Exportar Tudo"
                                $('#exportarTudo').on('click', function (event) {
                                    event.stopPropagation(); // Impede a propagação do evento para o collapse
                                    createZip(response.arquivos, response.nome);
                                });
                            }, 500); // 500ms de delay para a animação de scale
                        }
                    };

                    animateSteps(); // Inicia a animação
                } else {
                    $('#erros').html('Erro: ' + (response.message || 'Resposta inválida do servidor.'));
                }
            },
            error: function (xhr, status, error) {
                $('#erros').html('Erro ao processar: ' + error);
            }
        });
    });

    // Fechar o modal ao clicar no botão "Fechar"
    document.querySelector('#modal_preview form[method="dialog"] button').addEventListener('click', function () {
        const modal_preview = document.getElementById('modal_preview');
        modal_preview.close();
    });
});

// Função para download individual de arquivos
function downloadFile(base64, nomeArquivo, tipo) {
    const byteCharacters = atob(base64);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: tipo === 'word' ? 'application/msword' : 'application/pdf' });

    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = nomeArquivo;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Função para criar e baixar um ZIP com todos os arquivos
function createZip(arquivos, nomeCliente) {
    const zip = new JSZip();

    arquivos.forEach((arquivo, index) => {
        const byteCharacters = atob(arquivo.conteudo);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        zip.file(arquivo.nome, byteArray);
    });

    zip.generateAsync({ type: 'blob' })
        .then(function (content) {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(content);
            link.download = `${nomeCliente}_documentos.zip`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
}