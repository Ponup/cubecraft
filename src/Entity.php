<?php

declare(strict_types=1);

use Mammoth\Graphic\Shader\Program;
use Mammoth\Math\Vector;

abstract class Entity
{
	/**
 	 * @var Vector
 	 */
	protected $position;

	/**
 	 * @var TextureLoader
 	 */
	protected $textureLoader;

	/**
 	 * @var Shader\Program
 	 */
	protected $shaderProgram;

	public function __construct()
	{
		$this->textureLoader = new TextureLoader;
        $this->shaderProgram = new Program;
	}

    public function setPosition(Vector $position): void
    {
        $this->position = $position;
    }
}

