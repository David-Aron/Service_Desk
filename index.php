<?php
require "requires/starter.php";
if((isset($_SESSION['TMW_usuario'])) && ($_SESSION['TMW_usuario'] > 0)){
    header("location:chamados.php");
}
$login = "";

if(isset($_POST['LogandoUsuario'])){
    //Arrumando as variáveis
    $login = secureInputdata($_POST['inputLogin']);
    $senha = md5($_POST['inputPassword']);

    $usuario = new Usuario(0, "", $login, $senha, 1);
    if($usuario->login()){
        header("location:chamados.php");
    }
    else{
        $loginerro = 1;
    }
}
?>
<!DOCTYPE html>
<html lang=”pt-br”>
<head>
    <title>Login Service Desk</title>
    <?php require "requires/header.php"; ?>
</head>
<body>
<div class="container">
    <form action="./" id="loginusuario" name="loginusuario" class="form-signin" method="POST">
        <h2 class="form-signin-heading">Realizar login</h2>
        <label for="inputLogin" class="sr-only">Email ou Registro</label>
        <input type="text" id="inputLogin" name="inputLogin" class="form-control" placeholder="Email ou Registro" required autofocus <?php echo "value='".$login."'"; ?> />
        <label for="inputPassword" class="sr-only">Senha</label>
        <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Senha" required />
        <?php
        if(isset($loginerro)){
            echo "<span class='msg-error'>Dados incorretos, por favor digite-os novamente<br/></span>";
        }
        ?>
        <br/>
        <button name="LogandoUsuario" class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
        <br/>
          <p>Ou <a href='cadastro.php'>cadastre-se como usuário</a>.</p>
    </form>
</div> <!-- /container -->

<?php require_once("requires/footer.php"); ?>
</body>
</html>