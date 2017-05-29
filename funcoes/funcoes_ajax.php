<?php //Nesse php ficam as funções usadas nos AJAX, então ele é meio 'monstro'
session_start();
require 'funcoes.php';
require_once('../classes/Chamado.php'); //Já incluo esses .php's pois ambos são usados em quase todas funções aqui
require_once('../classes/Usuario.php');

//Testes com parâmetros get
if(isset($_GET['frase'])){$frase = $_GET['frase'];}
if(isset($_GET['frase2'])){$frase2 = $_GET['frase2'];}

//Testes com parâmetros post
if(isset($_POST['frase'])){$frase = $_POST['frase'];}
if(isset($_POST['frase2'])){$frase2 = $_POST['frase2'];}
if(isset($_POST['frase3'])){$frase3 = $_POST['frase3'];}
if(isset($_POST['frase4'])){$frase4 = $_POST['frase4'];}

if(isset($_GET['func']))
{
    $func = $_GET['func']; //Define pra facilitar

    //Chamadas de funções
    if($func == "CreatePopup"){CreatePopup($frase, $frase2);} //Função costrutora do popup principal
}
else if(isset($_POST['func'])) //Ou se recebemos como post
{
    $func = $_POST['func']; //Sim, facilitar

    //Chamadas de funções
    if($func == "CreateChamado"){CreateChamado($frase, $frase2, $frase3);}
    if($func == "DeletarChamado"){DeletarChamado($frase4);}
    if($func == "EncaminharChamado"){EncaminharChamado($frase, $frase2, $frase3);}
    if($func == "FecharChamado"){FecharChamado($frase);}
    if($func == "AtualizaQuadroChamados"){AtualizaQuadroChamados();}
    if($func == "AtualizaHistoricoChamados"){AtualizaHistoricoChamados();}
    if($func == "AtualizaListaChamados"){AtualizaListaChamados();}
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
}

