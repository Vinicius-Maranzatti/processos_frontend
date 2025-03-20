<!-- Escritorio 3.5 -->
<?php

   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   session_start(); // Iniciar a sessão para acessar variáveis de sessão
   require __DIR__ . '/../../back_end/connect.php'; // Conexão com o banco
   require __DIR__ . '/../../back_end/config.php'; // Configurações

   // Configuração do reCAPTCHA
   $recaptcha_secret = RECAPTCHA_SECRET;

   if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'], $_POST['g-recaptcha-response'])) {
      $password = trim($_POST['password']);
      $recaptcha_response = $_POST['g-recaptcha-response'];

      //  Verifica o reCAPTCHA v2 com a API do Google
      $url = 'https://www.google.com/recaptcha/api/siteverify';
      $data = [
         'secret' => $recaptcha_secret,
         'response' => $recaptcha_response
      ];

      $options = [
         'http' => [
               'header' => "Content-type: application/x-www-form-urlencoded\r\n",
               'method' => 'POST',
               'content' => http_build_query($data)
         ]
      ];

      $context = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      $responseKeys = json_decode($result, true);

      if (!$responseKeys["success"]) {
         $error_message = "Por favor, confirme que você não é um robô.";
      } else {
         // Se o reCAPTCHA for válido, verifica a senha
         $stmt = $conn->prepare("SELECT password_hash FROM access_password WHERE id = 1 LIMIT 1");
         $stmt->execute();
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         if ($row && password_verify($password, $row['password_hash'])) {
               $_SESSION['access_granted'] = true;
               header("Location: index.php");
               exit;
         } else {
               $error_message = "Senha incorreta. Tente novamente.";
         }
      }
   }

?>

