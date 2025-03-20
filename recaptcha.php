<?php

    require '/home/usuario/config/config.php';
    require __DIR__ . '/../../back_end/connect.php'; // Conexão com o banco
    session_start();

    // Verificar se o usuário tem acesso
    if (!isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true) {
        // Redirecionar para a página de bloqueio se não tiver acesso
        header("Location: index.php"); // Ou a página que bloqueia o acesso
        exit;
    }

    $recaptcha_secret = RECAPTCHA_SECRET;
    $recaptcha_response = $_POST['recaptcha_response'];

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

    if ($responseKeys["success"] && $responseKeys["score"] >= 0.5) {
        echo "Validação bem-sucedida!";
    } else {
        echo "Falha na verificação reCAPTCHA.";
    }
?>