function CreatePopup($frase, $frase2 = "") //A função
{
    if($frase == 'mostraChamado'){ //Se estamos querendo ver o popup que mostra as informações de um único chamado
        $chamado = new Chamado(); //Criamos o objeto

        $chamadox = $chamado->find("idchamado", $frase2); //Pegamos as informações do chamado que queremos ver (passando pra um array com objeto)
        $chamadox = json_decode(json_encode($chamadox),true); //Transformamos o objeto do array em array tbm
        ?>
        <div id='popup' class='popup-background background-white'> <!-- Fundo e holder do popup -->
            <div class="block center postar"> <!-- Corpo -->

                <?php
                $categoria = $chamado->findCategoria($chamadox[0]['idcategoria']); //Encontramos a categoria do chamado

                //Estado do chamado
                if($chamadox[0]['estado'] == 0){ //Se o estado é 0 (ainda não encaminhado)
                    $estado = "<span style='color:red;' title='Será encaminhado para um técnico'>&olcir; Chamado aberto.</span>";
                }
                else if($chamadox[0]['estado'] == 1){ //Se o estado é 1 (encaminhado)
                    $estado = "<span style='color:dodgerblue;' title='Será resolvido por um técnico'>&olcir; Chamado encaminhado.</span>";
                }
                else{ //No caso de ser 2 (já resolvido
                    $estado = "<span style='color:darkseagreen;' title='Resolvido'>&ofcir; Chamado fechado. Problema resolvido!</span>";
                }
                if((isset($_SESSION['TMW_tecnico'])) && ($chamadox[0]['estado'] == 1)){ //Caso seja um técnico logado e o chamado tenha sido encaminhado, vai aparecer um botão pra definir o pedido como fechado
                    $estado = $estado."&nbsp;<input type='button' class='btn btn-md' value='Definir como fechado' onclick='FecharChamado();'/>"; //Botão
                }
                ///
                $categoriaatual = $categoria[0]['titulo']; //Pega o título da categoria atual

                //Categoria do chamado
                if((isset($_SESSION['TMW_administrador'])) && ($chamadox[0]['estado'] == 0)) { //Se estamos logados como administrador e o pedido está aberto, podemos mudar a categoria do chamado
                    echo "<h3>Categoria: ";
                    $categorias = new Chamado();
                    $categorias = $categorias->findAllCategorias(); //Pega todas categorias no BD

                    //Criando um select com o array de categorias acima. Então, se for um adm, aparece o select interagivel invés de somente a informação
                    echo "<select class='form-control' style='display:inline-block; width:200px;' id='inputVCategoria' name='inputVCategoria'>"; //Select
                    foreach ($categorias as $cat) { //Para cada categoria
                        echo "<option value='" . $cat['idcategoria'] . "' title='" . $cat['descricao'] . "'"; //Criamos a option
                        if ($cat['titulo'] == $categoriaatual) { //Se for a categoria atual do chamado
                            echo " selected"; //Deixamos ela como default no select
                        }
                        echo ">" . $cat['titulo'] . "</option>"; //Fechamos o option
                    }
                    echo "</select></h3>"; //Fechamos o select
                }
                else{ //Caso não seja um administrador
                    echo "<h3>Categoria: ".$categoriaatual."</h3>"; //Somente apresenta a informação, não permitindo muda-la
                }
                ///

                echo "<input type='text' id='inputVIDchamado' name='inputVIDchamado' value='".$chamadox[0]['idchamado']."' hidden />"; //ID escondido, usado em funções javascript pra edição do chamado

                echo "<h1>Problema: ".$chamadox[0]['titulo']."</h1>"; //Só apresentando informações
                echo "<h3>Situação: ".$estado."</h3>";
                echo "<p>Descrição:<br/>&nbsp;".$chamadox[0]['descricao']."</p>";

                //Encaminhamento para técnico específico
                if(isset($_SESSION['TMW_administrador'])){ //Se for um adm logado
                    if($chamadox[0]['estado'] == 0){ //Se o estado do chamado for aberto (0), o botão para encaminhamento
                        $tecnicos = new Usuario();
                        $tecnicos = $tecnicos->fetchTecnicosSM(); //Pegamos uma lista com todos técnicos

                        echo "<select class='form-control' style='display:inline-block; width:200px;' id='inputVTecnico' name='inputVTecnico'>"; //Criamos um select
                            echo "<option value=''>--</option>"; //Opção pra possibilitar que o chamado seja encaminhado para todos técnicos em uma mesma categoria
                            foreach($tecnicos as $tecnico){ //De cada técnico...
                                echo "<option value='".$tecnico['idusuario']."'>".$tecnico['nome']."</option>"; //...Criamos uma opção no select
                            }
                        echo "</select><span>&nbsp;Se quiser direcionar o chamado a um técnico específico, selecione-o.</span><br/>"; //Breve explicação pro administrador saber o que muda

                        echo "<br/><input type='button' class='btn btn-md' value='Encaminhar chamado' onclick='EncaminharChamado()'/>"; //O administrador pode usar botão pra encaminhar o chamado, chamando uma função jquery. Onclick assim pois listeners não funcionam em eventos declarados após eles
                    }
                    echo "&nbsp;<input type='button' class='btn btn-md' value='Apagar chamado' onclick='DeletarChamado()'/><br/>"; //Mesmo que acima, só que pra deletar o chamado
                }
                ///

                echo "<br/><input type='button' class='btn btn-sm' value='Voltar' onclick='popupRemove();'/>"; //Pra fechar o popup
                ?>
            </div> <!-- /Corpo -->
        </div> <!-- /fundo e holder -->
        <?php
    }
    //Aqui entrariam testes pra chamar outros construtores de popup
}

function CreateChamado($idcategoria, $titulo, $descricao){ //Função de criação de chamado. No final, chama um popup notificador do resultado
    $idusuario = $_SESSION['TMW_usuario'];
    $chamado = new Chamado(null, $idcategoria, null, $titulo, $descricao, $idusuario);
    if($chamado->insert()){
        popupsm("Abertura de chamado realizada", "Confira a situação dele pelo seu histórico de chamados");
    }
    else{
        popupsm("Houve algum problema, por favor tente novamente mais tarde");
    }
}

function DeletarChamado($idchamado){ //Mesmo que acima, mas pra deletar um chamado
    $chamado = new Chamado();
    if($chamado->deleteField("idchamado", $idchamado)){
        popupsm("Chamado removido");
    }
    else{
        popupsm("Houve algum problema, por favor tente novamente mais tarde");
    }
}

