<dialog id="modal_preview" class="modal">
  <div class="modal-box w-11/12 max-w-5xl">
    <h3 class="text-lg font-bold mb-4">Arquivos Processados</h3>

    <div>
        <!-- Interface de processamento -->
        <div id="loading" style="display: none;">
            <div class="loader"></div> <!-- Animação de loading -->
            <p>Processando...</p>
            <p>Arquivos <span id="arquivoAtual">0</span> de <span id="totalArquivos">0</span> concluídos.</p>
            <div id="erros" style="color: red;"></div>
        </div>

        <div id="steps">
          <!-- Animação de passos -->
          <ul class="steps">
            <li class="step">Dados recebidos</li>
            <li class="step">Buscando arquivos</li>
            <li class="step">Aplicando dados</li>
            <li class="step">Processando</li>
          </ul>
        </div>
        

        <!-- Barra de progresso -->
        <div class="progress-bar">
            <div class="progress-bar-inner"></div>
        </div>

        <!-- Feedback de conclusão -->
        <div id="conclusao" style="display: none;">
            <p>Processamento concluído com sucesso!</p>
            <i class="fas fa-check-circle check-icon"></i>
        </div>

        <!-- Lista de arquivos processados -->
        <div id="arquivosProcessados" style="display: none;">
            <!-- Conteúdo dinâmico será inserido aqui -->
        </div>
    </div>

    <div class="modal-action">
      <form method="dialog">
        <button class="btn">Fechar</button>
      </form>
    </div>
  </div>
</dialog>