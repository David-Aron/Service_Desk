<?php
require "requires/starter.php";
if((!isset($_SESSION['TMW_usuario'])) || ($_SESSION['TMW_usuario'] < 1)){ //Se o usuário não está logado
    header("location:./"); //Retorna para o index (página de login)
}
else{
    $idusuario = $_SESSION['TMW_usuario']; //Se está logado, cria essa variável pra fácil acesso do id
}
?>
<!DOCTYPE html>
<html lang=”pt-br”>
<head>
    <title>Service Desk</title>
    <?php require "requires/header.php"; ?>
</head>
<body>
<div id='popups'></div> <!-- Container de popups, muito importante para mensagens e recursos dinâmicos -->
<div id='allcontainer'> <!-- Usado pra esconder, de vez em quando, os blocos da página -->
<?php
$usuario = new Usuario($idusuario); //Cria o usuário a ser usado
$usuariodata = $usuario->usuarioGetData(); //Pega as informações dele e salva em um array (e o próprio objeto) pra facilitar a manipulação delas

echo "<div class='logado-topbar'><span>".$usuariodata['nome']."</span><a href='chamados.php?sair=y' style='float:right; color:black;'>Sair</a></div>"; //Barra com nome do usuário e botão de logout. Aqui ficariam atalhos pra outros recursos, como cadastramento de técnicos por administradores, visualização de todas chamadas de todos estados, página de relatórios, etc.

//Administadror
if($usuariodata['administrador'] > 0){ //Se o usuário é um administrador
    $_SESSION['TMW_administrador'] = $usuariodata['administrador']; //Criamos a sessão de adm
?>
<div class="spaceabove"> <!-- Holder pra dar espaço acima do div sem erros nas margens -->
    <div class="block center postar">
        <div id="quadrochamadosholder">
            <h1 class="postar-title">Quadro de chamados</h1>
            <p>Revise os chamados e encaminhe-os para a categoria ou técnico corretos.</p>
            <div id="quadrochamados" class="center">
                <!--Esse é o quadro de chamados do administrador. Ele é preenchido dinamicamente por funções ajax, e atualizado automaticamente a cada 60 segundos -->
            </div>
        </div>
    <p style="float:right; font-weight: bold;">Chamados encaminhados: <span id="chamadosadmqnt"><?php echo $usuario->admGetQntEncaminhados(); ?></span></p><br/> <!-- Informação sobre quantos chamados o administrador já encaminhou -->
    </div>
</div>
<?php
}

//Tecnico
if($usuariodata['tecnico'] > 0){ //Se é um técnico... Não usamos 'else if', assim deixando um pouco aberta a possibilidade de um técnico ser administrador também
    $_SESSION['TMW_tecnico'] = $usuariodata['tecnico']; //Criamos a sessão de técnico
?>
<div class="spaceabove"> <!-- Holder pra dar espaço acima do div sem erros nas margens -->
    <div class="block center postar">
        <div id="quadrochamadosholder">
            <h1 class="postar-title">Chamados abertos</h1>
            <p>Confira os chamados de problemas e resolva.</p>
            <div id="listachamados" class="center">
                <!-- Listas dinâmicas de chamados do técnico. Elas se atualizam a cada 60 segundos -->
            </div>
        </div><br/>
    <br/><p style="float:right; font-weight: bold;">Chamados resolvidos: <span id="chamadostecqnt"><?php echo $usuario->tecGetQntResolvidos(); ?></span></p><br/>
    </div>
</div>
<?php
}

