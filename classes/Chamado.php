<?php
require_once 'CRUD.php';

class Chamado extends CRUD{
    protected $table = 'chamados'; //Tabela
    private $idchamado;
    private $idcategoria;
    private $categoria;
    private $titulo;
    private $descricao;
    private $idusuario; //Usuário que abrir o chamado
    private $idadministrador; //Administrador que encaminhar o chamado
    private $idtecnico; //Técnico que atender o chamado
    private $estado; //Estado do chamado (0 = Aberto, 1 = Encaminhado, 2 = Fechado) //Sim, necessário saber sobre os 3 estados
    private $dataabertura;
    private $datafechamento;

    //Construtor
    public function __construct($idchamado="", $idcategoria="", $categoria="", $titulo="", $descricao="", $idusuario="", $idadministrador="", $idtecnico="", $estado=0, $dataaberto="", $datafechamento="") //Variáveis são opcionais pra facilitar o acesso dos métodos
    {
        $this->idchamado = $idchamado;
        $this->idcategoria = $idcategoria;
        $this->categoria = $categoria;
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->idusuario = $idusuario;
        $this->idadministrador = $idadministrador;
        $this->idtecnico = $idtecnico;
        $this->estado = $estado;
        $this->datachamada = $dataaberto;
        $this->datafechamento = $datafechamento;
    }

    //Getters e setters
    public function getIdchamado()
    {
        return $this->idchamado;
    }

    public function setIdchamado($idchamado)
    {
        $this->idchamado = $idchamado;
    }

    public function getIdcategoria()
    {
        return $this->idcategoria;
    }

