<?php

require_once 'include/pmieducar/geral.inc.php';
require_once 'RegraAvaliacao/Model/RegraDataMapper.php';

class clsPmieducarSerie
{

    public $cod_serie;
    public $ref_usuario_exc;
    public $ref_usuario_cad;
    public $ref_cod_curso;
    public $nm_serie;
    public $etapa_curso;
    public $concluinte;
    public $carga_horaria;
    public $data_cadastro;
    public $data_exclusao;
    public $ativo;
    public $regra_avaliacao_id;
    public $regra_avaliacao_diferenciada_id;

    public $idade_inicial;
    public $idade_final;
    public $idade_ideal;

    public $alerta_faixa_etaria;
    public $bloquear_matricula_faixa_etaria;
    public $exigir_inep;

    /**
     * Armazena o total de resultados obtidos na última chamada ao método lista().
     *
     * @var int
     */
    public $_total;

    /**
     * Nome do schema.
     *
     * @var string
     */
    public $_schema;

    /**
     * Nome da tabela.
     *
     * @var string
     */
    public $_tabela;

    /**
     * Lista separada por vírgula, com os campos que devem ser selecionados na
     * próxima chamado ao método lista().
     *
     * @var string
     */
    public $_campos_lista;

    /**
     * Lista com todos os campos da tabela separados por vírgula, padrão para
     * seleção no método lista.
     *
     * @var string
     */
    public $_todos_campos;

    /**
     * Valor que define a quantidade de registros a ser retornada pelo método lista().
     *
     * @var int
     */
    public $_limite_quantidade;

    /**
     * Define o valor de offset no retorno dos registros no método lista().
     *
     * @var int
     */
    public $_limite_offset;

    /**
     * Define o campo para ser usado como padrão de ordenação no método lista().
     *
     * @var string
     */
    public $_campo_order_by;

