# Move Mania! - Resolume Arena Plugin

## Overview

**MOVE MANIA!** is a random movement & effects tool for Resolume Arena. This plugin provides dynamic transformation effects with beat-synced randomization capabilities.

## Features

### Core Transform Controls
- **Position**: Random X/Y movement with customizable range and speed
- **Scale**: Independent or uniform scaling with min/max limits
- **Rotation**: Random rotation within defined angle ranges
- **Opacity**: Dynamic transparency modulation

Each transform parameter can be:
- Enabled/disabled independently
- Customized with range limitations
- Adjusted with different speeds
- Smoothed for gradual transitions

### Beat Randomizer

Activate randomization in three modes:
1. **BPM Mode**: Syncs to your composition's BPM
2. **Speed Mode**: Changes at a custom speed rate
3. **Manual Mode**: Trigger changes on demand

Additional controls:
- **Freeze**: Lock the current random state
- **Smooth**: Interpolate between random values

### Echo Effects

Create trailing echo effects that work independently from movement:
- Adjustable echo count (1-20)
- Custom delay and fade per echo
- Position offset per echo layer
- Scale and rotation decay
- Multiple blend modes (Add, Normal, Multiply, Screen)

**Note**: Echo effects work on alpha clips and other sources even when all movements are disabled.

## Installation