    public function setIdcategoria($idcategoria)
    {
        $this->idcategoria = $idcategoria;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    public function getIdusuario()
    {
        return $this->idusuario;
    }

    public function setIdusuario($idusuario)
    {
        $this->idusuario = $idusuario;
    }

    public function getIdadministrador()
    {
        return $this->idadministrador;
    }

    public function setIdadministrador($idadministrador)
    {
        $this->idadministrador = $idadministrador;
    }

    public function getIdtecnico()
    {
        return $this->idtecnico;
    }

    public function setIdtecnico($idtecnico)
    {
        $this->idtecnico = $idtecnico;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function getDataabertura()
    {
        return $this->dataabertura;
    }

    public function setDataabertura($dataabertura)
    {
        $this->dataabertura = $dataabertura;
    }

    public function getDatafechamento()
    {
        return $this->datafechamento;
    }

    public function setDatafechamento($datafechamento)
    {
        $this->datafechamento = $datafechamento;
    }

    //Método de inserir o chamado no banco de dados. Utilizado na abertura de chamado
    public function insert()
    {
        $sql = "INSERT INTO $this->table(idcategoria, titulo, descricao, idusuario, idadministrador, idtecnico, estado, dataabertura, datafechamento) VALUES(:idcategoria, :titulo, :descricao, :idusuario, null, null, 0, NOW(), null)";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idcategoria', $this->idcategoria);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            return true; //Retorna que conseguimos abrir o chamado
        }
        else{
            return false;
        }
    }

    //Método usado pelo administrador pra encaminhar um chamado. O chamado pode ser encaminhado pra um técnico ou pra uma categoria em geral
    public function encaminhar(){
        $sql = "UPDATE $this->table SET idcategoria = :idcategoria, idadministrador = :idadministrador, idtecnico = :idtecnico, estado = 1 WHERE idchamado = :idchamado";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idcategoria', $this->idcategoria);
        $stmt->bindParam(':idadministrador', $_SESSION['TMW_usuario']); //Salva o id do administrador que tá encaminhando (que é o que está logado)
        $stmt->bindParam(':idtecnico', $this->idtecnico); //Pode ser um valor vazio. Se não for, o chamado é encaminhado diretamente pro técnico especificado
        $stmt->bindParam(':idchamado', $this->idchamado);

        $stmt->execute();

        if($stmt->rowCount() == 1) {
            return true; //Retorna que conseguimos encaminhar o chamado
        }
        else{
            return false;
        }
    }

    //Método usado pelos técnicos pra fechar um chamado
    public function fechar(){
        $sql = "UPDATE $this->table SET idtecnico = :idtecnico, estado = 2, datafechamento = NOW() WHERE idchamado = :idchamado";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idtecnico', $_SESSION['TMW_usuario']); //Salva o id do administrador que tá encaminhando (que é o que está logado)
        $stmt->bindParam(':idchamado', $this->idchamado);

        $stmt->execute();

        if($stmt->rowCount() == 1) {
            return true; //Retorna que conseguimos fechar o chamado, e com isso, fecho as funcionalidades (por enquanto)!
        }
        else{
            return false;
        }
    }

    //Método que faz uma lista de todas categorias
    public function findAllCategorias(){
        $sql = "SELECT * FROM categorias";
        $stmt = Database::prepare($sql);
        $stmt->execute();
        $categorias =  $stmt->fetchAll();
        $categorias = json_decode(json_encode($categorias),true); //Transforma o objeto em array
        return $categorias;
    }

    //Método que pega uma categoria especificada por um id
    public function findCategoria($id){
        $sql = "SELECT * FROM categorias WHERE idcategoria = :idcategoria";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idcategoria', $id);
        $stmt->execute();
        $categoria = $stmt->fetchAll();
        $categoria = json_decode(json_encode($categoria),true); //Transforma o objeto em array
        return $categoria;
    }

    //Método que seleciona uma lista com poucas informações sobre os chamados. Utilizada por todos tipos de usuário
    public function selectChamadosListaSM($tipohistorico){

        $sql = "SELECT c.idchamado, cat.titulo AS categoria, c.titulo, c.descricao, c.estado, c.dataabertura FROM $this->table AS c INNER JOIN categorias AS cat ON c.idcategoria=cat.idcategoria";
        if($tipohistorico == "administrador"){$sql = $sql." WHERE estado = 0";} //Se for um administrador, vai poder ver somente os chamados abertos que não foram encaminhados
        else{
            $sql = $sql." WHERE c.id".$tipohistorico."= :idusuario"; //Se formos um usuário comum ou técnico, queremos aqui informações atreladas a nosso próprio ID
            if($tipohistorico == "tecnico"){$sql = $sql." AND estado = 1";} //Se for um técnico, queremos selecionar somente chamados encaminhados, não o contrário, nem os realizados.
        }
        $sql = $sql." ORDER BY idchamado DESC"; //Adiciona a query o parâmetro que deve ser organizada lista do maior id ao menor, assim mantendo a ordem de registro
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idusuario', $_SESSION['TMW_usuario']);
        $stmt->execute();
        $chamados =  $stmt->fetchAll();
        $chamados = json_decode(json_encode($chamados),true); //Transforma o objeto em array
        return $chamados;
    }

    //Método igual ao de cima, porém, seleciona os chamados em base da categoria do técnico, sem utilizar id algum. Assim, pega todos chamados de uma mesma categoria. //Conferir depois de juntar esse ao método acima
    public function selectChamadosListaCategoriaSM(){
        $sql = "SELECT c.idchamado, cat.titulo AS categoria, c.titulo, c.descricao, c.estado, c.dataabertura FROM $this->table AS c INNER JOIN categorias AS cat ON c.idcategoria=cat.idcategoria WHERE cat.idcategoria = :idcategoria AND idtecnico = ''";
        $stmt = Database::prepare($sql);
        $stmt->bindParam(':idcategoria', $_SESSION['TMW_tecnico']);
        $stmt->execute();
        $chamados =  $stmt->fetchAll();
        $chamados = json_decode(json_encode($chamados),true); //Transforma o objeto em array
        return $chamados;
    }

    //Placeholder pro método de atualizar um chamado. Vai ser utilizado caso o adminstrador possa editar todas informações de um chamado
    public function update($id){
        //Sem utilização, por enquanto
    }
}