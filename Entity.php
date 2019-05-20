<?php

use \Ponup\ddd\Shader;

abstract class Entity
{
	/**
 	 * @var glm\vec3
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
        $this->shaderProgram = new Shader\Program;
	}

    public function setPosition(glm\vec3 $position)
    {
        $this->position = $position;
    }
}

