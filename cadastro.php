<?php
require "requires/starter.php";
if((isset($_SESSION['TMW_usuario'])) && ($_SESSION['TMW_usuario'] > 0)){
    header("location:chamados.php");
}
$cadastro = 0; //Variável que indica o estado de cadastro. 0 = Nenhum cadastro; -1 = Cadastro falho; 1 = Cadastro com sucesso.
$nome = "";
$login = "";
$nomepode = true;
$loginpode = true;
$senhapode = true;
if(isset($_POST['CadastrandoUsuario'])){
    //Arrumando as variáveis
    $nome = secureInputdata($_POST['inputNomeCadastro']);
    $login = secureInputdata($_POST['inputEmailCadastro']);
    $senha1 = md5($_POST['inputPasswordCadastro']);
    $senha2 = md5($_POST['inputPasswordAgainCadastro']);
    //
    //Testando-as no BD
    $nomepode = CadastroValidarInput('nome', $nome);
    $loginpode = CadastroValidarInput('login', $login);
    $senhapode = validarSenhas($senha1, $senha2);
    //

    if(($nomepode == true) && ($loginpode == true) && ($senhapode == true)) {
        $usuario = new Usuario(0, $nome, $login, $senha1, 1);
        if ($usuario->insert()) { //Se conseguimos inserir o usuário
            $cadastro = 1; //Sucesso
        } else {
            $cadastro = 0;
        };
    }
}
else{
    //echo "Não recebi nada!";
}
?>
<!DOCTYPE html>
<html lang=”pt-br”>
<head>
    <title>Cadastro Service Desk</title>
    <?php require "requires/header.php"; ?>
</head>

<body>
<?php
if($cadastro == -1) {
    echo "<p class='notification-topbar'>Houve algum erro. Por favor, tente novamente.<span id='topbarclose' class='popup-bt-close'>X</span></p>";
}
?>
<div class="container">
    <?php
    if($cadastro < 1){
    ?>
        <form action="cadastro.php" id="cadastrousuario" name="cadastrousuario" class="form-signin" method="POST" autocomplete="off">
            <h1 class="form-signin-heading">Cadastro</h1>
            <label for="inputNomeCadastro" class="">Nome de usuário</label>
            <input type="text" id="inputNomeCadastro" name="inputNomeCadastro" class="form-control" placeholder="Seu nome de usuário" title="Por favor, preencha com seu nome de usuário (exemplo: Seu nome e sobrenome)." maxlength="44" required autofocus <?php echo "value='".$nome."''"; ?> />
            <span id='errornomeCadastro' class='msg-error' <?php if($nomepode == true){echo "hidden";} ?> >Nome de usuário indisponível. Por favor, insira outro nome.<br/></span>
            <br/>
            <label for="inputEmailCadastro" class="">Email ou Registro</label>
            <input type="text" id="inputEmailCadastro" name="inputEmailCadastro" class="form-control" placeholder="Email ou Registro" title="Preencha com seu endereço de email ou registro da funcionário." maxlength="44" required <?php echo "value='".$login."''"; ?> />
            <span id='erroremailCadastro' class='msg-error' <?php if($loginpode == true){echo "hidden";} ?> >Email ou registro indisponível. Por favor, insira outro ou revise a informação.<br/></span>
            <br/>
            <label for="inputPasswordCadastro" class="">Senha</label>
            <input type="password" id="inputPasswordCadastro" name="inputPasswordCadastro" class="form-control" placeholder="Senha" title="Preencha com a senha escolhida." required />
            <label for="inputPasswordAgainCadastro" class="">Confirmar senha</label>
            <input type="password" id="inputPasswordAgainCadastro" name="inputPasswordAgainCadastro" class="form-control" placeholder="Digite a senha novamente" title="Digite a senha novamente para confirmação, por favor." required />
            <span id='errorsenhaCadastro' class='msg-error' <?php if($senhapode == true){echo "hidden";} ?> >As senhas não conferem. Por favor digite-as novamente.<br/></span>
            <br/>
            <button type="submit" id="CadastrandoUsuario" name="CadastrandoUsuario" class="btn btn-lg btn-primary btn-block">Cadastrar</button>
            <input id='cadastrocleaner' type="reset" class='btn btn-md btn-block' value="Limpar"/>
            <input type="button" class='goLogin btn btn-md btn-block' value="Cancelar" />
        </form>
    <?php
    }
    else{
        echo "<br/><span class='center-text'><h1>Cadastro realizado com sucesso!<br/><h2>Por favor, prossiga para o login.</h2></h1></span><br/>".
            "<div class='center-text'><input type='button' class='goLogin btn btn-primary btn-lg' value='PROSSEGUIR' /></div>";
    }
    ?>
</div> <!-- /container -->

<script>
$(document).ready(function() {
    $("#topbarclose").click(function() {
        $(".notification-topbar").remove();
    });

    $(".goLogin").click(function() {
        window.location = './';
    });
});
</script>

<?php require_once("requires/footer.php"); ?>
</body>
</html>