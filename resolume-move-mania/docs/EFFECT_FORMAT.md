# Resolume Effect Schema

Resolume effects are XML files that define parameters and shaders. Here's the structure:

## Basic Structure

```xml
<?xml version="1.0" encoding="utf-8"?>
<effect name="Effect Name" category="Category">
  <!-- Parameters -->
  <param name="Parameter Name" type="type" attributes... />
  
  <!-- Shader Code -->
  <shader>
    <![CDATA[
      // GLSL code here
    ]]>
  </shader>
</effect>
```

## Parameter Types

### Toggle (Boolean)
```xml
<param name="Enable" type="toggle" default="true" />
```

### Float (Number)
```xml
<param name="Value" type="float" min="0" max="1" default="0.5" />
```

### Integer
```xml
<param name="Count" type="int" min="1" max="10" default="3" />
```

### Enum (Dropdown)
```xml
<param name="Mode" type="enum" default="Option1">
  <option name="Option 1" value="0" />
  <option name="Option 2" value="1" />
  <option name="Option 3" value="2" />
</param>
```

### Trigger (Button)
```xml
<param name="Trigger" type="trigger" />
```

### Color
```xml
<param name="Color" type="color" default="#FF0000" />
```

## Groups

Organize parameters into collapsible groups:

```xml
<group name="Group Name">
  <param name="Param 1" type="float" ... />
  <param name="Param 2" type="toggle" ... />
</group>
```

## Shader Variables

Parameters are automatically available as uniforms in the shader:

```glsl
uniform float paramName;  // For float parameters
uniform vec4 colorParam;  // For color parameters (rgba)
```

## Built-in Uniforms

Resolume provides these built-in uniforms:

```glsl
uniform float time;           // Time in seconds
uniform vec2 resolution;      // Output resolution
uniform sampler2D texture;    // Input texture
uniform float bpm;            // Current BPM
uniform float beat;           // Beat position (0.0 - 1.0)
```

## Presets

Define presets to save parameter configurations:

```xml
<presets>
  <preset name="Preset Name">
    <set param="Parameter Name" value="value" />
  </preset>
</presets>
```

## Common Shader Patterns

### Random Number Generation
```glsl
float random(float value) {
    return fract(sin(value * 12.9898 + seed * 78.233) * 43758.5453);
}
```

### UV Transformation (Position)
```glsl
vec2 transformedUV = uv - vec2(offsetX, offsetY);
```

### UV Transformation (Scale)
```glsl
vec2 center = vec2(0.5);
vec2 scaledUV = (uv - center) / scale + center;
```

### UV Transformation (Rotation)
```glsl
vec2 centeredUV = uv - 0.5;
float angle = radians(rotation);
mat2 rotationMatrix = mat2(cos(angle), -sin(angle), sin(angle), cos(angle));
vec2 rotatedUV = rotationMatrix * centeredUV + 0.5;
```

### Smoothing/Interpolation
```glsl
float smoothStep(float edge0, float edge1, float x) {
    float t = clamp((x - edge0) / (edge1 - edge0), 0.0, 1.0);
    return t * t * (3.0 - 2.0 * t);
}
```

## Best Practices

1. **Use descriptive parameter names** - Makes the UI clearer
2. **Set sensible defaults** - Users should get good results immediately
3. **Group related parameters** - Improves usability
4. **Provide presets** - Help users get started quickly
5. **Document your effect** - Include comments in XML and GLSL
6. **Test with various sources** - Ensure it works with different content types
7. **Optimize for performance** - Be mindful of GPU usage

## Debugging Tips

1. Check XML syntax carefully - One error breaks the whole effect
2. Use `<![CDATA[]]>` for shader code to avoid XML escaping issues
3. Test in Resolume after each change
4. Start simple, then add complexity
5. Use the Resolume console for error messages

## Resources

- Resolume Forum: https://forum.resolume.com/
- Resolume Manual: https://resolume.com/manual/
- GLSL Reference: https://www.khronos.org/opengl/wiki/Core_Language_(GLSL)
