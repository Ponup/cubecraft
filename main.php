<?php

declare(strict_types=1);

use Mammoth\Math\Angle;
use Mammoth\Math\Transform;
use Mammoth\Math\Vector;

ini_set('memory_limit', '2048M');

require 'vendor/autoload.php';

$yaw    = -90.0;    // Yaw is initialized to -90.0 degrees since a yaw of 0.0 results in a direction vector pointing to the right (due to how Eular angles work) so we initially rotate a bit to the left.
$pitch  =  0.0;
$lastMouseCoordinates = null;

$cameraPos   = new Vector(0.0, 0.0,  5.0);
$cameraFront = new Vector(0.0, 0.0, -1.0);
$cameraUp    = new Vector(0.0, 1.0,  0.0);

$keys = array_fill_keys(range('a', 'z'), false);

const WIDTH = 1024;
const HEIGHT = 800;

SDL_Init(SDL_INIT_VIDEO);

SDL_GL_SetAttribute(SDL_GL_CONTEXT_MAJOR_VERSION, 3);
SDL_GL_SetAttribute(SDL_GL_CONTEXT_MINOR_VERSION, 3);
SDL_GL_SetAttribute(SDL_GL_CONTEXT_PROFILE_MASK, SDL_GL_CONTEXT_PROFILE_CORE);

$window = SDL_CreateWindow("Cube Craft", SDL_WINDOWPOS_CENTERED, SDL_WINDOWPOS_CENTERED, WIDTH, HEIGHT, SDL_WINDOW_OPENGL | SDL_WINDOW_SHOWN);
$context = SDL_GL_CreateContext($window);

$cubeTexture = 1;
$firstCube = new Cube($cubeTexture);
$firstCube->setPosition(new Vector(0, 0, -10));
$cubes = [
    $firstCube,
];

$water = new Water;
$ground = new Ground;

glViewport(0, 0, WIDTH, HEIGHT);

glClearColor(0, 1, 1, 1.0);

$skybox = new Skybox;

$quit = false;
$event = new SDL_Event;
while (!$quit) {
    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

    $fov = 45.0;
    $projection = Transform::perspective($fov, (float) WIDTH / (float) HEIGHT, 0.1, 100.0);
    $view = Transform::lookAt($cameraPos, $cameraPos->add($cameraFront), $cameraUp);
    
    $skybox->render($view, $projection);
    //$ground->render($view, $projection);
    //$water->render($view, $projection);

    foreach ($cubes as $cube) {
        if (is_object($cube)) {
            $cube->render($view, $projection);
        }
    }

    while (SDL_PollEvent($event)) {
        switch ($event->type) {
            case SDL_QUIT:
                $quit = true;
                break;
            case SDL_MOUSEBUTTONDOWN:
                $cube = new SimpleCube($cubeTexture);
                $cubePos = $cameraPos->add($cameraFront->scale(5));
                $cubePos->x = floor($cubePos->x);
                $cubePos->y = floor($cubePos->y);
                $cubePos->z = floor($cubePos->z);
                $cube->setPosition($cubePos);
                $cubes[] = $cube;
                break;
            case SDL_MOUSEMOTION:
                $xpos = $event->motion->x;
                $ypos = $event->motion->y;

                if (null === $lastMouseCoordinates) {
                    $lastMouseCoordinates = new Vector($xpos - (WIDTH >> 1), $ypos + (HEIGHT >> 1));
                }

                $xoffset = $xpos - $lastMouseCoordinates->x;
                $yoffset = $lastMouseCoordinates->y - $ypos; // Reversed since y-coordinates go from bottom to left
                $lastMouseCoordinates = new Vector($xpos, $ypos);

                $sensitivity = 0.6; // Change this value to your liking
                $xoffset *= $sensitivity;
                $yoffset *= $sensitivity;

                $yaw   += $xoffset;
                $pitch += $yoffset;

                // Make sure that when pitch is out of bounds, screen doesn't get flipped
                if ($pitch > 89.0)
                    $pitch = 89.0;
                if ($pitch < -89.0)
                    $pitch = -89.0;

                $front = new Vector(
                    cos(Angle::toRadians($yaw)) * cos(Angle::toRadians($pitch)),
                    sin(Angle::toRadians($pitch)),
                    sin(Angle::toRadians($yaw)) * cos(Angle::toRadians($pitch))
                );
                $cameraFront = $front->normalize();
                break;
            case SDL_KEYDOWN:
                $symChar = chr($event->key->keysym->sym);
                if ($symChar == 'q') $quit = true;
                if ($symChar == 'c') $cubes = [];
                if ($symChar == '1') $cubeTexture = 1;
                if ($symChar == '2') $cubeTexture = 2;
                if ($symChar == 'u') array_pop($cubes);
                $keys['w'] = $symChar == 'w';
                $keys['s'] = $symChar == 's';
                $keys['a'] = $symChar == 'a';
                $keys['d'] = $symChar == 'd';
                break;
            case SDL_KEYUP:
                $keys = array_fill_keys(range('a', 'z'), false);
                break;
        }
    }

    $cameraSpeed = 0.5;
    if ($keys['w']) {
        $cameraPos = $cameraPos->add($cameraFront->scale($cameraSpeed));
    }
    if ($keys['s']) {
        $cameraPos = $cameraPos->substract($cameraFront->scale($cameraSpeed));
    }
    if ($keys['a']) {
        $cameraPos = $cameraPos->substract(
            $cameraFront->cross($cameraUp)->normalize()->scale($cameraSpeed)
        );
    }
    if ($keys['d']) {
        $cameraPos = $cameraPos->add(
            $cameraFront->cross($cameraUp)->normalize()->scale($cameraSpeed)
        );
    }

    SDL_GL_SwapWindow($window);
    SDL_Delay(30);
}

SDL_GL_DeleteContext($context);
SDL_DestroyWindow($window);
SDL_Quit();