    /**
     * Construtor.
     */
    public function __construct(
        $cod_serie = null,
        $ref_usuario_exc = null,
        $ref_usuario_cad = null,
        $ref_cod_curso = null,
        $nm_serie = null,
        $etapa_curso = null,
        $concluinte = null,
        $carga_horaria = null,
        $data_cadastro = null,
        $data_exclusao = null,
        $ativo = null,
        $idade_inicial = null,
        $idade_final = null,
        $regra_avaliacao_id = null,
        $observacao_historico = null,
        $dias_letivos = null,
        $regra_avaliacao_diferenciada_id = null,
        $alerta_faixa_etaria = false,
        $bloquear_matricula_faixa_etaria = false,
        $idade_ideal = null,
        $exigir_inep = false
    ) {
        $db = new clsBanco();
        $this->_schema = 'pmieducar.';
        $this->_tabela = "{$this->_schema}serie";
        $this->_campos_lista = $this->_todos_campos = 's.cod_serie, s.ref_usuario_exc, s.ref_usuario_cad, s.ref_cod_curso, s.nm_serie, s.etapa_curso, s.concluinte, s.carga_horaria, s.data_cadastro, s.data_exclusao, s.ativo, s.idade_inicial, s.idade_final, s.regra_avaliacao_id, s.observacao_historico, s.dias_letivos, s.regra_avaliacao_diferenciada_id, s.alerta_faixa_etaria, s.bloquear_matricula_faixa_etaria, s.idade_ideal, s.exigir_inep';

        if (is_numeric($ref_cod_curso)) {
            if (class_exists('clsPmieducarCurso')) {
                $tmp_obj = new clsPmieducarCurso($ref_cod_curso);
                $curso = $tmp_obj->detalhe();
                if (false != $curso) {
                    $this->ref_cod_curso = $ref_cod_curso;
                }
            } else {
                if ($db->CampoUnico("SELECT 1 FROM pmieducar.curso WHERE cod_curso = '{$ref_cod_curso}'")) {
                    $this->ref_cod_curso = $ref_cod_curso;
                }
            }
        }

        if (is_numeric($ref_usuario_exc)) {
            if (class_exists('clsPmieducarUsuario')) {
                $tmp_obj = new clsPmieducarUsuario($ref_usuario_exc);
                if (method_exists($tmp_obj, 'existe')) {
                    if ($tmp_obj->existe()) {
                        $this->ref_usuario_exc = $ref_usuario_exc;
                    }
                } elseif (method_exists($tmp_obj, 'detalhe')) {
                    if ($tmp_obj->detalhe()) {
                        $this->ref_usuario_exc = $ref_usuario_exc;
                    }
                }
            } else {
                if ($db->CampoUnico("SELECT 1 FROM pmieducar.usuario WHERE cod_usuario = '{$ref_usuario_exc}'")) {
                    $this->ref_usuario_exc = $ref_usuario_exc;
                }
            }
        }

        if (is_numeric($ref_usuario_cad)) {
            if (class_exists('clsPmieducarUsuario')) {
                $tmp_obj = new clsPmieducarUsuario($ref_usuario_cad);
                if (method_exists($tmp_obj, 'existe')) {
                    if ($tmp_obj->existe()) {
                        $this->ref_usuario_cad = $ref_usuario_cad;
                    }
                } elseif (method_exists($tmp_obj, 'detalhe')) {
                    if ($tmp_obj->detalhe()) {
                        $this->ref_usuario_cad = $ref_usuario_cad;
                    }
                }
            } else {
                if ($db->CampoUnico("SELECT 1 FROM pmieducar.usuario WHERE cod_usuario = '{$ref_usuario_cad}'")) {
                    $this->ref_usuario_cad = $ref_usuario_cad;
                }
            }
        }

        // Atribuibui a identificação de regra de avaliação
        if (!is_null($regra_avaliacao_id) && is_numeric($regra_avaliacao_id)) {
            $mapper = new RegraAvaliacao_Model_RegraDataMapper();

            if (isset($curso)) {
                $regras = $mapper->findAll(
                    [],
                    ['id' => $regra_avaliacao_id, 'instituicao' => $curso['ref_cod_instituicao']]
                );

                if (1 == count($regras)) {
                    $regra = $regras[0];
                }
            } else {
                $regra = $mapper->find($regra_avaliacao_id);
            }

            // Verificação fraca pois deixa ser uma regra de outra instituição
            if (isset($regra)) {
                $this->regra_avaliacao_id = $regra->id;
            }
        }

        if (!is_null($regra_avaliacao_diferenciada_id) && is_numeric($regra_avaliacao_diferenciada_id)) {
            $mapper = new RegraAvaliacao_Model_RegraDataMapper();

            if (isset($curso)) {
                $regras = $mapper->findAll(
                    [],
                    ['id' => $regra_avaliacao_diferenciada_id, 'instituicao' => $curso['ref_cod_instituicao']]
                );

                if (1 == count($regras)) {
                    $regra = $regras[0];
                }
            } else {
                $regra = $mapper->find($regra_avaliacao_diferenciada_id);
            }

            // Verificação fraca pois deixa ser uma regra de outra instituição
            if (isset($regra)) {
                $this->regra_avaliacao_diferenciada_id = $regra->id;
            }
        }

        if (is_numeric($cod_serie)) {
            $this->cod_serie = $cod_serie;
        }

        if (is_string($nm_serie)) {
            $this->nm_serie = $nm_serie;
        }

        if (is_numeric($etapa_curso)) {
            $this->etapa_curso = $etapa_curso;
        }

        if (is_numeric($concluinte)) {
            $this->concluinte = $concluinte;
        }

        if (is_numeric($carga_horaria)) {
            $this->carga_horaria = $carga_horaria;
        }

        if (is_string($data_cadastro)) {
            $this->data_cadastro = $data_cadastro;
        }

        if (is_string($data_exclusao)) {
            $this->data_exclusao = $data_exclusao;
        }

        if (is_numeric($ativo)) {
            $this->ativo = $ativo;
        }

        if (is_numeric($idade_inicial)) {
            $this->idade_inicial = $idade_inicial;
        }

        if (is_numeric($idade_final)) {
            $this->idade_final = $idade_final;
        }

        if (dbBool($alerta_faixa_etaria)) {
            $this->alerta_faixa_etaria = $alerta_faixa_etaria;
        }

        if (dbBool($bloquear_matricula_faixa_etaria)) {
            $this->bloquear_matricula_faixa_etaria = $bloquear_matricula_faixa_etaria;
        }

        if (is_numeric($idade_ideal)) {
            $this->idade_ideal = $idade_ideal;
        }

        if (dbBool($exigir_inep)) {
            $this->exigir_inep = $exigir_inep;
        }

        $this->observacao_historico = $observacao_historico;
        $this->dias_letivos = $dias_letivos;
    }

