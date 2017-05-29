<?php
session_start(); //Habilita sessões
require('funcoes/funcoes.php');
require('classes/Usuario.php'); //Classes usada em todas páginas
require('classes/Chamado.php');

//Função para deslogar
if (isset($_GET["sair"])) //Se o parâmetro pra sair for passado
{
    if (isset($_SESSION['TMW_usuario'])) //Confere antes se existe a sessão
    {
        unset($_SESSION['TMW_usuario']); //Mata ela
    }
    if (isset($_SESSION['TMW_administrador']))
    {
        unset($_SESSION['TMW_administrador']);
    }
    if (isset($_SESSION['TMW_tecnico']))
    {
        unset($_SESSION['TMW_tecnico']);
    }

    header('location:./'); //Realoca o usuário para o index.php
}