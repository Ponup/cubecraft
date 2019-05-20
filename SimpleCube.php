<?php

use glm\vec2;
use glm\vec3;
use glm\mat4;
use \Ponup\ddd\Shader;

class SimpleCube extends Entity
{
    public function __construct($texture)
    {
		parent::__construct();

        $texture = $texture == 1 ? 'ground.frag' : 'ground.1.frag';
        $this->position = new vec3(0, 0, 0);
        $this->shaderProgram->add(new Shader\Vertex("shaders/cubemap.vert"));
        $this->shaderProgram->add(new Shader\Fragment("shaders/" . $texture));
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

        //$this->cubeTexture = loadTexture('textures/' . $texture);
        $this->shaderProgram->Use();
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "texture1");
        glUniform1i($mvpLoc, 0);

        glBindVertexArray(0);
    }
    
    public function render($view, $projection)
    {
        $model = new mat4;
        $model = \glm\translate($model, $this->position);
        $this->shaderProgram->Use();
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "model");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($model));
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "view");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($view));
        $mvpLoc = glGetUniformLocation($this->shaderProgram->getId(), "projection");
        glUniformMatrix4fv($mvpLoc, 1, GL_FALSE, \glm\value_ptr($projection));

        glBindVertexArray($this->vertexArrayIds[0]);
        glDrawArrays(GL_TRIANGLES, 0, 36);
        glBindVertexArray(0);
    }
}