    /**
     * Cria um novo registro.
     *
     * @return bool
     */
    public function cadastra()
    {
        if (
            is_numeric($this->ref_usuario_cad) && is_numeric($this->ref_cod_curso) &&
            is_string($this->nm_serie) && is_numeric($this->etapa_curso) &&
            is_numeric($this->concluinte) && is_numeric($this->carga_horaria) &&
            is_numeric($this->dias_letivos)
        ) {
            $db = new clsBanco();

            $campos = '';
            $valores = '';
            $gruda = '';

            if (is_numeric($this->ref_usuario_cad)) {
                $campos .= "{$gruda}ref_usuario_cad";
                $valores .= "{$gruda}'{$this->ref_usuario_cad}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_cod_curso)) {
                $campos .= "{$gruda}ref_cod_curso";
                $valores .= "{$gruda}'{$this->ref_cod_curso}'";
                $gruda = ', ';
            }

            if (is_string($this->nm_serie)) {
                $campos .= "{$gruda}nm_serie";
                $valores .= "{$gruda}'{$this->nm_serie}'";
                $gruda = ', ';
            }

            if (is_numeric($this->etapa_curso)) {
                $campos .= "{$gruda}etapa_curso";
                $valores .= "{$gruda}'{$this->etapa_curso}'";
                $gruda = ', ';
            }

            if (is_numeric($this->concluinte)) {
                $campos .= "{$gruda}concluinte";
                $valores .= "{$gruda}'{$this->concluinte}'";
                $gruda = ', ';
            }

            if (is_numeric($this->carga_horaria)) {
                $campos .= "{$gruda}carga_horaria";
                $valores .= "{$gruda}'{$this->carga_horaria}'";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_inicial)) {
                $campos .= "{$gruda}idade_inicial";
                $valores .= "{$gruda}'{$this->idade_inicial}'";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_final)) {
                $campos .= "{$gruda}idade_final";
                $valores .= "{$gruda}'{$this->idade_final}'";
                $gruda = ', ';
            }

            if (is_numeric($this->regra_avaliacao_id)) {
                $campos .= "{$gruda}regra_avaliacao_id";
                $valores .= "{$gruda}'{$this->regra_avaliacao_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->regra_avaliacao_diferenciada_id)) {
                $campos .= "{$gruda}regra_avaliacao_diferenciada_id";
                $valores .= "{$gruda}'{$this->regra_avaliacao_diferenciada_id}'";
                $gruda = ', ';
            }

            $campos .= "{$gruda}data_cadastro";
            $valores .= "{$gruda}NOW()";
            $gruda = ', ';

            $campos .= "{$gruda}ativo";
            $valores .= "{$gruda}'1'";
            $gruda = ', ';

            if (is_string($this->observacao_historico)) {
                $campos .= "{$gruda}observacao_historico";
                $valores .= "{$gruda}'{$this->observacao_historico}'";
                $gruda = ', ';
            }

