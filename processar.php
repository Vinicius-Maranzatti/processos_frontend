<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/php_errors.log');
    session_start();
    require __DIR__ . '/../../back_end/connect.php'; // Conexão com o banco


    // Verificar se o usuário tem acesso
    if (!isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true) {
        // Redirecionar para a página de bloqueio se não tiver acesso
        header("Location: index.php"); // Ou a página que bloqueia o acesso
        exit;
    }
    
    require __DIR__ . '/vendor/autoload.php';
    use PhpOffice\PhpWord\TemplateProcessor;
    
    header('Content-Type: application/json');
    
    function jsonError($message) {
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Método de requisição inválido.');
    }
    
    $requiredFields = [
        'nome_completo', 
        'nacionalidade', 
        'estado_civil', 
        'rg', 
        'orgao_expedidor', 
        'cpf', 
        'profissao', 
        'endereco', 
        'bairro', 
        'cidade', 
        'estado', 
        'cep', 
        'data', 
        'tipo_beneficio'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            jsonError('Todos os campos são obrigatórios.');
        }
    }
    
    try {
        error_log("Iniciando processamento...");
    
        // Coletar dados do formulário
        $nome = mb_strtoupper($_POST['nome_completo']);
        $estado_civil = mb_strtolower($_POST['estado_civil']);
        $nacionalidade = mb_strtolower($_POST['nacionalidade']);
        $profissao = mb_strtolower($_POST['profissao']);
        $cpf = $_POST['cpf'];
        $rg = $_POST['rg'];
        $orgao_expedidor = mb_strtoupper($_POST['orgao_expedidor']);
        $endereco = mb_convert_case($_POST['endereco'], MB_CASE_TITLE, "UTF-8");
        $bairro = mb_convert_case($_POST['bairro'], MB_CASE_TITLE, "UTF-8");
        $complemento = !empty($_POST['complemento']) ? mb_strtolower($_POST['complemento']) : '';
        $cidade = mb_convert_case($_POST['cidade'], MB_CASE_TITLE, "UTF-8");
        $cidade_cliente = mb_convert_case($_POST['cidade_cliente'], MB_CASE_TITLE, "UTF-8");
        $uf = mb_strtoupper($_POST['estado']);
        $uf_cliente = mb_strtoupper($_POST['estado_cliente']);
        $cep = $_POST['cep'];
        $data = $_POST['data'];
        $dataEmissao = $_POST['dataEmissao']; 
        $dataNasc = $_POST['dataNasc']; 
        $tipo_beneficio = $_POST['tipo_beneficio'];
        $localNasc = mb_convert_case($_POST['localNasc'], MB_CASE_TITLE, "UTF-8");
        $genero = $_POST['genero'];

        $bairro_complemento = "";        
        if($complemento != "") {
            $bairro_complemento = $bairro . ", " . $complemento;
        } else {
            $bairro_complemento = $bairro; 
        }
        
        $inscrito = "";
        $portador = "";
        $domiciliado = "";
        if(isset($genero) && $genero === "masculino") {
            $inscrito = "inscrito";
            $portador = "portador";
            $domiciliado = "domiciliado";
        } else {
            $inscrito = "inscrita";
            $portador = "portadora";
            $domiciliado = "domiciliada";

            // Verifica o estado civil
            if ($estado_civil == "solteiro") {
                $estado_civil = "solteira";
            } elseif ($estado_civil == "casado") {
                $estado_civil = "casada";
            } elseif ($estado_civil == "divorciado") {
                $estado_civil = "divorciada";
            } elseif ($estado_civil == "viuvo") {
                $estado_civil = "viúva";
            } elseif ($estado_civil == "Separado de Fato") {
                $estado_civil = "separada de fato";
            }
        }
    
        $localidade = $cidade . "/" . $uf;
        $enderecoCompleto = $endereco . ", " . $bairro;
        $dataEmissao_orgaoExpedidor = $dataEmissao . ", " . $orgao_expedidor;
    
        error_log("Dados coletados: " . print_r($_POST, true));
    
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    
        $data_formatada = date("d/m/Y", strtotime($data));
        $data_emissao_formatada = date("d/m/Y", strtotime($dataEmissao));
        $data_nasc_formatada = date("d/m/Y", strtotime($dataNasc));
        
        $diaNasc = date("d",strtotime($dataNasc));
        $mesNasc = date("m",strtotime($dataNasc));
        $anoNasc = date("Y",strtotime($dataNasc));
    
        $dia = date("d", strtotime($data));
        $mes_number = date("m", strtotime($data));

        $formatter = new IntlDateFormatter(
            'pt_BR',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            'MMMM'
        );
    
        $mes = $formatter->format(strtotime($data));
        $ano = date("Y", strtotime($data));
    
        $pastas_beneficios = [
            '0' => 'B-21',
            '1' => 'B-31 OU 32',
            '2' => 'B-41 DEFICIENTE',
            '3' => 'B-41 RURAL OU HIBRIDA',
            '4' => 'B-41 URBANA',
            '5' => 'B-42',
            '6' => 'B-42 DEFICIENTE',
            '7' => 'LOAS'
        ];
    
        if (!isset($pastas_beneficios[$tipo_beneficio])) {
            jsonError('Tipo de benefício inválido.');
        }
    
        $pasta_selecionada = __DIR__ . '/formularios/' . $pastas_beneficios[$tipo_beneficio];
        error_log("Pasta selecionada: $pasta_selecionada");
    
        if (!is_dir($pasta_selecionada)) {
            jsonError('Pasta de documentos não encontrada para o benefício: ' . $tipo_beneficio);
        }
    
        // Listar arquivos manualmente
        $arquivos_word = [];
        $arquivos_pdf = [];
    
       $diretorio = opendir($pasta_selecionada);
    if ($diretorio) {
        while (($arquivo = readdir($diretorio)) !== false) {
            if ($arquivo != "." && $arquivo != "..") {
                $caminho_completo = $pasta_selecionada . '/' . $arquivo;
                if (is_file($caminho_completo)) {
                    $extensao = strtolower(pathinfo($caminho_completo, PATHINFO_EXTENSION));
                    if ($extensao === 'docx') {
                        $arquivos_word[] = $caminho_completo;
                    } elseif ($extensao === 'pdf') {
                        $arquivos_pdf[] = $caminho_completo;
                    }
                }
            }
        }
        closedir($diretorio);
    } else {
        jsonError('Não foi possível abrir a pasta: ' . $pasta_selecionada);
    }
    
    error_log("Arquivos Word encontrados: " . print_r($arquivos_word, true));
    error_log("Arquivos PDF encontrados: " . print_r($arquivos_pdf, true));
    
    if (empty($arquivos_word) && empty($arquivos_pdf)) {
        jsonError('Nenhum arquivo encontrado na pasta do benefício: ' . $tipo_beneficio);
    }
    
        $nome_pasta = str_replace(' ', '_', $nome);
        $pasta_destino = __DIR__ . '/../../documentos/site/' . $nome_pasta;
        error_log("Criando pasta de destino: $pasta_destino");
    
        if (!is_dir($pasta_destino)) {
            if (!mkdir($pasta_destino, 0777, true)) {
                jsonError('Falha ao criar a pasta de destino. Verifique as permissões.');
            }
        } else {
            // Verifica se a pasta de destino já existe
            $arquivos_existentes = glob("$pasta_destino/*");
    
            // Filtra os arquivos que pertencem ao tipo de benefício antigo
            foreach ($arquivos_existentes as $arquivo) {
                $nome_arquivo = basename($arquivo);
                if (strpos($nome_arquivo, $pastas_beneficios[$tipo_beneficio]) !== false) {
                    // Remove o arquivo antigo
                    unlink($arquivo);
                    error_log("Arquivo antigo removido: $arquivo");
                }
            }
        }
    
        $arquivos_processados = [];
    
        // Processar arquivos Word
        foreach ($arquivos_word as $arquivo) {
            error_log("Processando arquivo: $arquivo");
    
            // Cria uma cópia do arquivo modelo na pasta do cliente
            $novo_arquivo_word = "$pasta_destino/" . basename($arquivo);
            if (!copy($arquivo, $novo_arquivo_word)) {
                jsonError('Falha ao copiar o arquivo modelo para a pasta do cliente.');
            }
    
            // Processa o arquivo Word
            $templateProcessor = new TemplateProcessor($novo_arquivo_word);
    
            $templateProcessor->setValue('nome', $nome); #Nome
            $templateProcessor->setValue('nacionalidade', $nacionalidade); #Nacionalidade
            $templateProcessor->setValue('estado_civil', $estado_civil); #Estado Civil
            $templateProcessor->setValue('profissao', $profissao); #Profissão
            $templateProcessor->setValue('cpf', $cpf); #CPF
            $templateProcessor->setValue('rg', $rg); #RG
            $templateProcessor->setValue('orgao_expedidor', $orgao_expedidor); #Orgão Expedidor
            $templateProcessor->setValue('endereco', $endereco); #Endereço
            $templateProcessor->setValue('bairro', $bairro); #Bairro
            $templateProcessor->setValue('complemento', $complemento); #Complemento
            $templateProcessor->setValue('cidade', $cidade); #Cidade
            $templateProcessor->setValue('estado', $uf); #Estado/UF
            $templateProcessor->setValue('cep', $cep); #CEP
            $templateProcessor->setValue('data', $data_formatada); #Data de Emissão Formatada
            $templateProcessor->setValue('dia', $dia); #Dia de Emissão
            $templateProcessor->setValue('mes', $mes); #Mês de Emissão
            $templateProcessor->setValue('ano', $ano); #Ano de Emissão
            $templateProcessor->setValue('bairro_complemento', $bairro_complemento); #Bairro e Complemento
            $templateProcessor->setValue('localNasc', $localNasc); #Cidade de Nascimento
            $templateProcessor->setValue('cidade_cliente', $cidade_cliente); #Cidade de Nascimento
            $templateProcessor->setValue('estado_cliente', $uf_cliente); #Estado Cliente
            $templateProcessor->setValue('dataNasc', $dataNasc); #Cidade do Cliente
            $templateProcessor->setValue('inscrito', $inscrito); #Inscrito ou Inscrita
            $templateProcessor->setValue('domiciliado', $domiciliado); #Domiciliado ou Domiciliada
            $templateProcessor->setValue('portador', $portador); #Portador ou Portadora
    
            $templateProcessor->saveAs($novo_arquivo_word);
    
            error_log("Arquivo Word processado: $novo_arquivo_word");
    
            $conteudo_docx = file_get_contents($novo_arquivo_word);
            $conteudo_base64 = base64_encode($conteudo_docx);
    
            $arquivos_processados[] = [
                'nome' => basename($novo_arquivo_word),
                'conteudo' => $conteudo_base64,
                'tipo' => 'word'
            ];
        }
    
        try {
            // Processar arquivos PDF
            error_log("Chamando processarPDF...");
        
            // Preparar os dados para o script Node.js
            $dadosPdf = [
                'pasta_selecionada' => $pasta_selecionada,
                'pasta_destino' => $pasta_destino,
                'dados_formulario' => [
                    'editNome' => $nome,
                    'editCpf' => $cpf,
                    'editRg' => $rg,
                    'editCep' => $cep,
                    'editEndereco' => $endereco,
                    'editBairro' => $bairro,
                    'editCidade' => $cidade,
                    'editEstado' => $uf,
                    'editComplemento' => $complemento,
                    'editDataFormatada' => $data_formatada,
                    'editOrgaoExpedidor' => $orgao_expedidor,
                    'editLocalidade' => $localidade,
                    'editDataNascimento' => $data_nasc_formatada,
                    'editDataEmissao' => $data_emissao_formatada,
                    'editDia' => $dia,
                    'editMes' => $mes_number,
                    'editAno' => $ano,
                    'editData' => $data_formatada,
                    'editDiaNasc' => $diaNasc,
                    'editMesNasc' => $mesNasc,
                    'editAnoNasc' => $anoNasc,
                    'editCidadeCliente' => $cidade_cliente,
                    'editEstadoCliente' => $uf_cliente,
                    'editEnderecoCompleto' => $enderecoCompleto,
                ],
            ];
        
            // Caminho para o arquivo JSON (mesmo diretório do preencher-pdf.js)
            $jsonFile = __DIR__ . '/dados.json';
        
            // Salvar os dados no arquivo JSON
            file_put_contents($jsonFile, json_encode($dadosPdf));
        
            // Caminho completo do Node.js
            $nodePath = '/home/u899333487/.nvm/versions/node/v20.11.1/bin/node';
    
            // Comando para executar o script Node.js
            $command = "export NODE_PATH=/home/u899333487/.nvm/versions/node/v20.11.1/lib/node_modules; $nodePath " . __DIR__ . "/preencher-pdf.js $jsonFile 2>&1";
            error_log("Executando comando: $command");
        
            // Executar o script Node.js
            exec($command, $output, $return_var);
        
            if ($return_var !== 0) {
                throw new Exception("Erro ao processar PDFs: " . implode("\n", $output));
            }
        
            // Ler os arquivos PDF processados
            $arquivos_pdf_processados = json_decode(file_get_contents($jsonFile . '_output'), true);
            error_log("Arquivos PDF processados: " . print_r($arquivos_pdf_processados, true));
        
            // Adicionar os arquivos PDF processados à lista de arquivos
            $arquivos_processados = array_merge($arquivos_processados, $arquivos_pdf_processados);
        
            // Remover o arquivo JSON e o arquivo de saída
            if (file_exists($jsonFile)) {
                unlink($jsonFile);
            }
            if (file_exists($jsonFile . '_output')) {
                unlink($jsonFile . '_output');
            }
        } catch (Exception $e) {
            error_log("Erro ao processar PDFs: " . $e->getMessage());
            jsonError($e->getMessage());
        }
        
        if (count($arquivos_processados) > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Documentos processados com sucesso!',
                'arquivos' => $arquivos_processados,
                'nome' => $nome
            ]);
        } else {
            jsonError('Nenhum documento foi processado.');
        }
    
    } catch (Exception $e) {
        error_log("Erro no processamento: " . $e->getMessage());
        jsonError('Erro ao processar os documentos: ' . $e->getMessage());
    }
    
    function limparArquivosAntigos($pasta) {
        $arquivos = glob("$pasta/*");
        $agora = time();
    
        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo)) {
                $tempo_de_vida = $agora - filemtime($arquivo);
                if ($tempo_de_vida > 86400) { // 86400 segundos = 1 dia
                    unlink($arquivo);
                }
            }
        }
    }
    
    // Chame a função de limpeza após o processamento
    limparArquivosAntigos($pasta_destino);
    exit;
?>