//Usuario
//Abaixo, segue o menu para criação de chamada. Sem testes sobre o tipo de usuário, pra assim permitir que tanto um técnico ou administrador possam abrir um chamado
?>
<div class="spaceabove"> <!-- Holder pra dar espaço acima do div sem erros nas margens -->
    <div class="block center postar">
        <div id="criachamadoholder" style="display:inline-block; width:60%;"> <!-- Holder do formulário pra abertura de chamado -->
            <h1 class="postar-title">Abrir chamado</h1>
            <p>Encontrou um problema? Faça um chamado com o formulário abaixo e um técnico irá resolver.</p>
            <form id="chamadoabertura" name="chamadoabertura"> <!-- Form de abertura -->
                <?php

                //Categorias
                $categorias = new Chamado();
                $categorias = $categorias->findAllCategorias();
                $categorias = json_decode(json_encode($categorias),true);
                ?>
                <select class="form-control" style="display:inline-block; width:30%;" id="inputCategoria" name="inputCategoria">
                    <option value='--' title='Selecione uma categoria'>Selecionar categoria</option> <!-- Opção default, que é inválida, assim o usuário precisa selecionar uma válida no select -->
                    <?php
                    foreach($categorias as $cat){
                        echo "<option value='".$cat['idcategoria']."' title='".$cat['descricao']."'>".$cat['titulo']."</option>";
                    }
                ///

                    ?>
                </select><span>&nbsp;Seleciona a categoria do problema.</span>

                <label for="inputTitulo" class="sr-only">Assunto</label>
                <input type="text" id="inputTitulo" name="inputTitulo" class="form-control" style="display:inline-block; width:90%;" placeholder="Problema" maxlength="90" required />
                <textarea style="width: 90%;" class='form-control' id='inputDescricao' name="inputDescricao" placeholder="Descreva o problema" required rows="10" cols="500" maxlength="1000" wrap="hard"></textarea> <!-- Textarea pra haver bastante espaço pradigitação -->
                <br/>
                <button name="AbrindoChamado" id="AbrindoChamado" class="btn btn-lg btn-secondary" style='width:90%;' type="button">ENVIAR</button> <!-- Botão de envio do chamado. Esse botão tem um click listener que inicia a abertura do chamado -->
            </form>
            <h4 id='chamadoaberturaw' class='msg-error' hidden>Por favor, preencha todos campos.</h4> <!-- Mensagem de erro que aparecerá para o usuário caso ele não preencha corretamente os dados (Incluindo não selecionar uma categoria) -->
        </div> <!-- /holder do formulário -->
        <div style="display:inline-block; width:40%; float:right;"> <!-- Caixa ao lado do holder anterior -->
            <h3 class="postar-title">Seus chamados</h3>
            <div id="chamados-historico" class="chamados-historico">
                <!-- Aqui fica um registro dos chamados abertos pelo próprio usuário, assim ele pode acompanhar o andamento deles -->
            </div>
        </div>
    </div>
</div>
</div> <!-- /allcontainer -->

<script> //Funções Javascript e Jquery
$(document).ready(function() { //Quando a leitura do document terminar

    $("#AbrindoChamado").click(function() { //Se o usuário clicou no botão de abrir um chamado
        crudChamados("CreateChamado"); //*Chama* a função crud de chamados com o parâmetro de criação
    });

    if($("#quadrochamados").length ){ //Se existe um quadro de chamados (adm)
        UpdateQuadro(); //Insere o conteúdo nele
    }

    if($("#chamados-historico").length ){ //Se existe um histórico de chamados (usuário)
        UpdateHistorico(); //Insere conteúdo
    }

    if($("#listachamados").length ){ //Se existem as listas de chamados (técnico)
        UpdateLista(); //Insere conteúdo
    }
});

