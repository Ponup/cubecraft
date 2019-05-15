<?php

use glm\vec2;
use glm\vec3;
use glm\mat4;
use \Ponup\ddd\Shader;

class Water
{
    public function __construct()
    {
        $this->time = 0;

        $this->position = new vec3(0, -1, 0);
        $this->shaderProgram = new Shader\Program;
        $this->shaderProgram->add(new Shader\Vertex("shaders/simple.vert"));
        $this->shaderProgram->add(new Shader\Fragment('shaders/water.frag'));
        $this->shaderProgram->compile();
        $this->shaderProgram->link();

        $vertices = [
            -1, 0, 1,
            1, 0, 1,
            1, 0, -1,
            1, 0, -1,
            -1, 0, -1,
            -1, 0, 1,
        ];
        $vertices = array_map(function ($el) {
            return floatval($el);
        }, $vertices);
        $this->vertices = $vertices;

        $vertexArrayIds = [];
        glGenVertexArrays(1, $vertexArrayIds);
        glBindVertexArray($vertexArrayIds[0]);

        glGenBuffers(1, $this->vertexBufferIds);
        glBindBuffer(GL_ARRAY_BUFFER, $this->vertexBufferIds[0]);
        glBufferData(GL_ARRAY_BUFFER, count($vertices) * 4, $vertices, GL_STATIC_DRAW);

        glBindVertexArray(0);
    }

    public function render($view, $projection)
    {
        $this->time += 0.01;

        glBindBuffer(GL_ARRAY_BUFFER, $this->vertexBufferIds[0]);
        glEnableVertexAttribArray(0);
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 0, 0);

        $model = new mat4;
        $model = \glm\translate($model, $this->position)->scale(10);
        $mvp = $model->multiply($view)->multiply($projection);
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "mvp");
        $waveColor = glGetUniformLocation($this->shaderProgram->getId(), "waveColor");
        $this->shaderProgram->Use();
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($mvp));
        glUniform3f($waveColor, 0, 0.0, min(((sin($this->time * 4) + 1) / 2),0.4));
        glDrawArrays(GL_TRIANGLES, 0, count($this->vertices));
        glBindBuffer(GL_ARRAY_BUFFER, 0);
    }
}