### Method 1: Manual Installation
1. Copy `MoveMania.effect` to your Resolume effects folder:
   - **Windows**: `C:\Users\[Username]\Documents\Resolume\Arena\[version]\effects\`
   - **Mac**: `/Users/[Username]/Documents/Resolume/Arena/[version]/effects/`

2. Restart Resolume Arena

### Method 2: Drag & Drop
1. Simply drag the `.effect` file into the Resolume interface
2. The effect will be added to your Effects panel

## Usage

### Basic Setup
1. Apply the effect to a clip or layer
2. Enable "Master Enable" to activate
3. Adjust individual transform sections as needed

### Recommended Workflow
1. Start with transparent or cropped sources for best results
2. Enable only the transforms you need
3. Use presets as starting points
4. Fine-tune parameters for your specific content

### Best Practices
- **Use with alpha channels**: PNG sequences work best
- **Crop sources**: Remove black borders before applying
- **Combine with blend modes**: Enhance effects with layer blending
- **Start subtle**: Begin with small ranges and increase gradually

## Presets Included

### 1. Gentle Float
Subtle, smooth movement perfect for ambient visuals
- Small position range (0.2)
- Slow speed (0.5)
- Minimal scale variation (0.9-1.1)
- Gentle rotation (±15°)

### 2. Chaotic Jump
High-energy, beat-synced chaos
- Large position range (1.0)
- Fast speed (3.0)
- Extreme scale (0.5-2.0)
- Wild rotation (±180°)
- BPM synced

### 3. Echo Trail
Creates motion trails without movement
- 5 echo layers
- Subtle offset
- Additive blending
- Scale decay for natural fade

### 4. Strobe Effect
Rhythmic opacity pulsing
- Full opacity range (0-1)
- High speed (4.0)
- BPM synced for musical timing

## Parameter Reference

### Global Parameters
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Master Enable | Toggle | On/Off | On | Main effect switch |
| Seed | Float | 0-1000 | 0 | Random seed for reproducibility |

### Beat Randomizer
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Beat Sync | Toggle | On/Off | Off | Activate beat synchronization |
| Beat Mode | Enum | BPM/Speed/Manual | BPM | Synchronization mode |
| Speed | Float | 0.1-10 | 1.0 | Speed multiplier |
| Manual Trigger | Trigger | - | - | Manual activation |
| Freeze | Toggle | On/Off | Off | Lock current state |

### Position
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Position | Toggle | On/Off | On | Activate position randomization |
| X Range | Float | 0-2 | 0.5 | Horizontal movement range |
| Y Range | Float | 0-2 | 0.5 | Vertical movement range |
| X Speed | Float | 0-5 | 1.0 | Horizontal change speed |
| Y Speed | Float | 0-5 | 1.0 | Vertical change speed |
| Smooth Position | Toggle | On/Off | On | Enable smoothing |
| Position Smoothing | Float | 0-1 | 0.5 | Smoothing intensity |

### Scale
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Scale | Toggle | On/Off | On | Activate scale randomization |
| Scale Min | Float | 0-5 | 0.8 | Minimum scale value |
| Scale Max | Float | 0-5 | 1.2 | Maximum scale value |
| Scale Speed | Float | 0-5 | 1.0 | Scale change speed |
| Uniform Scale | Toggle | On/Off | On | Link X/Y scale |
| Scale X Independent | Float | 0-5 | 1.0 | X scale modifier |
| Scale Y Independent | Float | 0-5 | 1.0 | Y scale modifier |
| Smooth Scale | Toggle | On/Off | On | Enable smoothing |
| Scale Smoothing | Float | 0-1 | 0.5 | Smoothing intensity |

### Rotation
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Rotation | Toggle | On/Off | On | Activate rotation randomization |
| Rotation Range | Float | 0-360 | 45 | Maximum rotation angle |
| Rotation Speed | Float | 0-5 | 1.0 | Rotation change speed |
| Smooth Rotation | Toggle | On/Off | On | Enable smoothing |
| Rotation Smoothing | Float | 0-1 | 0.5 | Smoothing intensity |

### Opacity
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Opacity | Toggle | On/Off | Off | Activate opacity randomization |
| Opacity Min | Float | 0-1 | 0.5 | Minimum opacity |
| Opacity Max | Float | 0-1 | 1.0 | Maximum opacity |
| Opacity Speed | Float | 0-5 | 1.0 | Opacity change speed |
| Smooth Opacity | Toggle | On/Off | On | Enable smoothing |
| Opacity Smoothing | Float | 0-1 | 0.5 | Smoothing intensity |

### Echo Effects
| Parameter | Type | Range | Default | Description |
|-----------|------|-------|---------|-------------|
| Enable Echo | Toggle | On/Off | Off | Activate echo effect |
| Echo Count | Int | 1-20 | 3 | Number of echo layers |
| Echo Delay | Float | 0-1 | 0.1 | Time between echoes |
| Echo Fade | Float | 0-1 | 0.5 | Opacity decay per echo |
| Echo Offset X | Float | -1 to 1 | 0.1 | Horizontal offset |
| Echo Offset Y | Float | -1 to 1 | 0.1 | Vertical offset |
| Echo Scale Decay | Float | 0-1 | 0.9 | Scale reduction per echo |
| Echo Rotation Decay | Float | -180 to 180 | 5 | Rotation change per echo |
| Echo Blend Mode | Enum | Add/Normal/Multiply/Screen | Add | Blending mode |

## Troubleshooting

### Effect not appearing
- Ensure file is in correct effects folder
- Restart Resolume Arena
- Check Resolume version compatibility

### Performance issues
- Reduce echo count
- Disable unused transform sections
- Lower smoothing values

### Unexpected behavior
- Reset seed to 0
- Check if source has alpha channel
- Verify crop boundaries

## Technical Notes

### Shader Implementation
The effect uses GLSL shaders for GPU-accelerated processing. The implementation includes:
- Pseudo-random number generation for deterministic randomness
- UV coordinate transformation for position/scale/rotation
- Multi-pass rendering for echo effects
- Various blend mode calculations

### Compatibility
- **Minimum Version**: Resolume Arena 7.0+
- **GPU Requirements**: OpenGL 3.3+ compatible
- **Platform**: Windows & macOS

## License

This plugin is provided as-is for use in Resolume Arena.

## Support

For issues, suggestions, or updates:
1. Check the documentation in this repository
2. Review preset configurations for examples
3. Test with different source types

## Credits

Inspired by MOVE MANIA! concept for Resolume Arena.
Built for the VJ community.

---

**Enjoy creating with Move Mania!** 🎨✨