function EncaminharChamado($idchamado, $idcategoria, $idtecnico=""){ //Função pra encaminhar um chamado (adm)
    $idusuario = $_SESSION['TMW_usuario'];
    $chamado = new Chamado($idchamado, $idcategoria, null, null, null, null, null, $idtecnico); //Vários null's pois esse update de chamado é pequeno e usa poucas informações
    if($chamado->encaminhar($idchamado)){
        popupsm("Encaminhamento de chamado realizado");
    }
    else{
        popupsm("Houve algum problema, por favor tente novamente mais tarde");
    }
}

function FecharChamado($idchamado){ //Função pra fechar um chamado (técnico)
    $chamado = new Chamado($idchamado); //Sim, só precisamos passar o id
    if($chamado->fechar($idchamado)){
        popupsm("Fechado fechado. Obrigado pelo trabalho!");
    }
    else{
        popupsm("Houve algum problema, por favor tente novamente mais tarde");
    }
}

function AtualizaQuadroChamados(){ //Função construtora do quadro de chamados do adm
    $chamadosquadro = new Chamado();
    $chamadosquadro = $chamadosquadro->selectChamadosListaSM("administrador"); //Pega as informações sobre os chamados
    if(empty($chamadosquadro)){ //Caso não haja nenhum chamado
        echo "<p>Nenhum chamado para mostrar.</p>";
    }
    else{
        foreach($chamadosquadro as $chamado){ //Caso haja algum, criamos um item para cada
            $abertura = date('d-m-Y', strtotime(str_replace('-', '/', $chamado['dataabertura']))); //Convertendo a data do bd pro padrão brasileiro
            $titulo = substr($chamado['titulo'], 0, 50);
            echo "<div onclick='mostraChamado(".$chamado['idchamado'].")'class='chamadoquadroholder'><div class='cursorpointer chamadoquadro'>".
                "<span>".$titulo."...</span><br/>".
                "<span style='color:lightgray;'>".$chamado['categoria']."</span><br/>".
                "<span>".$abertura."</span><br/>".
                "</div></div>&nbsp;";
        }
    }
}

function AtualizaListaChamados(){ //Função construtora das listas de chamados dos técnicos
    echo "<div style='width:45%; display:inline-block;'>"
        ."<h3>Chamados direcionados a você</h1>";
    montaListaChamadosTecnicos("tecnico"); //Chama uma função padrão pra montar a lista
    echo "</div>";
    echo "<div style='width:45%; display:inline-block; float:right;'>"
        ."<h3>Chamados para sua categoria</h1>";
    montaListaChamadosTecnicos("categoria"); //Função padrão
    echo "</div>";
}

function AtualizaHistoricoChamados(){ //Função construtora da lista comum de chamados aberto pelo usuário
    $chamadohistorico = new Chamado();
    $chamadohistorico = $chamadohistorico->selectChamadosListaSM("usuario"); //Pega as informações dos chamados
    if(empty($chamadohistorico)){ //Se não houver nenhum chamado
        echo "<p>Nenhum chamado para mostrar.</p>";
    }
    else{
    ?>
        <table id="chamadoshu"> <!-- Estrutura da lista -->
            <tr>
                <th></th>
                <th>Abertura</th>
                <th>Categoria</th>
                <th>Problema</th>
        </tr>
    <?php
        foreach ($chamadohistorico AS $chamado) { //Para cada chamado que pegamos, criamos uma linha nova na lista
            $abertura = date('d-m-Y', strtotime(str_replace('-', '/', $chamado['dataabertura']))); //Convertendo a data do bd pro padrão brasileiro
            $titulo = substr($chamado['titulo'], 0, 14);

            //Estados
            if ($chamado['estado'] == 0) {
                $estado = "<span style='color:red;' title='Chamado aberto'>&olcir;</span>";
            }
            else if ($chamado['estado'] == 1) {
                $estado = "<span style='color:dodgerblue;' title='Chamado encaminhado'>&ofcir;</span>";
            }
            else{
                $estado = "<span style='color:darkseagreen;' title='Chamado fechado'>&ofcir;</span>";
            }
            //

            echo "<tr class='cursorpointer' title='" . $chamado['titulo'] . "' onclick='mostraChamado(" . $chamado['idchamado'] . ")'>" .
                "<td>" . $estado . "</td>" .
                "<td>" . $abertura . "</td>" .
                "<td>" . $chamado['categoria'] . "</td>" .
                "<td>" . $titulo . "...</td>" .
                "</tr>";
        }
    }
    echo " </table>";
    ?>
<?php
}