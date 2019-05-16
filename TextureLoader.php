<?php

class TextureLoader
{
	private $imageLoader;

	public function __construct()
	{
    	$this->imageLoader = new Ponup\GlLoaders\ImageLoader;
	}

	public function load(string $path): int
	{
    	$textureID = [];
    	glGenTextures(1, $textureID);

    	$data = $this->imageLoader->load($path, $width, $height);
    	glBindTexture(GL_TEXTURE_2D, $textureID[0]);
    	glTexImage2D(GL_TEXTURE_2D, 0, GL_RGB, $width, $height, 0, GL_RGBA, GL_UNSIGNED_BYTE, $data);
    	unset($data);
    	glGenerateMipmap(GL_TEXTURE_2D);

    	glTexParameteri(GL_TEXTURE_2D, GL_TEXTURE_WRAP_S, GL_REPEAT);
    	glTexParameteri(GL_TEXTURE_2D, GL_TEXTURE_WRAP_T, GL_REPEAT);
    	glTexParameteri(GL_TEXTURE_2D, GL_TEXTURE_MIN_FILTER, GL_LINEAR_MIPMAP_LINEAR);
    	glTexParameteri(GL_TEXTURE_2D, GL_TEXTURE_MAG_FILTER, GL_LINEAR);
    	glBindTexture(GL_TEXTURE_2D, 0);

    	return $textureID[0];
	}
}