<!DOCTYPE html>
<html>
    <head>
      <title>Processos</title>
      <link rel="icon" href="assets/midia/logo.png"> <!-- Icon Page -->
      <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
      <link href="https://cdn.jsdelivr.net/npm/daisyui@5.0.0/themes.css" rel="stylesheet" type="text/css" />

      <script src="https://kit.fontawesome.com/c6fbeab466.js" crossorigin="anonymous"></script>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

      <!-- Index css -->
      <link rel="stylesheet" href="assets/css/bootstrap-grid.min.css">
      <link rel="stylesheet" href="assets/css/style.css">

      <!-- reCAPTCHA script -->
      <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    </head>

    <body>

      <!-- Verificar se o usuário tem acesso -->
      <?php if (!isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true): ?>
      <div id="block-page">
         <div id="content-block">
               <form class="ui form" action="index.php" method="POST">
                  <div class="d-flex aligh-items-center justify-content-center mb-4">
                     <img src="assets/midia/password.gif" alt="Gif senha" width="200">
                  </div>
                  <h2>Liberar Acesso</h2>

                  <?php if (isset($error_message)): ?>
                  <div class="password_error">
                     <i class="fa-solid fa-circle-xmark mr-3"></i>
                     <?php echo $error_message; ?>
                  </div>
                  <?php endif; ?>

                  <div class="input-content mb-3">
                     <!-- <label style="font-size: 1.1em;">Senha de Acesso <span class="text-required">*</span></label> -->
                     <div class="password-wrapper">
                        <input id="password" class="input-password" type="password" name="password" required>
                        <span class="toggle-password" onclick="togglePassword()">
                              <i class="fa-solid fa-eye" id="eye-icon"></i>
                        </span>
                     </div>
                  </div>
                  
                  <!-- reCAPTCHA v2 Checkbox -->
                  <div class="g-recaptcha" data-sitekey="6LcmRPMqAAAAAAvJFHTwbCyYTbBFbTWyhJSQ2nWz" style="width: 100%;"></div>

                  <div>
                     <button id="btn-password" type="submit" class="btn btn-neutral mt-3">
                        <span>Desbloquear</span>
                        <span><i class="fa-solid fa-unlock"></i></span>
                     </button>
                  </div>
               </form>
         </div>
      </div>

      <?php else: ?>
         
      <!-- Conteúdo protegido -->
      <div id="site-content">
         <div class="container">
            <form id="formulario" method="POST">
               <header class="d-flex align-items-center mb-4">
                  <div id="saudacoes">
                     <h1>Seja bem-vindo! Novamente</h1>
                  </div>

                  <!-- Menu -->
                  <div class="ml-auto">
                     <div class="dropdown dropdown-hover dropdown-end">
                        <div tabindex="0" role="button" class="btn m-1"> 
                           <i class="fa-solid fa-bars"></i>
                        </div>


                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                           <!-- Desconectar -->
                           <li>
                              <a id="btn-logout" class="link-custom"  onclick="window.location.href='logout.php'">
                                 <i class="fa-solid fa-right-from-bracket"></i>
                                 <span class="ms-2">Desconectar</span>
                              </a>
                           </li>

                           <!-- Ir para pagian Escritorio -->
                           <li>
                              <a href="https://www.appsheet.com/start/b366e52e-3a7c-49a9-ade9-f3c4160cee11?platform=desktop#vss=H4sIAAAAAAAAA6WOsQ7CMBBD_8VzviArYkAIFhALYQjNVYpIk6pJgSrKv3OBIuaKzWfr-Zxxt_Q4JN3cIM_5d21pgkRWOE49KUiFVfBpCE5BKOx19zEbZ8kn1gXlIr54ogiZF9Hyr98C1rC2raWhVlWQK2aM4wqxMSMoAt2Y9NXRey0jpbDXhmaMZE48ZPmAuPHrZ6-92QXDja12kcoLc4tmfWABAAA=&view=cliente&appName=Processos-80278729" 
                              class="link-custom" id="btn-logout" target="_blank">
                                 <i class="fa-solid fa-scale-balanced"></i>
                                 <span class="ms-2">Escritório</span>
                              </a>
                           </li>
                        </ul>
                     </div>
                  </div>

               </header>
               <div class="row">
                  <div id="titleOne" class="col-12 d-flex align-items-center mb-4">
                     <h1 class="mr-3"><i class="fa-solid fa-bullseye"></i></h1>
                     <h2>Informações do Cliente</h2>
                  </div>

                  <!-- Column 1 -->
                  <div class="col-12 mb-4">
                     <div class="row">
                        <!-- Nome Completo -->
                        <div class="col-5">
                           <div class="input-content">
                              <label for="nome_completo">Nome Completo <span class="text-required">*</span></label>
                              <input type="text" id="nome_completo" class="input-custom" name="nome_completo" placeholder="Nome Completo" required>
                           </div>
                        </div>

                        <!-- Nacionalidade -->
                        <div class="col-4">
                           <div class="input-content">
                              <label for="nacionalidade">Nacionalidade <span class="text-required">*</span></label>
                              <input type="text" id="nacionalidade" class="input-custom" name="nacionalidade" placeholder="Nacionalidade" required>
                           </div>
                        </div>

                        <!-- Estado Civil -->
                        <div class="col-3">
                           <div class="select-content">
                              <label>Estado Civil <span class="text-required">*</span></label>
                              <div class="custom-select">
                                 <div class="select-trigger">
                                    <span>Selecione</span>
                                    <i class="arrow"></i>
                                 </div>
                                 <ul class="select-options">
                                    <li data-value="">Selecione</li>
                                    <li data-value="Solteiro">Solteiro</li>
                                    <li data-value="Casado">Casado</li>
                                    <li data-value="Divorciado">Divorciado</li>
                                    <li data-value="Viúvo">Viúvo</li>
                                    <li data-value="União Estável">União Estável</li>
                                    <li data-value="Separado de Fato">Separado de Fato</li>
                                 </ul>
                                 <input type="hidden" name="estado_civil" required>
                              </div>
                           </div>
                        </div>

                     </div>
                  </div>

                  <!-- Column 2 -->
                  <div class="col-12 mb-4">
                     <div class="row">
                        
                        <!-- Identidade -->
                        <div class="col-4">
                           <div class="input-content">
                              <label for="rg">Identidade <span class="text-required">*</span></label>
                              <input type="text" class="input-custom" name="rg" id="rg" placeholder="00.000.000" required>
                           </div>
                        </div>

                        <!-- Órgão Expedidor -->
                        <div class="col-2 mb-4">
                           <div class="input-content">
                              <label for="orgao_expedidor">Órgão Ex. <span class="text-required">*</span></label>
                              <input name="orgao_expedidor" id="orgao_expedidor" class="input-custom" type="text" placeholder="SSP/SP" minlength="2" maxlength="10" required>
                           </div>
                        </div>

                        <!-- Data de Emissão -->
                        <div class="col-3 mb-4">
                           <div class="input-content">
                              <label for="dataEmissao">Data Emissão <span class="text-required">*</span></label>
                              <input name="dataEmissao" id="dataEmissao" class="input-custom" type="date" required>
                           </div>
                        </div>
                        
                        <!-- Data de Nascimento -->
                        <div class="col-3 mb-4">
                           <div class="input-content">
                              <label for="dataNasc">Data de Nacimento <span class="text-required">*</span></label>
                              <input name="dataNasc" id="dataNasc" class="input-custom" type="date" required>
                           </div>
                        </div>
                        
                        <!-- Local de Nascimento -->
                        <div class="col-4">
                           <div class="input-content">
                              <label for="localNasc">Local de Nascimento <span class="text-required">*</span></label>
                              <input type="text" id="localNasc" class="input-custom" name="localNasc" placeholder="Local Nascimento" minlength="2" maxlength="100" required>
                           </div>
                        </div>

                        <!-- CPF -->
                        <div class="col-4">
                           <div class="input-content">
                              <label for="cpf">CPF <span class="text-required">*</span></label>
                              <input type="text" class="input-custom" name="cpf" id="cpf" placeholder="000.000.000-00" minlength="14" maxlength="14" required>
                           </div>
                        </div>

                        <!-- Profissão -->
                        <div class="col-4">
                           <div class="input-content">
                              <label for="profissao">Profissão <span class="text-required">*</span></label>
                              <input type="text" id="profissao" class="input-custom" name="profissao" placeholder="Profissão" minlength="5" maxlength="80" required>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Column 3 -->
                  <div class="col-12 mb-4">
                     <div class="row">
                        <!-- Endereço -->
                        <div class="col-5">
                           <div class="input-content tooltip" data-tip="Endereço do cliente">
                              <label for="endereco">Endereço <span class="text-required">*</span></label>
                              <input type="text" id="endereco" class="input-custom" name="endereco" placeholder="Endereço" minlength="10" maxlength="150" required>
                           </div>
                        </div>

                        <!-- Bairro -->
                        <div class="col-4">
                           <div class="input-content tooltip" data-tip="Bairro do cliente">
                              <label for="bairro">Bairro <span class="text-required">*</span></label>
                              <input type="text" name="bairro" id="bairro" class="input-custom" placeholder="Bairro" minlength="5" maxlength="50" required>
                           </div>
                        </div>

                        <!-- Complemento -->
                        <div class="col-3">
                           <div class="input-content tooltip" data-tip="Complemento do cliente">
                              <label for="complemento">Complemento</label>
                              <input type="text" id="complemento" class="input-custom" name="complemento" placeholder="Complemento">
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Column 4 -->
                  <div class="col-12 mb-4">
                     <div class="row">
                        <!-- Cidade -->
                        <div class="col-4">
                           <div class="input-content tooltip" data-tip="Cidade do cliente">
                              <label for="cidade_cliente">Cidade <span class="text-required">*</span></label>
                              <input type="text" id="cidade_cliente" class="input-custom" name="cidade_cliente" placeholder="Cidade" minlength="2" maxlength="80" required>
                           </div>
                        </div>

                        <!-- Estado Cliente -->
                        <div class="col-2">
                           <div class="input-content tooltip" data-tip="Estado/UF do cliente">
                              <label for="uf_cliente">UF <span class="text-required">*</span></label>
                              <input type="text" id="uf_cliente" class="input-custom" name="estado_cliente" placeholder="UF" minlength="2" maxlength="2" required>
                           </div>
                        </div>

                        <!-- CEP -->
                        <div class="col-3">
                           <div class="input-content tooltip" data-tip="CEP do cliente">
                              <label for="cep">CEP <span class="text-required">*</span></label>
                              <input type="text" id="cep" class="input-custom" name="cep" id="cep" placeholder="00000-000" required>
                           </div>
                        </div>
                        
                        <div class="col-3">
                           <label class="mt-2">Gênero  <span class="text-required">*</span></label>
                           <div class="mt-3">
                              <!-- Masculino -->
                              <label for="sexo_m">Masculino</label>
                              <input type="radio" id="sexo_m" name="genero" value="masculino" class="radio me-2" checked />

                               <!-- Feminino -->
                              <label for="sexo_f">Feminino</label>
                              <input type="radio" id="sexo_F" name="genero" value="feminino" class="radio"/>
                           </div>
                        </div>

                     </div>
                  </div>

                  <div class="divider"></div>

                  <div class="col-12">
                     <div class="row">

                        <!-- Data Emissão -->
                        <div class="col-4">
                           <div class="input-content tooltip" data-tip="Data da emissão dos documentos">
                              <label for="data">Data Emissão<span class="text-required">*</span></label>
                              <input name="data" id="data" class="input-custom" type="date" required>
                           </div>
                        </div>

                        <!-- Cidade da Emissão -->
                        <div class="col-4">
                           <div class="input-content tooltip" data-tip="Cidade da emissão">
                              <label for="cidade">Cidade Emissão<span class="text-required">*</span></label>
                              <input type="text" id="cidade" class="input-custom" name="cidade" placeholder="Cidade" value="Guapiaçu" minlength="2" maxlength="80" required>
                           </div>
                        </div>

                        <!-- Estado da emissão -->
                        <div class="col-2">
                           <div class="input-content tooltip" data-tip="Estado/UF da emissão">
                              <label for="uf">UF <span class="text-required">*</span></label>
                              <input type="text" id="uf" class="input-custom" name="estado" placeholder="UF" value="SP" minlength="2" maxlength="2" required>
                           </div>
                        </div>

                     </div>
                  </div>

                  <div id="titleTwo" class="col-12 d-flex align-items-center mt-5">
                     <h1 class="mr-3"><i class="fa-solid fa-gear"></i></h1>
                     <h2>Escolher tipo de benefício</h2>
                  </div>

                  <!-- Column 5 -->
                  <div class="col-12 d-flex flex-column mb-4 mt-3">
                     <div class="select-content">
                        <label>Tipo de Benefícios <span class="text-required">*</span></label>
                        <div class="custom-select" style="width: 25rem;">
                           <div class="select-trigger">
                              <span>Selecione</span>
                              <i class="arrow"></i>
                           </div>
                           <ul class="select-options">
                              <li data-value="">Selecione</li>
                              <li data-value="0">B-21</li>
                              <li data-value="1">B-31 ou B-32</li>
                              <li data-value="2">B-41 Deficiênte</li>
                              <li data-value="3">B-41 Rural ou Híbrido</li>
                              <li data-value="4">B-41 Urbana</li>
                              <li data-value="5">B-42</li>
                              <li data-value="6">B-42 Deficiênte</li>
                              <li data-value="7">Loas</li>
                           </ul>
                           <input type="hidden" name="tipo_beneficio" required>
                        </div>
                     </div>
                  </div>

                  <!-- Buttons -->
                  <div class="d-flex">
                     <div>
                        <button type="submit" class="btn btn-custom btn-soft btn-primary mr-5">Começar <span><i class="fa-solid fa-arrow-up-right-from-square"></i></span></button>
                     </div>

                     <div>
                        <button id="limparCampos" class="btn btn-default">Limpar Campos</button>
                     </div>
                  </div>
                  
               </div>
            </form>
         </div>

      </div>

      <?php endif; ?>


      <!-- Scripts -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

      <!-- Scripts Personalizados -->
      <script src="assets/js/mascara.js"></script>
      <script src="assets/js/select.js"></script>
      <script src="assets/js/block_page.js"></script>
      <script src="assets/js/validacao.js"></script>

      <!-- Script para enviar o formulário via AJAX -->
      <script src="assets/js/form.js"></script>
      
      <?php include 'modal_preview.php' ?>



    </body>
</html>