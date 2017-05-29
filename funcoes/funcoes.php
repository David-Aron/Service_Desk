<?php //Algumas funções globais aqui

function secureInputdata($data){ //Usada pra deixar seguro o conteúdo digitado em campos input
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = addslashes($data);
    return $data;
}

function CadastroValidarInput($field, $value){ //Usada no cadastro de usuário, pra conferir se os valores estão livres de colisão
    $usuario = new usuario();
    $resultado = $usuario->find($field, $value);
    foreach($resultado as $res) {
        if (isset($res->login)) {
            return false;
        }
    }
    return true; //Se estão, retorna true
}

function validarSenhas($senha1, $senha2){ //Função básica pra comparar dois valores. Separei assim pois achei que pode vir a ser usada em outras coisas
    if($senha1 == $senha2){return true;}
    else{return false;}
}

function popupsm($title="", $content=""){ //Função pra criar um popup pequeno na tela. Usada pra notificações gerais, como um cadastro realizado ou falho
    echo "<div id='popup3' class='popup-background popup-background-black cursorpointer' onclick='popupRemove()' >". //Onclick pois listeners não pegam
        "<div class='block center postar' style='margin-top:13%; width:60%;'>".
        "<p onclick='popupRemove()' class='popup-bt-close' style='color:black;'>X</p><br/>". //Mesmo acima
        "<h1 class='center-text'>".$title."</h1>".
        "<h3 class='center-text'>".$content."</h3>".
        "</div></div>"; //Um tanto feia, mas é bem útil.
}

function montaListaChamadosTecnicos($tipo){ //Função montadora de listas do adm, já que tem duas listas praticamente iguais, assim diminuimos bastante o código
    $chamadohistorico = new Chamado(); //Criamos o objeto
    if($tipo == "tecnico"){ //Se queremos ver a lista de chamados específicos para esse técnico
        $chamadohistorico = $chamadohistorico->selectChamadosListaSM($tipo);
    }
    else{ //Se queremos ver a lista de todos chamados pra categoria desse técnico
        $chamadohistorico = $chamadohistorico->selectChamadosListaCategoriaSM();
    }
    if(empty($chamadohistorico)){ //Se não há nenhum chamado
        echo "<p>Nenhum chamado para mostrar.</p>";
    }
    else{ //Se houver algum
        if($tipo == "tecnico"){echo "<table id='chamadosht'>";} //Caso seja do técnico, será o elemento com esse id
        else{echo "<table id='chamadoshc'>";} //Caso não, será com um id diferente
        ?>
            <tr> <!-- Estrutura da lista -->
                <th></th>
                <th>Abertura</th>
                <th>Categoria</th>
                <th>Problema</th>
                <th>Descrição</th>
            </tr>
        <?php
        foreach ($chamadohistorico AS $chamado) { //Para cada chamado que houver pra lista, criamos uma linha nova nela
            $abertura = date('d-m-Y', strtotime(str_replace('-', '/', $chamado['dataabertura']))); //Convertendo a data do bd pro padrão brasileiro
            $titulo = substr($chamado['titulo'], 0, 50); //Encurtando o título
            $descricao = substr($chamado['descricao'], 0, 25); //Encurtando a descrição

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
                "<td>" . $descricao . "...</td>" .
                "</tr>";
        }
    }
    echo "</table>";
}
