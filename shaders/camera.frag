#version 330 core

out vec4 color;

void main()
{
    // Linearly interpolate between both textures (second texture is only slightly combined)
    color = vec4(4,4,4,4);
}
