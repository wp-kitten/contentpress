<?php

namespace App\Widgets;

abstract class AbstractWidgetBase
{
    /**
     * Holds the ID of the widget
     * @var string
     */
    private $id = '';

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var array
     */
    private $options = [];

    /**
     * Render the output of the widget
     * @return mixed
     */
    abstract function render();


    /**
     * WidgetBase constructor.
     * @param string $id
     * @param array $options
     */
    public function __construct( $id = '', $options = [] )
    {
        $this->setId( $id );
        $this->setOptions( $options );
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData( $data ): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId( string $id ): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions( array $options ): void
    {
        $this->options = $options;
    }
}
