<?php
    session_start();
    session_destroy();  // Destroi todas as variáveis de sessão
    header("Location: ../index.php");  // Redireciona para a página inicial
    exit;
?>