<?php

abstract class Entity
{
	protected $position;

	protected $textureLoader;

	public function __construct()
	{
		$this->textureLoader = new TextureLoader;
	}

    public function setPosition(glm\vec3 $position)
    {
        $this->position = $position;
    }
}

