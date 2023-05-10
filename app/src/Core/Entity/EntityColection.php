<?php

namespace Core\Entity;

use Core\Inferface\EntityInterface;

class EntityColection
{
    public $pagina;
    public $itens;

    public function __construct($dados = null)
    {
        if (!empty($dados)) {
            $this->pagina = $dados->page;
            $this->itens  = $dados->itens;
        }
    }

    public function paginaAtual()
    {
        return $this->pagina->current;
    }

    public function proximaPagina()
    {
        return $this->pagina->next;
    }

    public function proximaAnterior()
    {
        return $this->pagina->before;
    }

    public function totalPaginas()
    {
        return $this->pagina->total;
    }

    public function totalDeRegistros()
    {
        return $this->pagina->results;
    }

    public function getItem()
    {
        return current($this->itens);
    }

    public function getItens()
    {
        return $this->itens;
    }

    public function toApi()
    {
        return (object)[
            'page'  => $this->pagina,
            'itens' => $this->itensToArray()
        ];
    }

    public function itensToArray()
    {
        return array_reduce($this->itens, function ($result, EntityInterface $item) {
            $result[] = (object)$item->toApi();
            return $result;
        }, []);
    }
}