<?php

declare(strict_types=1);

use Mammoth\Graphic\Shader\Fragment;
use Mammoth\Graphic\Shader\Vertex;
use Mammoth\Math\Matrix;
use Mammoth\Math\Transform;
use Mammoth\Math\Vector;

class Water extends Entity
{
    public function __construct()
    {
        parent::__construct();

        $this->time = 0;

        $this->position = new Vector(0, -1, 0);
        $this->shaderProgram->add(new Vertex("shaders/cubemap.vert"));
        $this->shaderProgram->add(new Fragment('shaders/water.frag'));
        $this->shaderProgram->compile();
        $this->shaderProgram->link();

        $vertices = [
            -1, 0, 1,
            1, 0, 1,
            -1, 0, -1,
            -1, 0, -1,
            1, 0, -1,
            1, 0, 1
        ];
        $vertices = array_map(function ($el) {
            return floatval($el);
        }, $vertices);
        $this->vertices = $vertices;

        $this->vertexArrayIds = [];
        glGenVertexArrays(1, $this->vertexArrayIds);
        glBindVertexArray($this->vertexArrayIds[0]);

        glGenBuffers(1, $this->vertexBufferIds);
        glBindBuffer(GL_ARRAY_BUFFER, $this->vertexBufferIds[0]);
        glBufferData(GL_ARRAY_BUFFER, count($vertices) * 4, $vertices, GL_STATIC_DRAW);

        glEnableVertexAttribArray(0);
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 0, 0);

        glBindVertexArray(0);
    }

    public function render(Matrix $view, Matrix $projection): void
    {
        $this->time += 0.02;

        $model = new Matrix();
        $model = Transform::translate($model, $this->position);
        $mvp = $model->multiply($view)->multiply($projection);

        $this->shaderProgram->Use();

        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "model");
        glUniformMatrix4fv($mvpLoc, 1, false, $model->toRowVector());
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "view");
        glUniformMatrix4fv($mvpLoc, 1, false, $view->toRowVector());
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "projection");
        glUniformMatrix4fv($mvpLoc, 1, false, $projection->toRowVector());
        $waveColor = glGetUniformLocation($this->shaderProgram->getId(), "waveColor");
        glUniform3f($waveColor, 0, 0.0, min(((sin($this->time * 4) + 1) / 2), 0.4));

        glBindVertexArray($this->vertexArrayIds[0]);
        glDrawArrays(GL_TRIANGLES, 0, 18);
        glBindVertexArray(0);
    }
}
