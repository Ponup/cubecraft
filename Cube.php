<?php

use glm\vec2;
use glm\vec3;
use glm\mat4;
use \Ponup\ddd\Shader;

function loadTexture($path)
{
    $textureID = [];
    glGenTextures(1, $textureID);

    $imgLoader = new Ponup\GlLoaders\ImageLoader;
    $data = $imgLoader->load($path, $width, $height);
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

class Cube
{
    protected $shaderProgram;

    public function setPosition($position)
    {
        $this->position = $position;
    }
    public function __construct($texture)
    {
        if($texture == 1) {
            $texture = 'marble.jpg';
            $loc = 'texture1';
            $shader = 'cubemap.frag';
        }
        else {
            $texture = 'grass.jpg';
            $loc = 'texture2';
            $shader = 'cubemap.1.frag';
        }

        $this->position = new vec3(0, 0, 0);
        $this->shaderProgram = new Shader\Program;
        $this->shaderProgram->add(new Shader\Vertex("shaders/cubemap.vert"));
        $this->shaderProgram->add(new Shader\Fragment("shaders/" . $shader));
        $this->shaderProgram->compile();
        $this->shaderProgram->link();

        $vertices = [
            // positions          // texture Coords
            -0.5, -0.5, -0.5,  0.0, 0.0,
            0.5, -0.5, -0.5,  1.0, 0.0,
            0.5,  0.5, -0.5,  1.0, 1.0,
            0.5,  0.5, -0.5,  1.0, 1.0,
            -0.5,  0.5, -0.5,  0.0, 1.0,
            -0.5, -0.5, -0.5,  0.0, 0.0,

            -0.5, -0.5,  0.5,  0.0, 0.0,
            0.5, -0.5,  0.5,  1.0, 0.0,
            0.5,  0.5,  0.5,  1.0, 1.0,
            0.5,  0.5,  0.5,  1.0, 1.0,
            -0.5,  0.5,  0.5,  0.0, 1.0,
            -0.5, -0.5,  0.5,  0.0, 0.0,

            -0.5,  0.5,  0.5,  1.0, 0.0,
            -0.5,  0.5, -0.5,  1.0, 1.0,
            -0.5, -0.5, -0.5,  0.0, 1.0,
            -0.5, -0.5, -0.5,  0.0, 1.0,
            -0.5, -0.5,  0.5,  0.0, 0.0,
            -0.5,  0.5,  0.5,  1.0, 0.0,

            0.5,  0.5,  0.5,  1.0, 0.0,
            0.5,  0.5, -0.5,  1.0, 1.0,
            0.5, -0.5, -0.5,  0.0, 1.0,
            0.5, -0.5, -0.5,  0.0, 1.0,
            0.5, -0.5,  0.5,  0.0, 0.0,
            0.5,  0.5,  0.5,  1.0, 0.0,

            -0.5, -0.5, -0.5,  0.0, 1.0,
            0.5, -0.5, -0.5,  1.0, 1.0,
            0.5, -0.5,  0.5,  1.0, 0.0,
            0.5, -0.5,  0.5,  1.0, 0.0,
            -0.5, -0.5,  0.5,  0.0, 0.0,
            -0.5, -0.5, -0.5,  0.0, 1.0,

            -0.5,  0.5, -0.5,  0.0, 1.0,
            0.5,  0.5, -0.5,  1.0, 1.0,
            0.5,  0.5,  0.5,  1.0, 0.0,
            0.5,  0.5,  0.5,  1.0, 0.0,
            -0.5,  0.5,  0.5,  0.0, 0.0,
            -0.5,  0.5, -0.5,  0.0, 1.0
        ];
        $vertices = array_map(function ($el) {
            return (abs($el) == 0.5) ? floatval($el) : floatval($el);
        }, $vertices);
        $this->vertices = $vertices;

        $this->vertexArrayIds = [];
        glGenVertexArrays(1, $this->vertexArrayIds);
        glBindVertexArray($this->vertexArrayIds[0]);

        glGenBuffers(1, $this->vertexBufferIds);
        glBindBuffer(GL_ARRAY_BUFFER, $this->vertexBufferIds[0]);
        glBufferData(GL_ARRAY_BUFFER, count($vertices) * PHP_INT_SIZE, $vertices, GL_STATIC_DRAW);

        glEnableVertexAttribArray(0);
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 5 * 4, 0);
        glEnableVertexAttribArray(1);
        glVertexAttribPointer(1, 2, GL_FLOAT, GL_FALSE, 5 * 4, 3 * 4);


        $this->texture = $texture;
        $this->cubeTexture = loadTexture('textures/' . $texture);
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), $loc);
        $this->shaderProgram->Use();
        glUniform1i($mvpLoc, 0);

        glBindVertexArray(0);
    }
    
    public function render($view, $projection)
    {
        $model = new mat4;
        $model = \glm\translate($model, $this->position);
        glActiveTexture(GL_TEXTURE0);
        glBindTexture(GL_TEXTURE_2D, $this->cubeTexture);
        $this->shaderProgram->Use();
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "model");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($model));
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "view");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($view));
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "projection");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($projection));

        glBindVertexArray($this->vertexArrayIds[0]);
        glDrawArrays(GL_TRIANGLES, 0, 36);
        //glBindTexture(GL_TEXTURE_2D, 0);
        glBindVertexArray(0);
    }
}
