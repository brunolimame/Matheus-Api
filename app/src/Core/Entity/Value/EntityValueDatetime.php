<?php


namespace Core\Entity\Value;

use Respect\Validation\Validator;

class EntityValueDatetime implements EntityValueObjectInterface
{
    const DATETIME_FORMAT = "Y-m-d H:i:s";
    const DATETIME_FORMAT_BR = "d/m/Y H:i:s";
    const DATE_FORMAT = "Y-m-d";
    const DATE_FORMAT_BR = "d/m/Y";
    private $valor;

    public function __construct($valor = null)
    {
        $this->set($valor);
    }

    public static function factory($valor = null)
    {
        return new self($valor);
    }

    public function set($valor, $formato = null)
    {
        $this->valor = $valor;
        if ($valor instanceof \DateTime) {
            $formato     = $this->validarFormato($formato);
            $this->value = $this->format($formato);
        }
        return $this;
    }

    public function setConvert($valor, $format)
    {
        $this->set($this->convert($valor, $format)->format(self::DATETIME_FORMAT));
        return $this;
    }

    public function setDateConvert($valor, $format)
    {
        $this->set($this->convert($valor, $format)->format(self::DATE_FORMAT));
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    /**
     * @return \DateTime|false
     */
    public function object()
    {

        return $this->convert($this->value());
    }

    public function format($formato = null): string
    {
        $formato = $this->validarFormato($formato);

        return $this->object()->format($formato);
    }

    public function formatDate()
    {
        return $this->format(self::DATE_FORMAT);
    }

    public function formatBr($formato = null): string
    {
        $formato = $this->validarFormatoBr($formato);
        return $this->object()->format($formato);
    }

    public function formatDateBr($formato = null)
    {
        return $this->format(self::DATE_FORMAT_BR);
    }

    protected function validarFormato($formato = null)
    {
        return (is_string($formato) && !empty($formato) && !is_null($formato)) ? $formato : self::DATETIME_FORMAT;
    }

    protected function validarFormatoBr($formato = null)
    {
        return (is_string($formato) && !empty($formato) && !is_null($formato)) ? $formato : self::DATETIME_FORMAT_BR;
    }

    public function extenso($curto = false)
    {
        $listaMeses = [
            'completo' => ['Janeiro', 'Fevereito', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            'curto'    => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
        ];
        $numeroMes  = (int)$this->object()->format('n') - 1;
        $nomeMes    = $curto ? $listaMeses['curto'][$numeroMes] : $listaMeses['completo'][$numeroMes];

        return sprintf('%s de %s de %s', $this->object()->format('d'), $nomeMes, $this->object()->format('Y'));
    }

    /**
     * @param string $valor
     * @param string|null $formato
     * @return \DateTime|false
     */
    public function convert($valor, $formato = null)
    {
        if (empty($valor)) {
            return null;
        }

        $formato = $this->validarFormato($formato);
        $data    = \DateTime::createFromFormat($formato, $valor);

        if (!Validator::dateTime()->validate($data)) {
            throw new \InvalidArgumentException(sprintf('<%s>[%s] não permite o valor <%s> no formato <%s>.', static::class, (new \ReflectionClass($this))->getShortName(), $valor, $formato));
        }
        return $data;
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}
