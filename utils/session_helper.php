<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function verificar_login()
{
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_perfil'])) {
        
        header("Location: /restaurante/index.php");
        exit();
    }
}

/**
 * Restringe o acesso de uma página a perfis específicos (ex: 'admin' ou 'garcom').
 * Se o usuário logado não pertencer ao perfil permitido, ele é redirecionado de volta ao local adequado.
 * * @param string $perfil_permitido 'admin' ou 'garcom'
 */
function verificar_perfil($perfil_permitido)
{

    verificar_login();

    if ($_SESSION['usuario_perfil'] !== $perfil_permitido) {
        
        if ($_SESSION['usuario_perfil'] === 'admin') {
            header("Location: /restaurante/admin/dashboard.php");
        } else {
            header("Location: /restaurante/mesas/mapa.php");
        }
        exit();
    }
}

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}