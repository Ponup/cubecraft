<?php

use glm\mat4;

use \Ponup\ddd\Shader;
use Ponup\ddd\Shader\Program;
use Ponup\ddd\Shader\Vertex;
use Ponup\ddd\Shader\Fragment;
use glm\vec3;

class Skybox extends Entity
{
    public function __construct()
    {
		parent::__construct();

        $this->skyboxVertices = $skyboxVertices = [
            // positions          
            -1.0,  1.0, -1.0,
            -1.0, -1.0, -1.0,
            1.0, -1.0, -1.0,
            1.0, -1.0, -1.0,
            1.0,  1.0, -1.0,
            -1.0,  1.0, -1.0,

            -1.0, -1.0,  1.0,
            -1.0, -1.0, -1.0,
            -1.0,  1.0, -1.0,
            -1.0,  1.0, -1.0,
            -1.0,  1.0,  1.0,
            -1.0, -1.0,  1.0,

            1.0, -1.0, -1.0,
            1.0, -1.0,  1.0,
            1.0,  1.0,  1.0,
            1.0,  1.0,  1.0,
            1.0,  1.0, -1.0,
            1.0, -1.0, -1.0,

            -1.0, -1.0,  1.0,
            -1.0,  1.0,  1.0,
            1.0,  1.0,  1.0,
            1.0,  1.0,  1.0,
            1.0, -1.0,  1.0,
            -1.0, -1.0,  1.0,

            -1.0,  1.0, -1.0,
            1.0,  1.0, -1.0,
            1.0,  1.0,  1.0,
            1.0,  1.0,  1.0,
            -1.0,  1.0,  1.0,
            -1.0,  1.0, -1.0,

            -1.0, -1.0, -1.0,
            -1.0, -1.0,  1.0,
            1.0, -1.0, -1.0,
            1.0, -1.0, -1.0,
            -1.0, -1.0,  1.0,
            1.0, -1.0,  1.0
        ];


        $this->shaderProgram->add(new Vertex("shaders/skybox.vert"));
        $this->shaderProgram->add(new Fragment("shaders/skybox.frag"));
        $this->shaderProgram->compile();
        $this->shaderProgram->link();

        $this->skyboxVertices = array_map(function ($el) {
            return floatval($el) * 5000;
        }, $skyboxVertices);
        glGenVertexArrays(1, $skyboxVAO);
        $this->vao = $skyboxVAO[0];
        glBindVertexArray($this->vao);

        glGenBuffers(1, $skyboxVBO);
        glBindBuffer(GL_ARRAY_BUFFER, $skyboxVBO[0]);
        glBufferData(GL_ARRAY_BUFFER, sizeof($this->skyboxVertices) * 4, $this->skyboxVertices, GL_STATIC_DRAW);
        glEnableVertexAttribArray(0);
        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, 0, 0);
        //glBindVertexArray(0);

        $textureIds = [];
        glGenTextures(1, $textureIds);
        $this->texture = $textureIds[0];
        glBindTexture(GL_TEXTURE_CUBE_MAP, $this->texture);

        $faces = [
            'right.jpg',
            'left.jpg',
            'top.jpg',
            'bottom.jpg',
            'front.jpg',
            'back.jpg'
        ];

        $imgLoader = new Ponup\GlLoaders\ImageLoader;
        for ($i = 0; $i < count($faces); $i++) {
            $data = $imgLoader->load('textures/skybox/' . $faces[$i], $width, $height);
            glTexImage2D(GL_TEXTURE_CUBE_MAP_POSITIVE_X + $i, 0, GL_RGBA, $width, $height, 0, GL_RGBA, GL_UNSIGNED_BYTE, $data);
            unset($data);
        }

        glTexParameteri(GL_TEXTURE_CUBE_MAP, GL_TEXTURE_MAG_FILTER, GL_LINEAR);
        glTexParameteri(GL_TEXTURE_CUBE_MAP, GL_TEXTURE_MIN_FILTER, GL_LINEAR);
        glTexParameteri(GL_TEXTURE_CUBE_MAP, GL_TEXTURE_WRAP_S, GL_CLAMP_TO_EDGE);
        glTexParameteri(GL_TEXTURE_CUBE_MAP, GL_TEXTURE_WRAP_T, GL_CLAMP_TO_EDGE);
        glTexParameteri(GL_TEXTURE_CUBE_MAP, GL_TEXTURE_WRAP_R, GL_CLAMP_TO_EDGE);
        glBindTexture(GL_TEXTURE_CUBE_MAP, 0);

        glBindVertexArray(0);
    }

    public function render(mat4 $view, mat4 $projection)
    {
        glDisable(GL_DEPTH_TEST);
        //glDepthFunc(GL_LEQUAL);
        $this->shaderProgram->use();
        $model = new mat4;
        $model = \glm\translate($model, new vec3(0, 0, 0));

        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "projection");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($projection));
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "view");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($view));

        glBindVertexArray($this->vao);
        glActiveTexture(GL_TEXTURE0);
        glBindTexture(GL_TEXTURE_CUBE_MAP, $this->texture);
        glDrawArrays(GL_TRIANGLES, 0, count($this->skyboxVertices));
        glBindVertexArray(0);
        //glDepthFunc(GL_LESS);
        glEnable(GL_DEPTH_TEST);
    }
}
