<?php

declare(strict_types=1);

use Mammoth\Graphic\Shader\Fragment;
use Mammoth\Graphic\Shader\Vertex;
use Mammoth\Math\Matrix;
use Mammoth\Math\Transform;
use Mammoth\Math\Vector;

class SimpleCube extends Entity
{
    public function __construct($texture)
    {
        parent::__construct();

        $texture = $texture == 1 ? 'ground.frag' : 'ground.1.frag';
        $this->position = new Vector(0, 0, 0);
        $this->shaderProgram->add(new Vertex("shaders/cubemap.vert"));
        $this->shaderProgram->add(new Fragment("shaders/" . $texture));
        $this->shaderProgram->compile();
        $this->shaderProgram->link();

        $vertices = [
            // positions          // texture Coords
            -0.5, -0.5, -0.5,
            0.5, -0.5, -0.5,
            0.5,  0.5, -0.5,
            0.5,  0.5, -0.5,
            -0.5,  0.5, -0.5,
            -0.5, -0.5, -0.5,

            -0.5, -0.5,  0.5,
            0.5, -0.5,  0.5,
            0.5,  0.5,  0.5,
            0.5,  0.5,  0.5,
            -0.5,  0.5,  0.5,
            -0.5, -0.5,  0.5,

            -0.5,  0.5,  0.5,
            -0.5,  0.5, -0.5,
            -0.5, -0.5, -0.5,
            -0.5, -0.5, -0.5,
            -0.5, -0.5,  0.5,
            -0.5,  0.5,  0.5,

            0.5,  0.5,  0.5,
            0.5,  0.5, -0.5,
            0.5, -0.5, -0.5,
            0.5, -0.5, -0.5,
            0.5, -0.5,  0.5,
            0.5,  0.5,  0.5,

            -0.5, -0.5, -0.5,
            0.5, -0.5, -0.5,
            0.5, -0.5,  0.5,
            0.5, -0.5,  0.5,
            -0.5, -0.5,  0.5,
            -0.5, -0.5, -0.5,

            -0.5,  0.5, -0.5,
            0.5,  0.5, -0.5,
            0.5,  0.5,  0.5,
            0.5,  0.5,  0.5,
            -0.5,  0.5,  0.5,
            -0.5,  0.5, -0.5,
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
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 0, 0);

        $this->shaderProgram->Use();
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "texture1");
        glUniform1i($mvpLoc, 0);

        glBindVertexArray(0);
    }

    public function render(Matrix $view, Matrix $projection): void
    {
        $model = new Matrix();
        $model = Transform::translate($model, $this->position);
        $this->shaderProgram->Use();
        
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "model");
        glUniformMatrix4fv($mvpLoc, 1, false, $model->toRowVector());
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "view");
        glUniformMatrix4fv($mvpLoc, 1, false, $view->toRowVector());
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "projection");
        glUniformMatrix4fv($mvpLoc, 1, false, $projection->toRowVector());

        glBindVertexArray($this->vertexArrayIds[0]);
        glDrawArrays(GL_TRIANGLES, 0, 36);
        glBindVertexArray(0);
    }
}