            if (is_numeric($this->dias_letivos)) {
                $campos .= "{$gruda}dias_letivos";
                $valores .= "{$gruda}'{$this->dias_letivos}'";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_ideal)) {
                $campos .= "{$gruda}idade_ideal";
                $valores .= "{$gruda}'{$this->idade_ideal}'";
                $gruda = ', ';
            }

            if (dbBool($this->alerta_faixa_etaria)) {
                $campos .= "{$gruda}alerta_faixa_etaria";
                $valores .= "{$gruda} true ";
                $gruda = ', ';
            } else {
                $campos .= "{$gruda}alerta_faixa_etaria";
                $valores .= "{$gruda} false ";
                $gruda = ', ';
            }

            if (dbBool($this->bloquear_matricula_faixa_etaria)) {
                $campos .= "{$gruda}bloquear_matricula_faixa_etaria";
                $valores .= "{$gruda} true ";
                $gruda = ', ';
            } else {
                $campos .= "{$gruda}bloquear_matricula_faixa_etaria";
                $valores .= "{$gruda} false ";
                $gruda = ', ';
            }

            if (dbBool($this->exigir_inep)) {
                $campos .= "{$gruda}exigir_inep";
                $valores .= "{$gruda} true ";
                $gruda = ', ';
            } else {
                $campos .= "{$gruda}exigir_inep";
                $valores .= "{$gruda} false ";
                $gruda = ', ';
            }

            $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");

            return $db->InsertId("{$this->_tabela}_cod_serie_seq");
        }

        return false;
    }

    /**
     * Edita os dados de um registro.
     *
     * @return bool
     */
    public function edita()
    {
        if (is_numeric($this->cod_serie) && is_numeric($this->ref_usuario_exc)) {
            $db = new clsBanco();
            $set = '';

            if (is_numeric($this->ref_usuario_exc)) {
                $set .= "{$gruda}ref_usuario_exc = '{$this->ref_usuario_exc}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_usuario_cad)) {
                $set .= "{$gruda}ref_usuario_cad = '{$this->ref_usuario_cad}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ref_cod_curso)) {
                $set .= "{$gruda}ref_cod_curso = '{$this->ref_cod_curso}'";
                $gruda = ', ';
            }

            if (is_string($this->nm_serie)) {
                $set .= "{$gruda}nm_serie = '{$this->nm_serie}'";
                $gruda = ', ';
            }

            if (is_numeric($this->etapa_curso)) {
                $set .= "{$gruda}etapa_curso = '{$this->etapa_curso}'";
                $gruda = ', ';
            }

            if (is_numeric($this->concluinte)) {
                $set .= "{$gruda}concluinte = '{$this->concluinte}'";
                $gruda = ', ';
            }

            if (is_numeric($this->carga_horaria)) {
                $set .= "{$gruda}carga_horaria = '{$this->carga_horaria}'";
                $gruda = ', ';
            }

            if (is_string($this->data_cadastro)) {
                $set .= "{$gruda}data_cadastro = '{$this->data_cadastro}'";
                $gruda = ', ';
            }

            $set .= "{$gruda}data_exclusao = NOW()";
            $gruda = ', ';

            if (is_numeric($this->ativo)) {
                $set .= "{$gruda}ativo = '{$this->ativo}'";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_inicial)) {
                $set .= "{$gruda}idade_inicial = '{$this->idade_inicial}'";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}idade_inicial = NULL";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_final)) {
                $set .= "{$gruda}idade_final = '{$this->idade_final}'";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}idade_final = NULL";
                $gruda = ', ';
            }

            if (is_numeric($this->regra_avaliacao_id)) {
                $set .= "{$gruda}regra_avaliacao_id = '{$this->regra_avaliacao_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->regra_avaliacao_diferenciada_id)) {
                $set .= "{$gruda}regra_avaliacao_diferenciada_id = '{$this->regra_avaliacao_diferenciada_id}' ";
            } else {
                $set .= "{$gruda}regra_avaliacao_diferenciada_id = NULL ";
            }

            $gruda = ', ';

            if (is_string($this->observacao_historico)) {
                $set .= "{$gruda}observacao_historico = '{$this->observacao_historico}'";
                $gruda = ', ';
            }

            if (is_numeric($this->dias_letivos)) {
                $set .= "{$gruda}dias_letivos = '{$this->dias_letivos}'";
                $gruda = ', ';
            }

            if (is_numeric($this->idade_ideal)) {
                $set .= "{$gruda}idade_ideal = '{$this->idade_ideal}'";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}idade_ideal = NULL";
                $gruda = ', ';
            }

            if (dbBool($this->alerta_faixa_etaria)) {
                $set .= "{$gruda}alerta_faixa_etaria = true ";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}alerta_faixa_etaria = false ";
                $gruda = ', ';
            }

            if (dbBool($this->bloquear_matricula_faixa_etaria)) {
                $set .= "{$gruda}bloquear_matricula_faixa_etaria = true ";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}bloquear_matricula_faixa_etaria = false ";
                $gruda = ', ';
            }

            if (dbBool($this->exigir_inep)) {
                $set .= "{$gruda}exigir_inep = true ";
                $gruda = ', ';
            } else {
                $set .= "{$gruda}exigir_inep = false ";
                $gruda = ', ';
            }

            if ($set) {
                $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE cod_serie = '{$this->cod_serie}'");

                return true;
            }
        }

        return false;
    }

    /**
     * Retorna uma lista de registros filtrados de acordo com os parâmetros.
     *
     * @return array
     */
    public function lista(
        $int_cod_serie = null,
        $int_ref_usuario_exc = null,
        $int_ref_usuario_cad = null,
        $int_ref_cod_curso = null,
        $str_nm_serie = null,
        $int_etapa_curso = null,
        $int_concluinte = null,
        $int_carga_horaria = null,
        $date_data_cadastro_ini = null,
        $date_data_cadastro_fim = null,
        $date_data_exclusao_ini = null,
        $date_data_exclusao_fim = null,
        $int_ativo = null,
        $int_ref_cod_instituicao = null,
        $int_idade_inicial = null,
        $int_idade_final = null,
        $int_ref_cod_escola = null,
        $regra_avaliacao_id = null,
        $int_idade_ideal = null
    ) {
        $sql = "SELECT {$this->_campos_lista}, c.ref_cod_instituicao FROM {$this->_tabela} s, {$this->_schema}curso c";

        $whereAnd = ' AND ';
        $filtros = ' WHERE s.ref_cod_curso = c.cod_curso';

        if (is_numeric($int_cod_serie)) {
            $filtros .= "{$whereAnd} s.cod_serie = '{$int_cod_serie}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_usuario_exc)) {
            $filtros .= "{$whereAnd} s.ref_usuario_exc = '{$int_ref_usuario_exc}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_usuario_cad)) {
            $filtros .= "{$whereAnd} s.ref_usuario_cad = '{$int_ref_usuario_cad}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_curso)) {
            $filtros .= "{$whereAnd} s.ref_cod_curso = '{$int_ref_cod_curso}'";
            $whereAnd = ' AND ';
        }

        if (is_string($str_nm_serie)) {
            $filtros .= "{$whereAnd} translate(upper(s.nm_serie),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN') LIKE translate(upper('%{$str_nm_serie}%'),'ÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ','AAAAAAEEEEIIIIOOOOOUUUUCYN')";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_etapa_curso)) {
            $filtros .= "{$whereAnd} s.etapa_curso = '{$int_etapa_curso}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_concluinte)) {
            $filtros .= "{$whereAnd} s.concluinte = '{$int_concluinte}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_carga_horaria)) {
            $filtros .= "{$whereAnd} s.carga_horaria = '{$int_carga_horaria}'";
            $whereAnd = ' AND ';
        }

        if (is_string($date_data_cadastro_ini)) {
            $filtros .= "{$whereAnd} s.data_cadastro >= '{$date_data_cadastro_ini}'";
            $whereAnd = ' AND ';
        }

        if (is_string($date_data_cadastro_fim)) {
            $filtros .= "{$whereAnd} s.data_cadastro <= '{$date_data_cadastro_fim}'";
            $whereAnd = ' AND ';
        }

        if (is_string($date_data_exclusao_ini)) {
            $filtros .= "{$whereAnd} s.data_exclusao >= '{$date_data_exclusao_ini}'";
            $whereAnd = ' AND ';
        }

        if (is_string($date_data_exclusao_fim)) {
            $filtros .= "{$whereAnd} s.data_exclusao <= '{$date_data_exclusao_fim}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($regra_avaliacao_id)) {
            $filtros .= "{$whereAnd} s.regra_avaliacao_id = '{$regra_avaliacao_id}'";
            $whereAnd = ' AND ';
        }

        if (is_null($int_ativo) || $int_ativo) {
            $filtros .= "{$whereAnd} s.ativo = '1'";
            $whereAnd = ' AND ';
        } else {
            $filtros .= "{$whereAnd} s.ativo = '0'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_instituicao)) {
            $filtros .= "{$whereAnd} c.ref_cod_instituicao = '$int_ref_cod_instituicao'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_idade_inicial)) {
            $filtros .= "{$whereAnd} idade_inicial = '{$int_idade_inicial}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_idade_ideal)) {
            $filtros .= "{$whereAnd} idade_ideal = '{$int_idade_ideal}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_idade_final)) {
            $filtros .= "{$whereAnd} idade_final= '{$int_idade_final}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_escola)) {
            $filtros .= "{$whereAnd} EXISTS (SELECT 1
                                         FROM pmieducar.escola_serie es
                                        WHERE s.cod_serie = es.ref_cod_serie
                                          AND es.ativo = 1
                                          AND es.ref_cod_escola = '{$int_ref_cod_escola}') ";
            $whereAnd = ' AND ';
        }

        $db = new clsBanco();
        $countCampos = count(explode(',', $this->_campos_lista));
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} s, "
            . "{$this->_schema}curso c {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();

                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    public function listaSeriesComComponentesVinculados(
        $int_cod_serie = null,
        $int_ref_cod_curso = null,
        $int_ref_cod_instituicao = null,
        $int_ativo = null
    ) {
        $sql = "SELECT {$this->_campos_lista},
            c.ref_cod_instituicao FROM {$this->_tabela} s,
            {$this->_schema}curso c";

        $whereAnd = ' AND ';
        $filtros = ' WHERE s.ref_cod_curso = c.cod_curso';

        if (is_numeric($int_cod_serie)) {
            $filtros .= "{$whereAnd} s.cod_serie = '{$int_cod_serie}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_curso)) {
            $filtros .= "{$whereAnd} s.ref_cod_curso = '{$int_ref_cod_curso}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($int_ref_cod_instituicao)) {
            $filtros .= "{$whereAnd} c.ref_cod_instituicao = '$int_ref_cod_instituicao'";
            $whereAnd = ' AND ';
        }

        if (is_null($int_ativo) || $int_ativo) {
            $filtros .= "{$whereAnd} s.ativo = '1'";
            $whereAnd = ' AND ';
        }

        $filtros .= "{$whereAnd} s.cod_serie IN (SELECT DISTINCT ano_escolar_id
            FROM modules.componente_curricular_ano_escolar)";

        $whereAnd = ' AND ';

        $db = new clsBanco();
        $countCampos = count(explode(',', $this->_campos_lista));
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} s, "
            . "{$this->_schema}curso c {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();

                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro.
     *
     * @return array
     */
    public function detalhe()
    {
        if (is_numeric($this->cod_serie) && is_numeric($this->ref_cod_curso)) {
            $db = new clsBanco();
            $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} s WHERE s.cod_serie = '{$this->cod_serie}' AND s.ref_cod_curso = '{$this->ref_cod_curso}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        } elseif (is_numeric($this->cod_serie)) {
            $db = new clsBanco();
            $db->Consulta("SELECT {$this->_todos_campos} FROM {$this->_tabela} s WHERE s.cod_serie = '{$this->cod_serie}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro ou FALSE caso não exista.
     *
     * @return array|bool
     */
    public function existe()
    {
        if (is_numeric($this->cod_serie)) {
            $db = new clsBanco();
            $db->Consulta("SELECT 1 FROM {$this->_tabela} WHERE cod_serie = '{$this->cod_serie}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Exclui um registro.
     *
     * @return bool
     */
    public function excluir()
    {
        if (is_numeric($this->cod_serie) && is_numeric($this->ref_usuario_exc)) {
            $this->ativo = 0;

            return $this->edita();
        }

        return false;
    }

    /**
     * Define quais campos da tabela serão selecionados no método Lista().
     */
    public function setCamposLista($str_campos)
    {
        $this->_campos_lista = $str_campos;
    }

    /**
     * Define que o método Lista() deverpa retornar todos os campos da tabela.
     */
    public function resetCamposLista()
    {
        $this->_campos_lista = $this->_todos_campos;
    }

    /**
     * Define limites de retorno para o método Lista().
     */
    public function setLimite($intLimiteQtd, $intLimiteOffset = null)
    {
        $this->_limite_quantidade = $intLimiteQtd;
        $this->_limite_offset = $intLimiteOffset;
    }

    /**
     * Retorna a string com o trecho da query responsável pelo limite de
     * registros retornados/afetados.
     *
     * @return string
     */
    public function getLimite()
    {
        if (is_numeric($this->_limite_quantidade)) {
            $retorno = " LIMIT {$this->_limite_quantidade}";
            if (is_numeric($this->_limite_offset)) {
                $retorno .= " OFFSET {$this->_limite_offset} ";
            }

            return $retorno;
        }

        return '';
    }

    /**
     * Define o campo para ser utilizado como ordenação no método Lista().
     */
    public function setOrderby($strNomeCampo)
    {
        if (is_string($strNomeCampo) && $strNomeCampo) {
            $this->_campo_order_by = $strNomeCampo;
        }
    }

    /**
     * Retorna a string com o trecho da query responsável pela Ordenação dos
     * registros.
     *
     * @return string
     */
    public function getOrderby()
    {
        if (is_string($this->_campo_order_by)) {
            return " ORDER BY {$this->_campo_order_by} ";
        }

        return '';
    }

    /**
     * Seleciona as série que não estejam cadastradas na escola.
     *
     * @param int $ref_cod_curso
     * @param int $ref_cod_escola
     *
     * @return array
     */
    public function getNotEscolaSerie($ref_cod_curso, $ref_cod_escola)
    {
        $db = new clsBanco();
        $sql = "SELECT *
            FROM
              pmieducar.serie s
            WHERE s.ref_cod_curso = '{$ref_cod_curso}'
            AND s.cod_serie NOT IN
            (
              SELECT es.ref_cod_serie
              FROM pmieducar.escola_serie es
              WHERE es.ref_cod_escola = '{$ref_cod_escola}'
            )";

        $db->Consulta($sql);

        while ($db->ProximoRegistro()) {
            $tupla = $db->Tupla();
            $resultado[] = $tupla;
        }

        return $resultado;
    }

    /**
     * Verifica se a data de nascimento enviada por parâmetro está dentro do período de corte etário pré-definido.
     *
     * @param int $dataNascimento
     *
     * @return boolean
     */
    public function verificaPeriodoCorteEtarioDataNascimento($dataNascimento, $ano)
    {
        $detSerie = $this->detalhe();
        $idadeInicial = $detSerie['idade_inicial'];
        $idadeFinal = $detSerie['idade_final'];

        $instituicaoId = $this->getInstituicaoByCurso($detSerie['ref_cod_curso']);
        $objInstituicao = new clsPmieducarInstituicao($instituicaoId);
        $detInstituicao = $objInstituicao->detalhe();
        $dataBaseMatricula = $detInstituicao['data_base_matricula'];

        //Caso não tenha data base na matricula, não verifica se está dentro do periodo
        if (!is_string($dataBaseMatricula)) {
            return true;
        }

        $anoLimite = $ano;
        $mesLimite = $dataBaseMatricula[1];
        $diaLimite = $dataBaseMatricula[2];

        $dataLimite = $anoLimite . '-' . $mesLimite . '-' . $diaLimite;

        $dataNascimento = new DateTime($dataNascimento);
        $dataLimite = new DateTime($dataLimite);

        $diferencaDatas = $dataNascimento->diff($dataLimite);

        $idadeNaData = $diferencaDatas->y;
        $idadesPermitidas = range($idadeInicial, $idadeFinal);

        $idadeCompativel = false;

        foreach ($idadesPermitidas as $idade) {
            if ($idade == $idadeNaData) {
                $idadeCompativel = true;
            }
        }

        return $idadeCompativel;
    }

    public function getInstituicaoByCurso($codCurso)
    {
        $objCurso = new clsPmieducarCurso($codCurso);
        $detCurso = $objCurso->detalhe();

        return $detCurso['ref_cod_instituicao'];
    }
}