//Função Jquery AJAX para montar um popup
function createpopup(frase1, frase2 = ""){
    $.get('funcoes/funcoes_ajax.php', { func: "CreatePopup", frase: frase1, frase2: frase2}, function(data){
        $('#popups').empty();
        $('#popups').append(data).hide().fadeIn(200);
        $('#allcontainer').hide();
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função Jquery para remover o popup
function popupRemove(){
    $('#popups').fadeOut(200, function(){
        $('#popups').empty();
        $('#allcontainer').show();
        $('#popups').show();
    });
}

//Função CRUD dinâmico de chamados. Basicamente, quase todas ações de BD podem usar ela como padrão, precisando então só dos parâmetros
function crudChamados(funcao){
    pode = true; //Variável usada pra indicar que podemos enviar AJAX
    if(funcao == "CreateChamado"){ //Se for a função de criar chamado, nesse caso, a partir do formulário
        if(($('#inputDescricao').val() == "") || ($('#inputTitulo').val() == "") || ($('#inputCategoria').val() == "--")) //Conferimos se tem algum campo preenchidos incorretamente
        { //Se sim, mostramos o aviso
            $('#chamadoaberturaw').show();
            pode = false; //Não pode prosseguir pro AJAX
        }
    }
    if(pode == true){ //Caso tudo esteja certo e possa prosseguir
        $('#chamadoaberturaw').hide(); //Esconde o aviso de erro de preenchimento. Isso executa sempre sim, por precaução
        $.post('funcoes/funcoes_ajax.php', { func: funcao, frase: $('#inputCategoria').val(), frase2: $('#inputTitulo').val(), frase3: $('#inputDescricao').val(), frase4:$('#inputVIDchamado').val()}, function(data){ //Super AJAX
            $('#popups').empty();
            $('#popups').append(data);
            if(funcao == "CreateChamado"){ //Caso tenha sido pra criar um chamado, vamos resetar os dados do formulário
                $('#inputCategoria option[value=--]').attr('selected','selected');
                $('#inputTitulo').val('')
                $('#inputDescricao').val('')
            }
            UpdateHistorico(); //Atualiza as informações no histórico de chamados (pra mostrar as alterações)
            if($("#quadrochamados").length ) { //Caso tenhamos o quadro de chamados presente na página
                UpdateQuadro(); //Atualizamos suas informações tbm
            }
            if($("#listachamados").length ) { //Caso tenhamos as listas de chamados presentes
                UpdateLista(); //Atualizamos
            }
        }).fail(function() {
            alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
        });
    }
}

//Função AJAX de encaminhamento de chamado (ADM). Não vai pro CRUD pq a estrutura dela é um tanto quanto diferente
function EncaminharChamado(){
    $.post('funcoes/funcoes_ajax.php', { func: "EncaminharChamado", frase: $('#inputVIDchamado').val(), frase2: $('select[name=inputVCategoria]').val(), frase3: $('select[name=inputVTecnico]').val()}, function(data){
        $('#popups').empty();
        $('#popups').append(data);

        UpdateHistorico(); //Atualiza as informações no histórico de chamados (pra mostrar as alterações)
        if($("#quadrochamados").length ) {
            UpdateQuadro(); //Atualizamos
            UpdateADMqntEncaminhados(); //E atualizamos a msg que diz quantos chamados esse adm já encaminhou
        }
        if($("#listachamados").length ) { //Caso tenhamos as listas de chamados presentes
            UpdateLista(); //Atualizamos
        }
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função AJAX de fechamento de chamado (Técnico)
function FecharChamado(){
    $.post('funcoes/funcoes_ajax.php', { func: "FecharChamado", frase: $('#inputVIDchamado').val() }, function(data){
        $('#popups').empty();
        $('#popups').append(data);

        UpdateHistorico(); //Atualiza as informações no histórico de chamados (pra mostrar as alterações)
        if($("#quadrochamados").length ) {
            UpdateQuadro(); //Atualizamos
        }
        if($("#listachamados").length ) { //Caso tenhamos as listas de chamados presentes
            UpdateLista(); //Atualizamos
            UpdateTECqntFechados(); //E atualizamos a msg que diz quantos chamados esse técnico já fechou
        }
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função necessária pra chamar o CRUD de chamados com parâmetro Delete. Sem essa função, teria muita, muita concatenação no php pra passar o parâmetro direto ajax
function DeletarChamado(){
    crudChamados("DeletarChamado");
}

//Mesmo que acima, só que para mostrar o chamado
function mostraChamado(id){
    createpopup('mostraChamado', id);
}

//Função AJAX de atualizar o quadro de chamados (adm)
function UpdateQuadro(){
    $.post('funcoes/funcoes_ajax.php', { func: 'AtualizaQuadroChamados'}, function(data){
        $('#quadrochamados').empty();
        $('#quadrochamados').append(data).hide().fadeIn(200);

        UpdateQuadroTimer(); //Chama uma função timer pra atualização ocorrer automaticamente
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função AJAX de atualizar o histórico de chamados (usuário)
function UpdateHistorico(){
    $.post('funcoes/funcoes_ajax.php', { func: 'AtualizaHistoricoChamados'}, function(data){
        $('#chamados-historico').empty();
        $('#chamados-historico').append(data).hide().fadeIn(200);
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função AJAX de atualizar as listas de chamados (técnico)
function UpdateLista(){
    $.post('funcoes/funcoes_ajax.php', { func: 'AtualizaListaChamados'}, function(data){
        $('#listachamados').empty();
        $('#listachamados').append(data).hide().fadeIn(200);

        UpdateListaTimer(); //Chama uma função timer pra atualização ocorrer automaticamente
    }).fail(function() {
        alert( "Houve algum erro. Por favor, tente novamente mais tarde." );
    });
}

//Função timer de atualização automática do quadro de chamados
function UpdateQuadroTimer(){
    setTimeout(
        function()
        {
            UpdateQuadro(); //Atualiza o quadro
        }, 60000) //A cada 60 segundos
}

//Função timer de atualização automática das listas de chamados
function UpdateListaTimer(){
    setTimeout(
        function()
        {
            UpdateLista(); //Atualiza as listas
        }, 60000) //A cada 60 segundos
    }

//Função pra atualizar a msg sobre a quantidade de chamados encaminhados pelo adm
function UpdateADMqntEncaminhados(){
    $("#chamadosadmqnt").text(parseInt($('#chamadosadmqnt').text())+1); //Só converte pra int e adiciona 1. Funciona
}

//Função pra atualizar a msg sobre a quantidade de chamados fechados pelo adm
function UpdateTECqntFechados(){
    $("#chamadostecqnt").text(parseInt($('#chamadostecqnt').text())+1); //Só converte pra int e adiciona 1. Também funciona
}
</script>

<?php require_once("requires/footer.php"); ?>
</body>
</html>
