<dialog id="modal_preview" class="modal">
  <div class="modal-box w-11/12 max-w-5xl">
    <h3 class="text-lg font-bold mb-4">Arquivos Processados</h3>

    <div>

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
            <p><i class="fas fa-check-circle check-icon"></i> Processamento concluído com sucesso!</p>
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